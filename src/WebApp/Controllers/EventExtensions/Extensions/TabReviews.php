<?php

namespace WebApp\Controllers\EventExtensions\Extensions;


use Business\Entities\Event;
use Business\Entities\Review;
use Business\Exceptions\DatabaseErrorException;
use Business\Exceptions\EventNotExistException;
use Exception;
use System\Routing\Responses\NotFoundResponse;
use System\Routing\Responses\OkResponse;
use System\Routing\Responses\Response;
use WebApp\Authentication\AuthenticationContext;
use WebApp\Controllers\EventExtensions\EventExtension;
use WebApp\Exceptions\NotConnectedUserException;
use WebApp\Librairies\Emitter;

class TabReviews extends EventExtension
{
    private const ORDER = 5;

    public function __construct(
        private readonly AuthenticationContext $authenticationGateway,
        private readonly Event                 $event
    )
    {
        parent::__construct('reviews', 'Avis', self::ORDER);
    }

    /**
     * @throws NotConnectedUserException
     * @throws DatabaseErrorException
     * @throws Exception
     */
    public function getContent(): string
    {
        if ($this->event->isOver()) {
            $event = $this->event;
            $reviewsForm = $this->getViewReviewsForm();
            $reviews = $this->getViewReviewsList();
            return $this->render("view-reviews", compact('event', 'reviews', 'reviewsForm'));
        }
        return $this->render('content-no-over');
    }

    /**
     * @throws NotConnectedUserException
     * @throws Exception
     */
    public function getViewReviewsForm(): ?string
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        $event = $this->event;
        if ($event->isOver() && $this->isActivated()) {
            if (!Review::checkUserPostReview($connectedUser, $this->event)) {
                return $this->render('reviews-form');
            }
            return $this->render('reviews-no-form');
        }
        return null;
    }

    /**
     * @throws DatabaseErrorException
     * @throws NotConnectedUserException
     * @throws Exception
     */
    public function getViewReviewsList(): ?string
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        $event = $this->event;
        if (
            $event->isPublic()
            || ($event->isPrivate() && !$event->isGuestOnly() && $event->getUser()->isFriend($connectedUser))
            || $this->isActivated()
        ) {
            $reviewsContent = $this->event->getReviews();
            if (!empty($reviewsContent)) {
                return $this->render('review', compact('reviewsContent'));
            }
            return $this->render('no-review');
        }
        return $this->render('content-not-auth');
    }

    /**
     * @throws NotConnectedUserException
     * @throws EventNotExistException
     * @throws Exception
     */
    public function saveNewReview(): ?bool
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        if ($this->event->isOver()
            && $this->isActivated() && !Review::checkUserPostReview($connectedUser, $this->event)) {
            if (isset($_POST['form_new_review_note']) && isset($_POST['form_new_review_text'])) {
                $run = true;
                $reviewNote = (int)htmlspecialchars($_POST['form_new_review_note']);
                $reviewText = htmlspecialchars($_POST['form_new_review_text']);
                if (strlen($reviewText) > 200) {
                    $run = false;
                }
                if (empty($reviewNote) || $reviewNote <= 0 || $reviewNote > 5) {
                    $run = false;
                }
                if ($run) {
                    if ($this->event->addReview($connectedUser, $reviewNote, $reviewText)) {
                        $emitter = Emitter::getInstance();
                        $emitter->emit('event.review.add', $this->event, $connectedUser);
                        return true;
                    }
                }
            }
        }
        return null;
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
        );
    }

    /**
     * @throws DatabaseErrorException
     * @throws NotConnectedUserException
     * @throws Exception
     */
    public function computeActionQuery(string $action): Response
    {
        $view = match ($action) {
            "reviews.form" => $this->getViewReviewsForm(),
            "reviews.update" => $this->getViewReviewsList(),
            "reviews.new" => $this->saveNewReview(),
            default => null,
        };
        if ($view == null) {
            return new NotFoundResponse();
        }
        return new OkResponse($view);
    }


}
