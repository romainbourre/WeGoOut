<?php

namespace Domain\Entities
{

    use Domain\ValueObjects\FrenchDate;
    use System\Configuration\Librairies\Database;


    /**
     * Class Review
     * Represent a review of event
     * @package App\Lib
     */
    class Review
    {

        private $id;
        private $event;
        private $user;
        private $note;
        private $comment;
        private $datetimeLeave;

        /**
         * Review constructor.
         * @param int $id
         */
        public function __construct(int $id)
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT event_id, user_id, rev_note, rev_text, rev_datetime_leave FROM REVIEW WHERE rev_id = :id');
            $request->bindValue(':id', $id);

            if ($request->execute())
            {

                $result = $request->fetch();

                $this->id = $id;
                $this->event = new Event($result['event_id']);
                $this->user = User::load($result['user_id']);
                $this->note = (float)htmlspecialchars($result['rev_note']);
                $this->comment = (string)htmlspecialchars($result['rev_text']);
                $this->datetimeLeave = new FrenchDate(strtotime($result['rev_datetime_leave']));

            }

        }

        /**
         * Get review of event
         * @return Event
         */
        public function getEvent(): Event
        {
            return $this->event;
        }

        /**
         * Get user of event
         * @return User
         */
        public function getUser(): User
        {
            return $this->user;
        }

        /**
         * Get note of event
         * @return float
         */
        public function getNote(): float
        {
            return $this->note;
        }

        /**
         * Get event of event
         * @return string
         */
        public function getComment(): string
        {
            return $this->comment;
        }

        /**
         * Get leave datetime of event
         * @return FrenchDate
         */
        public function getDatetimeLeave(): ?FrenchDate
        {
            return $this->datetimeLeave;
        }

        /**
         * Check if user leave review for one event
         * @param User $user
         * @param Event $event
         * @return bool|null
         */
        public static function checkUserPostReview(User $user, Event $event): ?bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT COUNT(*) as nbrRev FROM REVIEW WHERE event_id = :eventId AND user_id = :userId AND REV_DATETIME_LEAVE is not null AND rev_datetime_delete is null AND rev_valid = 1');
            $request->bindValue(':userId', $user->getID());
            $request->bindValue(':eventId', $event->getID());

            if ($request->execute())
            {

                $result = $request->fetch();

                return $result['nbrRev'];

            }

            return null;

        }
    }
}