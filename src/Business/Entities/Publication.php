<?php

namespace Business\Entities
{

    use Business\Exceptions\UserNotExistException;
    use System\Librairies\Database;


    /**
     * Class Publication
     * Represent a publication of event
     * @package App\Lib
     */
    class Publication
    {

        private $id;
        private $eventId;
        private $datetime;
        private $text;
        private $userId;

        /**
         * Publication constructor.
         * @param int $id id of publication
         */
        private function __construct(int $id)
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT * FROM COMENTEVENT WHERE COM_EVENT_ID = :id');
            $request->bindValue(':id', $id);

            if ($request->execute())
            {

                $result = $request->fetch();

                $this->id = $result['COM_EVENT_ID'];
                $this->eventId = (int)$result['EVENT_ID'];
                $this->datetime = strtotime($result['COM_EVENT_DATETIME_POST']);
                $this->text = $result['COM_EVENT_TEXT'];
                $this->userId = (int)$result['USER_ID'];

            }

        }

        /**
         * Save a new publication
         * @param User $User $User user of the publication
         * @param int $eventId event of the publication
         * @param string $text text of the publication
         * @return bool
         */
        public static function saveNewPublication(User $User, int $eventId, string $text): bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('INSERT INTO COMENTEVENT(event_id, user_id, COM_EVENT_DATETIME_POST, com_event_text) VALUES (:eventId, :userId, sysdate(), :text)');
            $request->bindValue(':eventId', $eventId);
            $request->bindValue(':userId', $User->getID());
            $request->bindValue(':text', $text);

            return $request->execute();

        }

        /**
         * Get array of publications for an event
         * @param int $eventId id of event
         * @return array event publications
         */
        public static function loadPubFromEvent(int $eventId): array
        {

            $bdd = Database::getDB();

            $publications = array();

            $request = $bdd->prepare('SELECT COM_EVENT_ID FROM COMENTEVENT WHERE EVENT_ID = :id AND COM_EVENT_DATETIME_DELETE is null AND COM_EVENT_VALID = 1 ORDER BY COM_EVENT_DATETIME_POST DESC');
            $request->bindValue(':id', $eventId);

            if ($request->execute())
            {

                while ($result = $request->fetch())
                {

                    $publications[] = new Publication($result['COM_EVENT_ID']);

                }

            }

            return $publications;

        }

        /**
         * Get a ID of the event
         * @return int ID of event
         */
        public function getID(): int
        {
            return $this->id;
        }

        /**
         * Get event of the publication
         * @return Event
         */
        public function getEvent(): Event
        {
            return new Event($this->eventId);
        }

        /**
         * Get datetime of the event
         * @return int timestamp
         */
        public function getDateTime(): int
        {
            return $this->datetime;
        }

        /**
         * Get a text of the event
         * @return string text of the event
         */
        public function getText(): string
        {
            return $this->text;
        }

        /**
         * Get user of the event
         * @return User user
         * @throws UserNotExistException
         */
        public function getUser(): User
        {
            return User::load($this->userId);
        }
    }
}