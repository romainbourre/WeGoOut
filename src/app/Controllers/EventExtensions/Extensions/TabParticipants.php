<?php

namespace App\Controllers\EventExtensions\Extensions
{


    use App\Authentication\AuthenticationContext;
    use App\Controllers\EventExtensions\EventExtension;
    use App\Controllers\EventExtensions\IEventExtension;
    use App\Exceptions\NotConnectedUserException;
    use App\Librairies\Emitter;
    use Domain\Entities\Alert;
    use Domain\Entities\Event;
    use Domain\Entities\User;
    use Domain\Exceptions\EventNotExistException;
    use Domain\Exceptions\UserDeletedException;
    use Domain\Exceptions\UserIncorrectPasswordException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Exceptions\UserSignaledException;
    use Domain\Interfaces\IEmailSender;
    use Exception;
    use Infrastructure\SendGrid\SendGridAdapter;
    use System\Logging\ILogger;

    class TabParticipants extends EventExtension implements IEventExtension
    {
        private const TAB_EXTENSION_NAME = "Participants";
        private const ORDER = 2;

        private IEmailSender $emailSender;

        /**
         * TabParticipants constructor.
         * @param Event $event event
         * @throws EventNotExistException
         */
        public function __construct(
            private readonly AuthenticationContext $authenticationGateway,
            private Event $event,
            private readonly ILogger $logger
        ) {
            parent::__construct('participants');
            $this->emailSender = new SendGridAdapter(CONF['SendGrid']['ApiKey'], $this->logger);
            $this->event = new Event($event->getID());
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
         * @throws UserNotExistException
         */
        public function getContent(): string
        {
            $event = $this->event;
            $invitationForm = $this->getViewInvitationForm();
            $participantsFilter = $this->getViewParticipantsFilter();
            $participantsList = $this->getViewParticipantsList();
            return $this->render(
                "list-participants",
                compact('event', 'invitationForm', 'participantsFilter', 'participantsList')
            );
        }

        /**
         * Get the invitation form
         * @return string|null
         * @throws NotConnectedUserException
         */
        private function getViewInvitationForm(): ?string
        {
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            if (($this->event->isCreator($connectedUser) || $this->event->isOrganizer($connectedUser)) && !$this->event->isStarted(
                ) && !$this->event->isOver()) {
                return $this->render('invitation-form');
            }
            return null;
        }

        /**
         * Generate view of filter selector
         * @return string filter view
         * @throws NotConnectedUserException
         */
        public function getViewParticipantsFilter(): string
        {
            $event = $this->event;
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            return $this->render("filter", compact('event', 'connectedUser'));
        }

        /**
         * Send invitation to an user of e-mail address
         * @throws UserIncorrectPasswordException
         * @throws Exception
         */
        public function sendInvitation()
        {
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            $event = $this->event;

            if (($event->isCreator($connectedUser) || $event->isOrganizer($connectedUser)) && !$event->isStarted() && !$event->isOver()) {
                $emitter = Emitter::getInstance();

                if (
                    isset($_POST['invit-user-id'])
                    && isset($_POST['invit-user-text'])
                ) {
                    $userId = (int)$_POST['invit-user-id'];
                    $text = (string)$_POST['invit-user-text'];

                    try {
                        try {
                            $user = User::load($userId);
                        } catch (UserNotExistException|UserSignaledException|UserDeletedException $e) {
                            if (filter_var($text, FILTER_VALIDATE_EMAIL)) {
                                $user = User::loadUserByEmail($text);
                            }
                        }

                        if ($user == null) {
                            throw new UserNotExistException();
                        }

                        if (!$this->event->isInvited($user) && !$this->event->isCreator(
                                $user
                            ) && !$this->event->isOrganizer($user)) {
                            if ($this->event->sendInvitation($user)) {
                                $emitter->emit("event.send.invitation", $event, $user, $connectedUser);
                            }
                        }
                    } catch (UserNotExistException|UserSignaledException|UserDeletedException $e) {
                        $email = $text;
                        $link = "https://" . $_SERVER['HTTP_HOST'] . "/events/" . $event->getID();
                        if ($event->sendInvitation(null, $email)) {
                            if (!$this->emailSender->sendHtmlEmail(
                                $email,
                                null,
                                CONF['Application']['Email'],
                                CONF['Application']['Name'],
                                "Invitation à un évènement",
                                self::render("email.email-invitation", compact('user', 'connectedUser', 'event', 'link'))
                            )) {
                                Alert::addAlert(
                                    'L\'envoi de l\'email d\'invitation a échoué. Veuillez rééssayer plus tard',
                                    3
                                );
                            }
                        }
                    }
                }
            }
        }

        /**
         * Get view of participants list
         * @param int $level level of sort (0 = all | 1 = valid | 2 = wait | 3 = invited)
         * @return string participants list
         * @throws UserNotExistException
         * @throws NotConnectedUserException
         */
        public function getViewParticipantsList(int $level = 0): string
        {
            switch ($level) {
                case 0:
                    $levelName = "all";
                    break;
                case 1:
                    $levelName = "valid";
                    break;
                case 2:
                    $levelName = "wait";
                    break;
                case 3:
                    $levelName = "invited";
                    break;
            }

            $event = $this->event;
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            $participants = $event->getParticipants($connectedUser, $level);
            if ($level == 0 || $level == 3) {
                $participants = array_merge($participants, $event->getEmailInvitation());
            }
            return $this->render("list", compact('event', 'levelName', 'participants', 'connectedUser'));
        }

        /**
         * Set a bending participant as valid participant
         * @param User $participantToValidate user
         * @return bool
         * @throws NotConnectedUserException
         */
        public function setParticipantAsValid(User $participantToValidate): bool
        {
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            if ($this->event->isCreator($connectedUser) || $this->event->isOrganizer($connectedUser)) {
                $emitter = Emitter::getInstance();
                if ($this->event->validateParticipant($connectedUser, $participantToValidate)) {
                    $emitter->emit("event.user.accept", $this->event, $participantToValidate, $connectedUser);
                    return true;
                }
            }
            return false;
        }

        /**
         * Unset participant from the event
         * @param string $id
         * @return bool
         * @throws Exception
         */
        public function unsetParticipant(string $id): bool
        {
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            if ($this->event->isCreator($connectedUser) || $this->event->isOrganizer($connectedUser)) {
                if (filter_var($id, FILTER_VALIDATE_EMAIL)) {
                    if ($this->event->unsetInvitation(null, $id)) {
                        return true;
                    }

                    return false;
                }

                try {
                    $participantToRemove = User::load((int)$id);
                    $emitter = Emitter::getInstance();
                    if ($this->event->unsetParticipant($connectedUser, $participantToRemove)) {
                        $emitter->emit("event.user.delete", $this->event, $participantToRemove, $connectedUser);
                        $emitter->emit("event.delete.invitation", $this->event, $participantToRemove, $connectedUser);
                        return true;
                    }
                } catch (UserNotExistException|UserSignaledException|UserDeletedException $e) {
                }
            }
            return false;
        }

        /**
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
    }
}
