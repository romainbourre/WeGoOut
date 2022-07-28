<?php

namespace Domain\Entities
{

    use DateTime;
    use Domain\Exceptions\TaskNotExistException;
    use Domain\Exceptions\UserNotExistException;
    use System\Librairies\Database;

    class Task
    {

        private $id;
        private $label;
        private $event;
        private $datetimeCreate;
        private $datetimeDeadline = null;
        private $category;
        private $userDesignated = null;
        private $price = null;
        private $priceAffect;
        private $spentOnPlace;
        private $note;
        private $publication;

        public const VISIBILITY_ORGANIZER = 0;
        public const VISIBILITY_ALL = 1;

        public const SPENT_EVENT = 0;
        public const SPENT_PERSON = 1;

        private const SPENT_ONPLACE_FALSE = 0;
        private const SPENT_ONPLACE_TRUE = 1;


        /**
         * Task constructor.
         * @param int $id id of task
         * @throws TaskNotExistException
         */
        public function __construct(int $id)
        {
            if (!is_null($id)) $this->load($id);
        }

        /**
         * Load data from database
         * @param int $id id of task
         * @return bool
         * @throws TaskNotExistException
         * @throws UserNotExistException
         */
        private function load(int $id): bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT * FROM TASK WHERE TASK_ID = :id');
            $request->bindValue(':id', $id);

            if ($request->execute())
            {

                if ($result = $request->fetch())
                {

                    $this->id = (int)$result['TASK_ID'];
                    $this->label = (string)$result['TASK_LABEL'];
                    $this->event = new Event((int)$result['EVENT_ID']);
                    if (!is_null($result['TASK_USER_DESIGNATION'])) $this->userDesignated = User::loadUserById((int)$result['TASK_USER_DESIGNATION']);
                    $this->datetimeCreate = DateTime::createFromFormat("d/m/Y", $result['TASK_DATETIME_CREATE']);
                    if (!is_null($result['TASK_DATETIME_DEADLINE'])) $this->datetimeDeadline = new DateTime($result['TASK_DATETIME_DEADLINE']);
                    $this->category = $result['TASK_CATEGORY_ID'];
                    $this->price = (float)$result['TASK_PRICE'];
                    $this->priceAffect = (int)$result['TASK_PRICE_AFFECT'];
                    $this->spentOnPlace = (int)$result['TASK_PRICE_ONPLACE'];
                    $this->publication = (int)$result['TASK_VISIBILITY'];
                    $this->note = (string)$result['TASK_NOTE'];

                    return true;

                }
                else
                {
                    throw new TaskNotExistException($id);
                }

            }

            return false;

        }

        /**
         * Reload data of task
         * @return bool
         * @throws TaskNotExistException
         * @throws UserNotExistException
         */
        public function reload(): bool
        {
            if (!is_null($this->id)) return $this->load($this->id);
            return false;
        }

        /**
         * Get id of task
         * @return int
         */
        public function getId(): int
        {
            return $this->id;
        }

        /**
         * Get label of task
         * @return string
         */
        public function getLabel(): string
        {
            return $this->label;
        }

        /**
         * Set label of task
         * @param string $label
         * @return bool
         */
        public function setLabel(string $label): bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('UPDATE TASK SET TASK_LABEL = :label WHERE TASK_ID = :taskId');
            $request->bindValue(':label', $label);
            $request->bindValue(':taskId', $this->id);

