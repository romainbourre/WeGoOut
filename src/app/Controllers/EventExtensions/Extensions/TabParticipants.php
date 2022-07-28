<?php

namespace App\Controllers\EventExtensions\Extensions
{


    use App\Controllers\EventExtensions\EventExtension;
    use App\Controllers\EventExtensions\IEventExtension;
    use App\Librairies\Emitter;
    use Domain\Entities\Alert;
    use Domain\Entities\Event;
    use Domain\Entities\User;
    use Domain\Exceptions\EventCanceledException;
    use Domain\Exceptions\EventDeletedException;
    use Domain\Exceptions\EventNotExistException;
    use Domain\Exceptions\EventSignaledException;
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
        private Event        $event;

        /**
         * TabParticipants constructor.
         * @param Event $event event
         * @throws EventCanceledException
         * @throws EventDeletedException
         * @throws EventNotExistException
         * @throws EventSignaledException
         */
        public function __construct(Event $event, private readonly ILogger $logger)
        {
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
            return $this->render("list-participants", compact('event', 'invitationForm', 'participantsFilter', 'participantsList'));
        }

        /**
         * Get the invitation form
         * @return string
         */
        private function getViewInvitationForm(): ?string
        {
            $me = $_SESSION['USER_DATA'];
            if (($this->event->isCreator($me) || $this->event->isOrganizer($me)) && !$this->event->isStarted() && !$this->event->isOver()) return $this->render('invitation-form');
            return null;
        }

        /**
         * Generate view of filter selector
         * @return string filter view
         */
        public function getViewParticipantsFilter(): string
        {
            $event = $this->event;
            return $this->render("filter", compact('event'));
        }

        /**
         * Send invitation to an user of e-mail address
         * @throws UserIncorrectPasswordException
         * @throws Exception
         */
        public function sendInvitation()
        {

            $me = $_SESSION['USER_DATA'];
            $event = $this->event;

            if (($event->isCreator($me) || $event->isOrganizer($me)) && !$event->isStarted() && !$event->isOver())
            {

                $emitter = Emitter::getInstance();

                if (
                    isset($_POST['invit-user-id'])
                    && isset($_POST['invit-user-text'])
                )
                {

                    $userId = (int)$_POST['invit-user-id'];
                    $text = (string)$_POST['invit-user-text'];

                    try
                    {

                        try
                        {
                            $user = User::loadUserById($userId);
                        }
                        catch (UserNotExistException | UserSignaledException | UserDeletedException $e)
                        {
                            if (filter_var($text, FILTER_VALIDATE_EMAIL))
                            {
                                $user = User::loadUserByEmail($text);
                            }


                        }

                        if ($user == null)
                            throw new UserNotExistException();

                        if (!$this->event->isInvited($user) && !$this->event->isCreator($user) && !$this->event->isOrganizer($user))
                        {
                            if ($this->event->sendInvitation($user))
                            {
                                $emitter->emit("event.send.invitation", $event, $user, $me);
                            }
                        }

                    }
                    catch (UserNotExistException | UserSignaledException | UserDeletedException $e)
                    {
                        $email = $text;
                        $link = "https://" . $_SERVER['HTTP_HOST'] . "/events/" . $event->getID();
                        if ($event->sendInvitation(null, $email))
                        {
                            if (!$this->emailSender->sendHtmlEmail($email, null, CONF['Application']['Email'], CONF['Application']['Name'], "Invitation à un évènement", self::render("email.email-invitation", compact('user', 'me', 'event', 'link'))))
                            {
                                Alert::addAlert('L\'envoi de l\'email d\'invitation a échoué. Veuillez rééssayer plus tard', 3);
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
         */
        public function getViewParticipantsList(int $level = 0): string
        {

            switch ($level)
            {
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
            $participants = $event->getParticipants($level);
            if ($level == 0 || $level == 3) $participants = array_merge($participants, $event->getEmailInvitation());
            return $this->render("list", compact('event', 'levelName', 'participants'));

        }

        /**
         * Set a bending participant as valid participant
         * @param User $user user
         * @return bool
         * @throws Exception
         */
        public function setParticipantAsValid(User $user): bool
        {
            $me = $_SESSION['USER_DATA'];
            $return = false;
            if ($this->event->isCreator($me) || $this->event->isOrganizer($me))
            {
                $emitter = Emitter::getInstance();
                if ($return = $this->event->setParticipantAsValid($user)) $emitter->emit("event.user.accept", $this->event, $user, $me);
            }
            return $return;
        }

        /**
         * Unset participant from the event
         * @param string $id
         * @return bool
         * @throws Exception
         */
        public function unsetParticipant(string $id): bool
        {
            $me = $_SESSION['USER_DATA'];
            if ($this->event->isCreator($me) || $this->event->isOrganizer($me))
            {
                if (filter_var($id, FILTER_VALIDATE_EMAIL))
                {
                    if ($this->event->unsetInvitation(null, $id))
                    {
                        return true;
                    }

                    return false;
                }

                try
                {
                    $user = User::loadUserById((int)$id);
                    $emitter = Emitter::getInstance();
                    if ($this->event->unsetParticipant($user))
                    {
                        $emitter->emit("event.user.delete", $this->event, $user, $me);
                        $emitter->emit("event.delete.invitation", $this->event, $user, $me);
                        return true;
                    }
                }
                catch (UserNotExistException | UserSignaledException | UserDeletedException $e)
                {
                }
            }
            return false;
        }

        public function isActivated(): bool
        {
            $event = $this->event;
            return ($event->isCreator($_SESSION['USER_DATA']) || $event->isOrganizer($_SESSION['USER_DATA']) || $event->isParticipantValid($_SESSION['USER_DATA']) || $event->isInvited($_SESSION['USER_DATA']));
        }
    }
}
