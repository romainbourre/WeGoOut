<?php

namespace WebApp\Controllers\EventExtensions\Extensions;


use Business\Entities\Event;
use Business\Entities\Task;
use Business\Entities\User;
use Business\Exceptions\DatabaseErrorException;
use Business\Exceptions\TaskNotExistException;
use Business\Exceptions\UserNotExistException;
use DateTime;
use Exception;
use System\Routing\Responses\NotFoundResponse;
use System\Routing\Responses\OkResponse;
use System\Routing\Responses\Response;
use WebApp\Attributes\Page;
use WebApp\Authentication\AuthenticationContext;
use WebApp\Controllers\EventExtensions\EventExtension;
use WebApp\Exceptions\NotConnectedUserException;

#[Page('css-todolist.css', 'tab-todolist.js')]
class TabToDoList extends EventExtension
{
    public const ORDER = 3;

    public function __construct(
        private readonly AuthenticationContext $authenticationGateway,
        private readonly Event                 $event
    )
    {
        parent::__construct('todolist', 'To Do List', self::ORDER);
    }

    /**
     * @throws TaskNotExistException
     * @throws Exception
     */
    public function getContent(): string
    {
        $taskForm = $this->getAddOnFormView();
        $tasksList = $this->getTasksListView();
        return $this->render("view-content", compact('taskForm', 'tasksList'));
    }

    /**
     * @throws Exception
     */
    private function getAddOnFormView(): ?string
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        if ($this->event->isCreator($connectedUser) || $this->event->isOrganizer($connectedUser)) {
            return $this->render('view-form-task');
        }
        return null;
    }

    /**
     * @throws TaskNotExistException
     * @throws NotConnectedUserException
     * @throws Exception
     */
    public function getTasksListView(): string
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        $tasksList = Task::getEventTasksForUser($this->event, $connectedUser);
        return $this->render('view-list-task', compact('tasksList', 'connectedUser'));
    }


    /**
     * @throws NotConnectedUserException
     * @throws Exception
     */
    protected function getSlideTaskView(): ?string
    {
        if (isset($_POST['task']) && !empty($_POST['task'])) {
            try {
                $task = new Task((int)$_POST['task']);
                $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
                return $this->render("slide-task", compact('task', 'connectedUser'));
            } catch (TaskNotExistException) {
            }
        }
        return null;
    }

    /**
     * @throws NotConnectedUserException
     */
    protected function addTask(): bool
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        if (isset($_POST['task-add-label'])
            && ($this->event->isCreator($connectedUser)
                || $this->event->isOrganizer($connectedUser))) {
            return $this->event->addTask(htmlspecialchars($_POST['task-add-label']));
        }
        return false;
    }

    /**
     * @throws NotConnectedUserException
     */
    protected function setUserDesignated(): bool
    {
        try {
            $task = new Task((int)$_POST['task']);
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            return $task->setUserDesignated($connectedUser);
        } catch (TaskNotExistException) {
        }
        return false;
    }

    /**
     * @throws NotConnectedUserException
     */
    protected function checkTask(): bool
    {
        try {
            $task = new Task((int)$_POST['task']);
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            if (!$task->isCheck($connectedUser)) {
                return $task->check($connectedUser);
            } else {
                return $task->uncheck($connectedUser);
            }
        } catch (TaskNotExistException) {
        }
        return false;
    }

    /**
     * Delete task of event
     * @return bool
     * @throws NotConnectedUserException
     */
    protected function deleteTask(): bool
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        if ($this->event->isCreator($connectedUser) || $this->event->isOrganizer($connectedUser)) {
            try {
                $task = new Task((int)$_POST['task']);
                return $task->delete();
            } catch (TaskNotExistException) {
            }
        }
        return false;
    }

    /**
     * @throws NotConnectedUserException
     * @throws DatabaseErrorException
     */
    protected function saveFormTask(): string
    {
        try {
            $task = new Task((int)$_POST['task']);
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();

            if ($this->event->equals($task->getEvent())) {
                // SAVE CHECK
                if (isset($_POST['task-check'])) {
                    $task->check($connectedUser);
                } else {
                    $task->uncheck($connectedUser);
                }

                if ($this->event->isCreator($connectedUser) || $this->event->isOrganizer($connectedUser)) {
                    // SAVE LABEL
                    if (isset($_POST['task-label']) && !empty($label = (string)$_POST['task-label'])) {
                        $task->setLabel($label);
                    }

                    // SAVE CATEGORY
                    if (isset($_POST['task-category'])) {
                        $task->setCategory((int)$_POST['task-category']);
                    }

                    // SAVE DEADLINE
                    if (isset($_POST['task-deadline'])) {
                        if (empty($deadline = $_POST['task-deadline'])) {
                            $deadline = null;
                        } else {
                            $deadline = DateTime::createFromFormat('d/m/Y', $deadline);
                        }
                        $task->setDatetimeDeadline($deadline);
                    }

                    // SAVE VISIBILITY
                    if (isset($_POST['task-visibility'])) {
                        $task->setVisibility((int)$_POST['task-visibility']);
                    }

                    // SAVE USER DESIGNATED
                    if (isset($_POST['task-designation'])) {
                        try {
                            $user = User::load((int)$_POST['task-designation']);
                        } catch (UserNotExistException) {
                            $user = null;
                        } finally {
                            $task->setUserDesignated($user);
                        }
                    }

                    // SAVE PRICE OF TASK
                    if (isset($_POST['task-price'])) {
                        $price = $_POST['task-price'];
                        if ($price == "") {
                            $price = null;
                        } else {
                            $price = (float)$price;
                        }
                        $task->setPrice($price);
                    }

                    // SAVE SPENT AFFECTATION
                    if (isset($_POST['task-spent-affect'])) {
                        $task->setPriceAffect((int)$_POST['task-spent-affect']);
                    }

                    // SAVE SPENT ON PLACE
                    if (isset($_POST['task-spent-onplace'])) {
                        $task->setSpentOnPlace(true);
                    } else {
                        $task->setSpentOnPlace(false);
                    }

                    // SAVE NOTE OF TASK
                    if (isset($_POST['task-notes'])) {
                        $notes = htmlspecialchars($_POST['task-notes']);
                        $task->setNote($notes);
                    }
                }
            }
            return "";
        } catch (TaskNotExistException) {
            return "";
        }
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
     * @throws NotConnectedUserException
     * @throws TaskNotExistException
     * @throws DatabaseErrorException
     */
    public function computeActionQuery(string $action): Response
    {
        $view = match ($action) {
            "task.load" => $this->getSlideTaskView(),
            "task.save" => $this->saveFormTask(),
            "task.add" => $this->addTask(),
            "task.list.update" => $this->getTasksListView(),
            "task.user.set" => $this->setUserDesignated(),
            "task.delete" => $this->deleteTask(),
            "task.check" => $this->checkTask(),
            default => null,
        };
        if (is_null($view)) {
            return new NotFoundResponse();
        }
        return new OkResponse($view);
    }


}

