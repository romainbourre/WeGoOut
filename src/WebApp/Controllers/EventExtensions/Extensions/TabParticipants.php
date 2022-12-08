<?php

namespace WebApp\Controllers\EventExtensions\Extensions;


use Business\Entities\Event;
use Business\Entities\User;
use Business\Exceptions\DatabaseErrorException;
use Business\Exceptions\UserNotExistException;
use Business\Ports\EmailSenderInterface;
use Exception;
use InvalidArgumentException;
use System\Routing\Responses\NotFoundResponse;
use System\Routing\Responses\OkResponse;
use System\Routing\Responses\Response;
use WebApp\Attributes\Page;
use WebApp\Authentication\AuthenticationContext;
use WebApp\Controllers\EventExtensions\EventExtension;
use WebApp\Exceptions\NotConnectedUserException;
use WebApp\Librairies\Emitter;
use WebApp\Services\ToasterService\ToasterInterface;

#[Page('css-participants.css', 'tab-participants.js')]
class TabParticipants extends EventExtension
{
    private const ORDER = 2;

    public function __construct(
        private readonly EmailSenderInterface  $emailSender,
        private readonly AuthenticationContext $authenticationGateway,
        private readonly Event                 $event,
        private readonly ToasterInterface      $toaster
    )
    {
        parent::__construct('participants', 'Participants', self::ORDER);
    }

    /**
     * @throws NotConnectedUserException
     * @throws Exception
     */
    public function getContent(): string
    {
        $invitationForm = $this->getViewInvitationForm();
        $participantsFilter = $this->getViewParticipantsFilter();
        $participantsList = $this->getViewParticipantsList();
        return $this->render("list-participants",
            ['event' => $this->event, 'invitationForm' => $invitationForm, 'participantsFilter' => $participantsFilter, 'participantsList' => $participantsList]);
    }

    /**
     * @throws Exception
     */
    private function getViewInvitationForm(): ?string
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        if (($this->event->isCreator($connectedUser) || $this->event->isOrganizer($connectedUser))
            && !$this->event->isStarted() && !$this->event->isOver()) {
            return $this->render('invitation-form');
        }
        return null;
    }

    /**
     * @throws Exception
     */
    public function getViewParticipantsFilter(): string
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        return $this->render("filter", ['event' => $this->event, 'connectedUser' => $connectedUser]);
    }

    /**
     * @throws DatabaseErrorException
     * @throws Exception
     */
    public function sendInvitation(): string
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        if (($this->event->isCreator($connectedUser) || $this->event->isOrganizer($connectedUser))
            && !$this->event->isStarted() && !$this->event->isOver()) {
            $emitter = Emitter::getInstance();
            if (isset($_POST['invit-user-id']) && isset($_POST['invit-user-text'])) {
                $userId = (int)$_POST['invit-user-id'];
                $text = (string)$_POST['invit-user-text'];

                try {
                    $user = null;
                    try {
                        $user = User::load($userId);

                    } catch (UserNotExistException) {
                        if (filter_var($text, FILTER_VALIDATE_EMAIL)) {
                            $user = User::loadUserByEmail($text);
                        }
                    }
                    if (!is_null($user)
                        && !$this->event->isInvited($user)
                        && !$this->event->isCreator($user)
                        && !$this->event->isOrganizer($user)) {
                        if ($this->event->sendInvitation($user)) {
                            $emitter->emit("event.send.invitation", $this->event, $user, $connectedUser);
                        }
                    }
                } catch (UserNotExistException) {
                    $email = $text;
                    $applicationDomain = CONF['Application']['Domain'];
                    $link = "$applicationDomain/events/{$this->event->getID()}";
                    $applicationEmail = CONF['Application']['Email'];
                    $applicationName = CONF['Application']['Name'];
                    $emailSubject = "Invitation à l'événement {$this->event->getTitle()}";
                    $emailContent = self::render("email.email-invitation",
                        ['connectedUser' => $connectedUser, 'event' => $this->event, 'link' => $link]
                    );
                    if ($this->event->sendInvitation(null, $email)) {
                        if (!$this->emailSender->sendHtmlEmail(
                            $email,
                            null,
                            $applicationEmail,
                            $applicationName,
                            $emailSubject,
                            $emailContent
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
     * @throws NotConnectedUserException
     * @throws Exception
     */
    public function getViewParticipantsList(int $level = 0): string
    {
        $levelName = match ($level) {
            0 => "all",
            1 => "valid",
            2 => "wait",
            3 => "invited",
            default => throw new InvalidArgumentException($level),
        };

        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        $participants = $this->event->getParticipants($connectedUser, $level);
        if ($level == 0 || $level == 3) {
            $participants = array_merge($participants, $this->event->getEmailInvitation());
        }
        return $this->render("list", ['event' => $this->event, 'levelName' => $levelName, 'participants' => $participants, 'connectedUser' => $connectedUser]);
    }

    /**
     * @throws NotConnectedUserException
     * @throws Exception
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
     * @throws DatabaseErrorException
     * @throws NotConnectedUserException
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
            } catch (UserNotExistException) {
            }
        }
        return false;
    }

    /**
     * @throws NotConnectedUserException
     */
    public function isActivated(): bool
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        return (
            $this->event->isCreator($connectedUser)
            || $this->event->isOrganizer($connectedUser)
            || $this->event->isParticipantValid($connectedUser)
            || $this->event->isInvited($connectedUser)
        );
    }

    /**
     * @throws UserNotExistException
     * @throws DatabaseErrorException
     * @throws Exception
     */
    public function computeActionQuery(string $action): Response
    {
        $view = match ($action) {
            "filter.update" => $this->getViewParticipantsFilter(),
            "filter.all" => $this->getViewParticipantsList(),
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

