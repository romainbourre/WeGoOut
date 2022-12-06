<?php

namespace WebApp\Controllers\EventExtensions\Extensions;


use Business\Entities\Event;
use Business\Entities\Publication;
use Business\Exceptions\EventNotExistException;
use Exception;
use System\Routing\Responses\NotFoundResponse;
use System\Routing\Responses\OkResponse;
use System\Routing\Responses\Response;
use WebApp\Authentication\AuthenticationContext;
use WebApp\Controllers\EventExtensions\EventExtension;
use WebApp\Exceptions\NotConnectedUserException;
use WebApp\Librairies\Emitter;

class TabPublications extends EventExtension
{
    private const ORDER = 1;

    public function __construct(
        private readonly AuthenticationContext $authenticationGateway,
        private readonly Event                 $event
    )
    {
        parent::__construct('publications', 'discussions', self::ORDER);
    }

    /**
     * @throws NotConnectedUserException
     * @throws EventNotExistException
     * @throws Exception
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
     * @throws EventNotExistException
     * @throws NotConnectedUserException
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
     * @throws Exception
     */
    public function getAjaxPublications(): string
    {
        $publicationsContent = Publication::loadPubFromEvent($this->event->getID());
        return $this->render('publication', compact('publicationsContent'));
    }

    /**
     * @throws NotConnectedUserException
     * @throws EventNotExistException
     * @throws Exception
     */
    public function setAjaxNewPublication(): string
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        if ($this->isActivated()) {
            if ($cleaned_data = $this->getDataNewPublication()) {
                list($textPublication) = $cleaned_data;
                if (Publication::saveNewPublication($connectedUser, $this->event->getID(), $textPublication)) {
                    $emitter = Emitter::getInstance();
                    $emitter->emit('event.pub.add', new Event($this->event->getID()), $connectedUser);
                }
            }
        }
        return "";
    }

    private function getDataNewPublication(): ?array
    {
        if (isset($_POST['form_new_publication_text'])) {
            if (!empty($_POST['form_new_publication_text'])) {
                $textPublication = htmlspecialchars($_POST['form_new_publication_text']);
                return array($textPublication);
            }
        }
        return null;
    }

    /**
     * @throws NotConnectedUserException
     * @throws EventNotExistException
     * @throws Exception
     */
    public function computeActionQuery(string $action): Response
    {
        $view = match ($action) {
            "publications" => $this->getAjaxPublications(),
            "new.publication" => $this->setAjaxNewPublication(),
            default => null,
        };
        if (is_null($view)) {
            return new NotFoundResponse();
        }
        return new OkResponse($view);
    }
}
