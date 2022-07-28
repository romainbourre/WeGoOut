<?php

namespace App\Controllers\EventExtensions\Extensions
{


    use App\Controllers\EventExtensions\EventExtension;
    use App\Controllers\EventExtensions\IEventExtension;
    use App\Librairies\Emitter;
    use Domain\Entities\Event;
    use Domain\Entities\Review;
    use Domain\Exceptions\EventCanceledException;
    use Domain\Exceptions\EventDeletedException;
    use Domain\Exceptions\EventNotExistException;
    use Domain\Exceptions\EventSignaledException;
    use Exception;

    class TabReviews extends EventExtension implements IEventExtension
    {
        private const TAB_EXTENSION_NAME = "Avis";
        private const ORDER = 5;

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
            parent::__construct('reviews');
            $this->event = new Event($event->getID());
        }

        /**
         * Check if event can be active
         * @return bool
         */
        public function active(): bool
        {
            return ($this->event->isOver());
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

            if ($this->event->isOver())
            {

                $event = $this->event;
                $reviewsForm = $this->getViewReviewsForm();
                $reviews = $this->getViewReviewsList();

                return $this->render("view-reviews", compact('event', 'reviews', 'reviewsForm'));

            }
            else
            {

                return $this->render('content-no-over');

            }

        }

        /**
         * Generate view of form
         * @return null|string view of form
         */
        public function getViewReviewsForm(): ?string
        {
            $me = $_SESSION['USER_DATA'];
            $event = $this->event;
            if ($event->isOver() && $this->isActivated())
            {
                if (!Review::checkUserPostReview($me, $this->event))
                {
                    return $this->render('reviews-form');
                }
                return $this->render('reviews-no-form');
            }
            return null;
        }

        /**
         * Generate view of list of reviews for the event
         * @return null|string view of list of reviews
         */
        public function getViewReviewsList(): ?string
        {
            $me = $_SESSION['USER_DATA'];
            $event = $this->event;
            if ($event->isPublic() || ($event->isPrivate() && !$event->isGuestOnly() && $event->getUser()->isFriend($me)) || $this->isActivated())
            {
                $reviewsContent = $this->event->getReviews();
                if (!is_null($reviewsContent) && !empty($reviewsContent)) return $this->render('review', compact('reviewsContent'));
                return $this->render('no-review');
            }
            return $this->render('content-not-auth');
        }

        /**
         * Save new review from an an user for the event
         * @return bool|null
         * @throws Exception
         */
        public function saveNewReview(): ?bool
        {
            $me = $_SESSION['USER_DATA'];
            if ($this->event->isOver() && $this->isActivated() && !Review::checkUserPostReview($me, $this->event))
            {
                if (isset($_POST['form_new_review_note']) && isset($_POST['form_new_review_text']))
                {
                    $run = true;
                    $reviewNote = (int)htmlspecialchars($_POST['form_new_review_note']);
                    $reviewText = htmlspecialchars($_POST['form_new_review_text']);
                    if (strlen($reviewText) > 200)
                    {
                        $run = false;
                    }
                    if (empty($reviewNote) || ($reviewNote <= 0 && $reviewNote > 5))
                    {
                        $run = false;
                    }
                    if ($run)
                    {
                        if ($this->event->setNewReview($reviewNote, $reviewText))
                        {
                            $emitter = Emitter::getInstance();
                            $emitter->emit('event.review.add', $this->event, $me);
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
         */
        public function isActivated(): bool
        {
            $event = $this->event;
            return ($event->isCreator($_SESSION['USER_DATA']) || $event->isOrganizer($_SESSION['USER_DATA']) || $event->isParticipantValid($_SESSION['USER_DATA']));
        }

    }
}