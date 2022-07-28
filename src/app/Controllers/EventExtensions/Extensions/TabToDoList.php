<?php

namespace App\Controllers\EventExtensions\Extensions
{

    
    use App\Controllers\EventExtensions\EventExtension;
    use App\Controllers\EventExtensions\IEventExtension;
    use DateTime;
    use Domain\Entities\Event;
    use Domain\Entities\Task;
    use Domain\Entities\User;
    use Domain\Exceptions\EventCanceledException;
    use Domain\Exceptions\EventDeletedException;
    use Domain\Exceptions\EventNotExistException;
    use Domain\Exceptions\EventSignaledException;
    use Domain\Exceptions\TaskNotExistException;
    use Domain\Exceptions\UserDeletedException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Exceptions\UserSignaledException;
    use System\Host\Host;

    class TabToDoList extends EventExtension implements IEventExtension
    {
        private const TAB_EXTENSION_NAME = "To Do List";
        public const ORDER = 3;

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
            parent::__construct('todolist');
            $this->event = new Event($event->getID());
        }


        public function active(): bool
        {
            $me = $_SESSION['USER_DATA'];
            return (($this->event->isCreator($me) || $this->event->isCreator($me)) || ($this->event->isParticipantValid($me) && Task::getEventTasksForUser($this->event, $me)));
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
            $me = $_SESSION['USER_DATA'];
            $event = $this->event;
            $taskForm = $this->getAddOnFormView();
            $tasksList = $this->getTasksListView();
            return $this->render("view-content", compact('taskForm', 'tasksList'));
        }

        /**
         * Get a form for add task
         * @return string
         */
        private function getAddOnFormView(): ?string
        {
            $me = $_SESSION['USER_DATA'];
            if ($this->event->isCreator($me) || $this->event->isOrganizer($me)) return $this->render('view-form-task');
            return null;
        }

        /**
         * Get list of tasks for the event
         * @return string
         */
        public function getTasksListView(): string
        {
            $event = $this->event;
            $tasksList = Task::getEventTasksForUser($event, Host::getMe());
            return $this->render('view-list-task', compact('tasksList'));
        }

        /**
         * Get view of a task slider
         * @return string
         */
        protected function getSlideTaskView(): ?string
        {
            if (isset($_POST['task']) && !empty($_POST['task']))
            {
                try
                {
                    $task = new Task((int)$_POST['task']);
                    return $this->render("slide-task", compact('task'));
                }
                catch (TaskNotExistException $e)
                {
                }
            }
            return null;
        }

        /**
         * Add task in event
         */
        protected function addTask(): bool
        {
            $me = $_SESSION['USER_DATA'];
            if (isset($_POST['task-add-label']) && ($this->event->isCreator($me) || $this->event->isOrganizer($me)))
            {
                return $this->event->addTask(htmlspecialchars($_POST['task-add-label']));
            }
            return false;
        }

        /**
         * Set user designated as me
         * @return bool
         */
        protected function setUserDesignated(): bool
        {
            try
            {
                $task = new Task((int)$_POST['task']);
                $me = $_SESSION['USER_DATA'];
                return $task->setUserDesignated($me);
            }
            catch (TaskNotExistException $e)
            {
            }
            return false;
        }

        /**
         * Check task
         * @return bool
         */
        protected function checkTask(): bool
        {
            try
            {
                $task = new Task((int)$_POST['task']);
                $me = $_SESSION['USER_DATA'];
                if (!$task->isCheck($me)) return $task->check($me);
                else return $task->uncheck($me);
            }
            catch (TaskNotExistException $e)
            {
            }
            return false;
        }

        /**
         * Delete task of event
         * @return bool
         */
        protected function deleteTask(): bool
        {
            $me = $_SESSION['USER_DATA'];
            if ($this->event->isCreator($me) || $this->event->isOrganizer($me))
            {
                try
                {
                    $task = new Task((int)$_POST['task']);
                    return $task->delete();
                }
                catch (TaskNotExistException $e)
                {
                }
            }
            return false;
        }

        /**
         * Save edited data of task
         */
        protected function saveFormTask()
        {

            try
            {

                $task = new Task((int)$_POST['task']);
                $me = $_SESSION['USER_DATA'];

                if ($this->event->equals($task->getEvent()))
                {

                    // SAVE CHECK
                    if (isset($_POST['task-check'])) $task->check($me);
                    else $task->uncheck($me);

                    if ($this->event->isCreator($me) || $this->event->isOrganizer($me))
                    {

                        // SAVE LABEL
                        if (isset($_POST['task-label']) && !empty($label = (string)$_POST['task-label']))
                        {
                            $task->setLabel($label);
                        }

                        // SAVE CATEGORY
                        if (isset($_POST['task-category']))
                        {
                            $task->setCategory((int)$_POST['task-category']);
                        }

                        // SAVE DEADLINE
                        if (isset($_POST['task-deadline']))
                        {
                            if (empty($deadline = $_POST['task-deadline']))
                            {
                                $deadline = null;
                            }
                            else
                            {
                                $deadline = DateTime::createFromFormat('d/m/Y', $deadline);
                            }
                            $task->setDatetimeDeadline($deadline);
                        }

                        // SAVE VISIBILITY
                        if (isset($_POST['task-visibility']))
                        {
                            $task->setVisibility((int)$_POST['task-visibility']);
                        }

                        // SAVE USER DESIGNATED
                        if (isset($_POST['task-designation']))
                        {
                            try
                            {
                                $user = User::loadUserById((int)$_POST['task-designation']);
                            }
                            catch (UserNotExistException | UserDeletedException | UserSignaledException $e)
                            {
                                $user = null;
                            }
                            finally
                            {
                                $task->setUserDesignated($user);
                            }
                        }

                        // SAVE PRICE OF TASK
                        if (isset($_POST['task-price']))
                        {
                            $price = $_POST['task-price'];
                            if ($price == "") $price = null;
                            else $price = (float)$price;
                            $task->setPrice($price);
                        }

                        // SAVE SPENT AFFECTATION
                        if (isset($_POST['task-spent-affect']))
                        {
                            $task->setPriceAffect((int)$_POST['task-spent-affect']);
                        }

                        // SAVE SPENT ON PLACE
                        if (isset($_POST['task-spent-onplace'])) $task->setSpentOnPlace(true);
                        else $task->setSpentOnPlace(false);

                        // SAVE NOTE OF TASK
                        if (isset($_POST['task-notes']))
                        {
                            $notes = (string)htmlspecialchars($_POST['task-notes']);
                            $task->setNote($notes);
                        }

                    }

                }

            }
            catch (TaskNotExistException $e)
            {
            }
        }

        public function isActivated(): bool
        {
            $event = $this->event;
            return ($event->isCreator($_SESSION['USER_DATA']) || $event->isOrganizer($_SESSION['USER_DATA']) || $event->isParticipantValid($_SESSION['USER_DATA']) || $event->isInvited($_SESSION['USER_DATA']));
        }
    }
}