            return $request->execute();

        }

        /**
         * Get event of the task
         * @return Event
         */
        public function getEvent(): Event
        {
            return $this->event;
        }

        /**
         * Get user designated for task
         * @return null|User
         */
        public function getUserDesignated(): ?User
        {
            return $this->userDesignated;
        }

        /**
         * Set designated user
         * @param User|null $user
         * @return bool
         */
        public function setUserDesignated(?User $user): bool
        {

            $bdd = Database::getDB();

            if (!is_null($user)) $userId = $user->getID();
            else $userId = null;

            $request = $bdd->prepare('UPDATE TASK SET TASK_USER_DESIGNATION = :userId WHERE TASK_ID = :taskId');
            $request->bindValue(':userId', $userId);
            $request->bindValue(':taskId', $this->id);

            return $request->execute();

        }

        /**
         * Get datetime creation of task
         * @return DateTime
         */
        public function getDatetimeCreate(): DateTime
        {
            return $this->datetimeCreate;
        }

        /**
         * Get datetime deadline of task
         * @return null|DateTime
         */
        public function getDatetimeDeadline(): ?DateTime
        {
            return $this->datetimeDeadline;
        }

        /**
         * Set a deadline of task
         * @param DateTime|null $deadline
         * @return bool
         */
        public function setDatetimeDeadline(?DateTime $deadline): bool
        {

            if (!is_null($deadline)) $deadline = $deadline->format('Y-m-d');

            $bdd = Database::getDB();

            $request = $bdd->prepare('UPDATE TASK SET TASK_DATETIME_DEADLINE = :deadline WHERE TASK_ID = :taskId');
            $request->bindValue(':deadline', $deadline);
            $request->bindValue(':taskId', $this->id);

            return $request->execute();

        }

        /**
         * Check if deadline is over
         * @return bool
         */
        public function deadlineIsOver(): bool
        {

            $now = new DateTime();
            $deadline = $this->datetimeDeadline;

            return ($now >= $deadline);

        }

        /**
         * Get category of task
         * @return int
         */
        public function getCategory(): ?int
        {
            return $this->category;
        }

        /**
         * Set category of task
         * @param int $category
         * @return bool
         */
        public function setCategory(int $category): bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT count(*) as EXIST FROM TASK_CATEGORY WHERE TASK_CATEGORY_ID = :categoryId');
            $request->bindValue(':categoryId', $category);

            if ($request->execute())
            {

                if (!($result = $request->fetch()) || (int)$result['EXIST'] == 0)
                {
                    $category = null;
                }

                $request = $bdd->prepare('UPDATE TASK SET TASK_CATEGORY_ID = :category WHERE TASK_ID = :taskId');
                $request->bindValue(':category', $category);
                $request->bindValue(':taskId', $this->id);
                return $request->execute();

            }

            return false;

        }

        /**
         * Get price of task
         * @return float
         */
        public function getPrice(): ?float
        {
            return $this->price;
        }

        /**
         * Set price of task
         * @param float|null $price
         * @return bool
         */
        public function setPrice(?float $price): bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('UPDATE TASK SET TASK_PRICE = :price WHERE TASK_ID = :taskId');
            $request->bindValue('price', $price);
            $request->bindValue(':taskId', $this->id);

            return $request->execute();

        }

        /**
         * Get affectation of price of task
         * @return int
         */
        public function getPriceAffect(): int
        {
            return $this->priceAffect;
        }

        /**
         * Set spent affectation of task
         * @param int $affect
         * @return bool
         */
        public function setPriceAffect(int $affect): bool
        {

            if ($affect == Task::SPENT_EVENT || $affect == Task::SPENT_PERSON)
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('UPDATE TASK SET TASK_PRICE_AFFECT = :affect WHERE TASK_ID = :taskId');
                $request->bindValue(':affect', $affect);
                $request->bindValue(':taskId', $this->id);

                return $request->execute();

            }

            return false;

        }

        /**
         * Check if the spent is on place of event
         * @return bool
         */
        public function isSpentOnPlace(): bool
        {
            return ($this->spentOnPlace == self::SPENT_ONPLACE_TRUE);
        }

        /**
         * Set if the spent of task is on place
         * @param bool $onplace
         * @return bool
         */
        public function setSpentOnPlace(bool $onplace): bool
        {

            if ($onplace) $onplace = self::SPENT_ONPLACE_TRUE;
            else $onplace = self::SPENT_ONPLACE_FALSE;

            $bdd = Database::getDB();

            $request = $bdd->prepare('UPDATE TASK SET TASK_PRICE_ONPLACE = :onPlace WHERE TASK_ID = :taskId');
            $request->bindValue(':onPlace', $onplace);
            $request->bindValue(':taskId', $this->id);

            return $request->execute();

        }

        /**
         * Get note of task
         * @return string
         */
        public function getNote(): string
        {
            return $this->note;
        }

        /**
         * Set note of task
         * @param string $notes
         * @return bool
         */
        public function setNote(string $notes): bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('UPDATE TASK SET TASK_NOTE = :note WHERE TASK_ID = :taskId');
            $request->bindValue(':note', $notes);
            $request->bindValue(':taskId', $this->id);

            return $request->execute();

        }

        /**
         * Get publication of task
         * @return mixed
         */
        public function getVisibility(): int
        {
            return $this->publication;
        }

        /**
         * Set visibility of task
         * @param int $visibility
         * @return bool
         */
        public function setVisibility(int $visibility): bool
        {

            if ($visibility == self::VISIBILITY_ALL || $visibility == self::VISIBILITY_ORGANIZER)
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('UPDATE TASK SET TASK_VISIBILITY = :visibility WHERE TASK_ID = :taskId');
                $request->bindValue(':visibility', $visibility);
                $request->bindValue(':taskId', $this->id);

                return $request->execute();

            }

            return false;

        }

        /**
         * Delete task
         * @return bool
         */
        public function delete(): bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('UPDATE TASK SET TASK_DATETIME_DELETE = sysdate() WHERE TASK_ID = :taskId');
            $request->bindValue(':taskId', $this->id);

            return $request->execute();

        }

        /**
         * Check task
         * @param User $user
         * @return bool
         */
        public function check(User $user): bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('INSERT INTO TASK_DONE(TASK_ID, USER_ID, TASK_DATETIME_DONE) VALUES (:eventId, :userId, sysdate())');
            $request->bindValue(':eventId', $this->id);
            $request->bindValue(':userId', $user->getID());

            if ($this->publication == self::VISIBILITY_ORGANIZER && ($this->event->isOrganizer($user) || $this->event->isCreator($user)))
            {
                if ($request->execute())
                {

                    $this->setUserDesignated($user);
                    return true;
                }
            }
            else if ($this->publication == self::VISIBILITY_ALL && ($this->event->isCreator($user) || $this->event->isOrganizer($user) || $this->event->isParticipantValid($user)))
            {
                return $request->execute();
            }

            return false;

        }

        /**
         * Uncheck task
         * @param User $user
         * @return bool
         */
        public function uncheck(User $user): bool
        {

            if ($this->event->isCreator($user) || $this->event->isOrganizer($user) || $this->event->isParticipantValid($user))
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('UPDATE TASK_DONE SET TASK_DATETIME_UNDONE = sysdate() WHERE TASK_ID = :taskId');
                $request->bindValue(':taskId', $this->id);

                return $request->execute();

            }

            return false;

        }

        /**
         * Check if task is checked
         * @param User|null $user
         * @return bool
         */
        public function isCheck(User $user = null): bool
        {

            $bdd = Database::getDB();
            $request = null;

            if ($this->publication == self::VISIBILITY_ORGANIZER)
            {
                $request = $bdd->prepare('SELECT COUNT(*) as FIND FROM TASK_DONE JOIN TASK ON TASK.TASK_ID = TASK_DONE.TASK_ID AND TASK.TASK_USER_DESIGNATION = TASK_DONE.USER_ID WHERE TASK.TASK_ID = :taskId AND TASK_DONE.TASK_DATETIME_UNDONE is null');
            }
            else if ($this->publication == self::VISIBILITY_ALL && !is_null($user))
            {
                $request = $bdd->prepare('SELECT COUNT(*) as FIND FROM TASK_DONE JOIN TASK ON TASK.TASK_ID = TASK_DONE.TASK_ID WHERE TASK.TASK_ID = :taskId AND TASK_DONE.USER_ID = :userId AND TASK_DONE.TASK_DATETIME_UNDONE is null');
                $request->bindValue(':userId', $user->getID());
            }

            if (!is_null($request))
            {

                $request->bindValue(':taskId', $this->id);

                if ($request->execute())
                {
                    $result = $request->fetch();
                    return ((int)$result['FIND'] > 0);
                }

            }

            return false;

        }

        /**
         * Get task categories
         * @return iterable|null list of categories
         */
        public static function getCategories(): ?iterable
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT * FROM TASK_CATEGORY');

            if ($request->execute())
            {

                $categories = array();

                while ($result = $request->fetch())
                {
                    $categories[$result['TASK_CATEGORY_ID']] = $result['TASK_CATEGORY_LABEL'];
                }

                return $categories;

            }

            return null;

        }

        /**
         * Get tasks list of event for an user
         * @param Event $event
         * @param User $user
         * @return iterable|null list of tasks
         */
        public static function getEventTasksForUser(Event $event, User $user): ?iterable
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT * FROM TASK WHERE EVENT_ID = :eventId AND TASK_DATETIME_DELETE is null');
            $request->bindValue(':eventId', $event->getID());

            if ($request->execute())
            {

                $tasks = array();

                while ($result = $request->fetch())
                {

                    $task = new Task((int)$result['TASK_ID']);

                    switch ($task->getVisibility())
                    {

                        case self::VISIBILITY_ORGANIZER:
                            if ($task->getEvent()->isCreator($user) || $task->getEvent()->isOrganizer($user)) $tasks[] = $task;
                            break;
                        case self::VISIBILITY_ALL:
                            if ($task->getEvent()->isCreator($user) || $task->getEvent()->isOrganizer($user) || $task->getEvent()->isParticipantValid($user)) $tasks[] = $task;
                            break;

                    }

                }

                return $tasks;

            }

            return null;

        }

        /**
         * Add task for an event
         * @param Event $event event of task
         * @param string $label label of task
         * @param int $visibility visibility of task
         * @param int $spentAffect affectation spent of task
         * @param int $spentOnplace spent on place
         * @return bool
         */
        public static function addTask(Event $event, string $label, int $visibility = self::VISIBILITY_ORGANIZER, int $spentAffect = self::SPENT_EVENT, int $spentOnplace = self::SPENT_ONPLACE_FALSE)
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('INSERT INTO TASK(TASK_DATETIME_CREATE, EVENT_ID, TASK_LABEL, TASK_VISIBILITY, TASK_PRICE_AFFECT, TASK_PRICE_ONPLACE) VALUES(sysdate(), :eventId, :label, :visibility, :affect, :onplace)');;
            $request->bindValue(':eventId', $event->getID());
            $request->bindValue(':label', $label);
            $request->bindValue(':visibility', $visibility);
            $request->bindValue(':affect', $spentAffect);
            $request->bindValue(':onplace', $spentOnplace);

            return $request->execute();
        }
    }
}