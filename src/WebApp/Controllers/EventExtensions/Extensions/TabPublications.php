<?php

namespace WebApp\Controllers\EventExtensions\Extensions;


use Business\Entities\Event;
use Business\Entities\Publication;
use Business\Exceptions\DatabaseErrorException;
use Business\Exceptions\EventNotExistException;
use Business\Ports\AuthenticationContextInterface;
use Exception;
use WebApp\Controllers\EventExtensions\EventExtension;
use WebApp\Controllers\EventExtensions\IEventExtension;
use WebApp\Exceptions\NotConnectedUserException;
use WebApp\Librairies\Emitter;

class TabPublications extends EventExtension implements IEventExtension
{
    private const TAB_EXTENSION_NAME = "discussions";
    private const ORDER = 1;


    public function __construct(
        private readonly AuthenticationContextInterface $authenticationGateway,
        private readonly Event $event
    ) {
        parent::__construct('publications');
    }

    /**
     * Get the name of the tab
     * @return string name
     */
    public function getExtensionName(): string
    {
        return self::TAB_EXTENSION_NAME;
    }

    /**
     * Get order of the tab
     * @return int order
     */
    public function getTabPosition(): int
    {
        return self::ORDER;
    }

    /**
     * Generate global content of the tab
     * @return string
     * @throws EventNotExistException
     * @throws NotConnectedUserException
     * @throws DatabaseErrorException
     */
    public function getContent(): string
    {
        if (!$this->isActivated()) {
            return $this->render('content-not-auth');
        }
        $publicationsContent = Publication::loadPubFromEvent($this->event->getID());
        $publications = $this->render('publication', compact('publicationsContent'));
        return $this->render("list-publications", compact('publications'));
    }

    /**
     * Check the global confidentiality for the tab
     * @return bool
     * @throws EventNotExistException
     * @throws NotConnectedUserException
     * @throws DatabaseErrorException
     */
    public function isActivated(): bool
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        $event = new Event($this->event->getID());
        if ($event->isPublic()) {
            return true;
        }
        if ($event->isCreator($connectedUser) || $event->isOrganizer($connectedUser)) {
            return true;
        }
        if ($event->isGuestOnly() && $event->isInvited($connectedUser)) {
            return true;
        }
        return $event->isParticipantValid($connectedUser);
    }

    /**
     * Get the publications view
     * @return string publications view
     */
    public function getAjaxPublications(): string
    {
        $publicationsContent = Publication::loadPubFromEvent($this->event->getID());
        return $this->render('publication', compact('publicationsContent'));
    }

    /**
     * Save a new publication for the event
     * @param array $args
     * @return bool
     * @throws EventNotExistException
     * @throws NotConnectedUserException
     * @throws DatabaseErrorException
     * @throws Exception
     */
    public function setAjaxNewPublication($args = array()): bool
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        if ($this->isActivated()) {
            if ($cleaned_data = $this->getDataNewPublication()) {
                list($textPublication, $eventId) = $cleaned_data;
                if (Publication::saveNewPublication($connectedUser, $eventId, $textPublication)) {
                    $emitter = Emitter::getInstance();
                    $emitter->emit('event.pub.add', new Event($this->event->getID()), $connectedUser);
                }
            }
        }
        return false;
    }

    /**
     * Retrieve data form of the publication
     * @return array|null data form checked
     */
    private function getDataNewPublication(): ?array
    {
        if (
            isset($_POST['id']) &&
            isset($_POST['form_new_publication_text'])
        ) {
            if (
                !empty($_POST['id']) &&
                !empty($_POST['form_new_publication_text'])
            ) {
                $textPublication = htmlspecialchars($_POST['form_new_publication_text']);
                $eventId = (int)htmlspecialchars($_POST['id']);


                return array($textPublication, $eventId);
            }
        }

        return null;
    }

    /**
     * @throws NotConnectedUserException
     * @throws DatabaseErrorException
     * @throws EventNotExistException
     */
    public function computeActionQuery(string $action): void
    {
        switch ($action) {
            case "publications":
                echo $this->getAjaxPublications();
                break;
            case "new.publication":
                $this->setAjaxNewPublication();
                break;
        }
    }
}

