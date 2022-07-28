<?php

namespace App\Controllers\EventExtensions\Extensions
{


    use App\Controllers\EventExtensions\EventExtension;
    use App\Controllers\EventExtensions\IEventExtension;
    use App\Librairies\Emitter;
    use Domain\Entities\Event;
    use Domain\Entities\Publication;
    use Domain\Exceptions\EventCanceledException;
    use Domain\Exceptions\EventDeletedException;
    use Domain\Exceptions\EventNotExistException;
    use Domain\Exceptions\EventSignaledException;

    class TabPublications extends EventExtension implements IEventExtension
    {
        private const TAB_EXTENSION_NAME = "discussions";
        private const ORDER = 1;

        private int $eventID;

        /**
         * TabPublications constructor.
         * @param Event $event event
         */
        public function __construct(Event $event)
        {
            parent::__construct('publications');
            $this->eventID = $event->getID();
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
         */
        public function getContent(): string
        {

            if ($this->isActivated())
            {

                $publicationsContent = Publication::loadPubFromEvent($this->eventID);
                $publications = $this->render('publication', compact('publicationsContent'));

                return $this->render("list-publications", compact('publications'));

            }

            return $this->render('content-not-auth');

        }

        /**
         * Check the global confidentiality for the tab
         * @return bool
         * @throws EventCanceledException
         * @throws EventDeletedException
         * @throws EventNotExistException
         * @throws EventSignaledException
         */
        public function isActivated(): bool
        {
            $me = $_SESSION['USER_DATA'];
            $event = new Event($this->eventID);
            return ($event->isPublic() || ($event->isPrivate() && !$event->isGuestOnly() && $event->getUser()->isFriend($me)) || ($event->isPrivate() && $event->isGuestOnly() && $event->isInvited($me)) || $event->isCreator($me) || $event->isOrganizer($me) || $event->isParticipantValid($me));
        }

        /**
         * Get the publications view
         * @return string publications view
         */
        public function getAjaxPublications(): string
        {
            $publicationsContent = Publication::loadPubFromEvent($this->eventID);
            return $this->render('publication', compact('publicationsContent'));
        }

        /**
         * Save a new publication for the event
         * @param array $args
         * @return bool
         * @throws EventCanceledException
         * @throws EventDeletedException
         * @throws EventNotExistException
         * @throws EventSignaledException
         */
        public function setAjaxNewPublication($args = array()): bool
        {
            $me = $_SESSION['USER_DATA'];
            if ($this->isActivated())
            {
                if ($cleaned_data = $this->getDataNewPublication())
                {
                    list($textPublication, $eventId) = $cleaned_data;
                    if (Publication::saveNewPublication($_SESSION['USER_DATA'], $eventId, $textPublication))
                    {
                        $emitter = Emitter::getInstance();
                        $emitter->emit('event.pub.add', new Event($this->eventID), $me);
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
            )
            {

                if (
                    !empty($_POST['id']) &&
                    !empty($_POST['form_new_publication_text'])
                )
                {

                    $textPublication = htmlspecialchars($_POST['form_new_publication_text']);
                    $eventId = (int)htmlspecialchars($_POST['id']);


                    return array($textPublication, $eventId);

                }

            }

            return null;
        }
    }
}
