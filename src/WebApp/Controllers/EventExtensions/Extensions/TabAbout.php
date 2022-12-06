<?php

namespace WebApp\Controllers\EventExtensions\Extensions;


use Business\Entities\Event;
use Exception;
use System\Routing\Responses\NotFoundResponse;
use System\Routing\Responses\Response;
use WebApp\Authentication\AuthenticationContext;
use WebApp\Controllers\EventExtensions\EventExtension;
use WebApp\Exceptions\NotConnectedUserException;

class TabAbout extends EventExtension
{
    private const ORDER = 4;

    public function __construct(
        private readonly AuthenticationContext $authenticationGateway,
        private readonly Event                 $event
    )
    {
        parent::__construct('about', 'à propos', self::ORDER);
    }

    /**
     * @throws Exception
     */
    public function getContent(): string
    {
        if ($this->isActivated()) {
            return $this->render("view-about", ['event' => $this->event]);
        }
        return $this->render("content-not-auth");
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

    public function computeActionQuery(string $action): Response
    {
        return new NotFoundResponse();
    }
}
