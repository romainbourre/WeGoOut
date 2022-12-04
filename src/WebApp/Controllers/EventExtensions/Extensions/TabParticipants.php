<?php

namespace WebApp\Controllers\EventExtensions\Extensions;


use Business\Entities\Event;
use Business\Entities\User;
use Business\Exceptions\DatabaseErrorException;
use Business\Exceptions\UserDeletedException;
use Business\Exceptions\UserNotExistException;
use Business\Exceptions\UserSignaledException;
use Business\Ports\AuthenticationContextInterface;
use Business\Ports\EmailSenderInterface;
use Exception;
use System\Routing\Responses\NotFoundResponse;
use System\Routing\Responses\OkResponse;
use System\Routing\Responses\Response;
use WebApp\Controllers\EventExtensions\EventExtension;
use WebApp\Controllers\EventExtensions\IEventExtension;
use WebApp\Exceptions\NotConnectedUserException;
use WebApp\Librairies\Emitter;
use WebApp\Services\ToasterService\ToasterInterface;

class TabParticipants extends EventExtension implements IEventExtension
{
    private const TAB_EXTENSION_NAME = "Participants";
    private const ORDER = 2;


    public function __construct(
        private readonly EmailSenderInterface $emailSender,
        private readonly AuthenticationContextInterface $authenticationGateway,
        private readonly Event $event,
        private readonly ToasterInterface $toaster
    ) {
        parent::__construct('participants');
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
        if (($this->event->isCreator($connectedUser) || $this->event->isOrganizer(
                    $connectedUser
                )) && !$this->event->isStarted() && !$this->event->isOver()) {
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
     * Send invitation to a user of e-mail address
     * @throws Exception
     */
    public function sendInvitation(): string
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        $event = $this->event;
        if (($event->isCreator($connectedUser) || $event->isOrganizer($connectedUser)) && !$event->isStarted(
            ) && !$event->isOver()) {
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
                    $applicationDomain = CONF['Application']['Domain'];
                    $link = "$applicationDomain/events/{$event->getID()}";
                    $applicationEmail = CONF['Application']['Email'];
                    $applicationName = CONF['Application']['Name'];
                    if ($event->sendInvitation(null, $email)) {
                        if (!$this->emailSender->sendHtmlEmail(
                            $email,
                            null,
                            $applicationEmail,
                            $applicationName,
                            "Invitation à un évènement",
                            self::render(
                                "email.email-invitation",
                                compact('connectedUser', 'event', 'link', 'applicationName')
                            )
                        )) {
                            $this->toaster->error(
                                'L\'envoi de l\'email d\'invitation a échoué. Veuillez rééssayer plus tard'
                            );
                        }
                    }
                }
            }
        }
        return '';
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

    /**
     * @throws UserNotExistException
     * @throws NotConnectedUserException
     * @throws DatabaseErrorException
     * @throws Exception
     */
    public function computeActionQuery(string $action): Response
    {
        $view = match ($action) {
            "filter.update" => $this->getViewParticipantsFilter(),
            "filter.all" => $this->getViewParticipantsList(0),
            "filter.valid" => $this->getViewParticipantsList(1),
            "filter.wait" => $this->getViewParticipantsList(2),
            "filter.invited" => $this->getViewParticipantsList(3),
            "part.accept" => $this->setParticipantAsValid(User::load($_POST['userId'])),
            "part.delete" => $this->unsetParticipant($_POST['userId']),
            "part.invite" => $this->sendInvitation(),
            default => null
        };
        if (is_null($view)) {
            return new NotFoundResponse();
        }
        return new OkResponse($view);
    }
}

