<?php

namespace App\Controllers\EventExtensions\Extensions
{


    use App\Controllers\EventExtensions\EventExtension;
    use App\Controllers\EventExtensions\IEventExtension;
    use Domain\Entities\Event;
    use Domain\Exceptions\EventCanceledException;
    use Domain\Exceptions\EventDeletedException;
    use Domain\Exceptions\EventNotExistException;
    use Domain\Exceptions\EventSignaledException;

    class TabAbout extends EventExtension implements IEventExtension
    {
        private const TAB_EXTENSION_NAME = "à propos";
        private const ORDER = 4;

        private Event $event;

        /**
         * TabParticipants constructor.
         * @param Event $event event
         * @throws EventCanceledException
         * @throws EventDeletedException
         * @throws EventNotExistException
         * @throws EventSignaledException
         */
        public function __construct(Event $event)
        {
            parent::__construct('about');
            $this->event = new Event($event->getID());
        }

        /**
         * Check if tab can be active
         * @return bool
         */
        public function active(): bool
        {
            return ($this->isActivated());
        }

        /**
         * Get name of the tab
         * @return string name of the tab
         */
        public function getExtensionName(): string
        {
            return self::TAB_EXTENSION_NAME;
        }

        /**
         * Get the order of the tab
         * @return int order
         */
        public function getTabPosition(): int
        {
            return self::ORDER;
        }

        /**
         * Generate global content view of the tab
         * @return string global content
         */
        public function getContent(): string
        {

            if ($this->isActivated())
            {

                $event = $this->event;
                return $this->render("view-about", compact('event'));

            }

            return $this->render("content-not-auth");

        }

        /**
         * Check the global confidentiality for the tab
         * @return bool
         */
        public function isActivated(): bool
        {
            $event = $this->event;
            return ($event->isCreator($_SESSION['USER_DATA']) || $event->isOrganizer($_SESSION['USER_DATA']) || $event->isParticipantValid($_SESSION['USER_DATA']) || $event->isInvited($_SESSION['USER_DATA']));
        }

    }
}
