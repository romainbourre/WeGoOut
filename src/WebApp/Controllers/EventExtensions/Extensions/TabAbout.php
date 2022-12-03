<?php

namespace WebApp\Controllers\EventExtensions\Extensions
{


    use Business\Entities\Event;
    use Business\Ports\AuthenticationContextInterface;
    use WebApp\Controllers\EventExtensions\EventExtension;
    use WebApp\Controllers\EventExtensions\IEventExtension;
    use WebApp\Exceptions\NotConnectedUserException;

    class TabAbout extends EventExtension implements IEventExtension
    {
        private const TAB_EXTENSION_NAME = "à propos";
        private const ORDER = 4;


        public function __construct(
            private readonly AuthenticationContextInterface $authenticationGateway,
            private readonly Event $event
        ) {
            parent::__construct('about');
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
         * @throws NotConnectedUserException
         */
        public function getContent(): string
        {
            if ($this->isActivated()) {
                $event = $this->event;
                return $this->render("view-about", compact('event'));
            }

            return $this->render("content-not-auth");
        }

        /**
         * Check the global confidentiality for the tab
         * @return bool
         * @throws NotConnectedUserException
         */
        public function isActivated(): bool
        {
            $event = $this->event;
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            return ($event->isCreator($connectedUser) || $event->isOrganizer(
                    $connectedUser
                ) || $event->isParticipantValid($connectedUser) || $event->isInvited($connectedUser));
        }

        public function computeActionQuery(string $action): void
        {
        }
    }
}
