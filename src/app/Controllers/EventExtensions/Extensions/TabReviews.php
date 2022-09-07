<?php

namespace App\Controllers\EventExtensions\Extensions
{


    use App\Authentication\AuthenticationContext;
    use App\Controllers\EventExtensions\EventExtension;
    use App\Controllers\EventExtensions\IEventExtension;
    use App\Exceptions\NotConnectedUserException;
    use App\Librairies\Emitter;
    use Domain\Entities\Event;
    use Domain\Entities\Review;
    use Domain\Exceptions\DatabaseErrorException;
    use Exception;

    class TabReviews extends EventExtension implements IEventExtension
    {
        private const TAB_EXTENSION_NAME = "Avis";
        private const ORDER = 5;


        public function __construct(
            private readonly AuthenticationContext $authenticationGateway,
            private readonly Event $event
        ) {
            parent::__construct('reviews');
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
         * @throws DatabaseErrorException
         */
        public function getContent(): string
        {
            if ($this->event->isOver()) {
                $event = $this->event;
                $reviewsForm = $this->getViewReviewsForm();
                $reviews = $this->getViewReviewsList();

                return $this->render("view-reviews", compact('event', 'reviews', 'reviewsForm'));
            } else {
                return $this->render('content-no-over');
            }
        }

        /**
         * Generate view of form
         * @return null|string view of form
         * @throws NotConnectedUserException
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
         * Generate view of list of reviews for the event
         * @return null|string view of list of reviews
         * @throws NotConnectedUserException
         * @throws DatabaseErrorException
         */
        public function getViewReviewsList(): ?string
        {
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            $event = $this->event;
            if ($event->isPublic() || ($event->isPrivate() && !$event->isGuestOnly() && $event->getUser()->isFriend(
                        $connectedUser
                    )) || $this->isActivated()) {
                $reviewsContent = $this->event->getReviews();
                if (!is_null($reviewsContent) && !empty($reviewsContent)) {
                    return $this->render('review', compact('reviewsContent'));
                }
                return $this->render('no-review');
            }
            return $this->render('content-not-auth');
        }

        /**
         * Save new review from a user for the event
         * @return bool|null
         * @throws Exception
         */
        public function saveNewReview(): ?bool
        {
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            if ($this->event->isOver() && $this->isActivated() && !Review::checkUserPostReview(
                    $connectedUser,
                    $this->event
                )) {
                if (isset($_POST['form_new_review_note']) && isset($_POST['form_new_review_text'])) {
                    $run = true;
                    $reviewNote = (int)htmlspecialchars($_POST['form_new_review_note']);
                    $reviewText = htmlspecialchars($_POST['form_new_review_text']);
                    if (strlen($reviewText) > 200) {
                        $run = false;
                    }
                    if (empty($reviewNote) || ($reviewNote <= 0 && $reviewNote > 5)) {
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
                ) || $event->isParticipantValid($connectedUser));
        }

    }
}