<?php

namespace WebApp\Controllers\EventExtensions\Extensions;


use Business\Entities\Event;
use Business\Entities\Task;
use Business\Entities\User;
use Business\Exceptions\TaskNotExistException;
use Business\Exceptions\UserDeletedException;
use Business\Exceptions\UserNotExistException;
use Business\Exceptions\UserSignaledException;
use Business\Ports\AuthenticationContextInterface;
use DateTime;
use System\Routing\Responses\NotFoundResponse;
use System\Routing\Responses\OkResponse;
use System\Routing\Responses\Response;
use WebApp\Controllers\EventExtensions\EventExtension;
use WebApp\Controllers\EventExtensions\IEventExtension;
use WebApp\Exceptions\NotConnectedUserException;

class TabToDoList extends EventExtension implements IEventExtension
{
    private const TAB_EXTENSION_NAME = "To Do List";
    public const ORDER = 3;


    public function __construct(
        private readonly AuthenticationContextInterface $authenticationGateway,
        private readonly Event $event
    ) {
        parent::__construct('todolist');
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
     * @throws TaskNotExistException
     */
    public function getContent(): string
    {
        $me = $this->authenticationGateway->getConnectedUserOrThrow();
        $event = $this->event;
        $taskForm = $this->getAddOnFormView();
        $tasksList = $this->getTasksListView();
        return $this->render("view-content", compact('taskForm', 'tasksList'));
    }

    /**
     * Get a form for add task
     * @return string|null
     * @throws NotConnectedUserException
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
     * Get list of tasks for the event
     * @return string
     * @throws NotConnectedUserException
     * @throws TaskNotExistException
     */
    public function getTasksListView(): string
    {
        $event = $this->event;
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        $tasksList = Task::getEventTasksForUser($event, $connectedUser);
        return $this->render('view-list-task', compact('tasksList', 'connectedUser'));
    }

    /**
     * Get view of a task slider
     * @return string|null
     * @throws NotConnectedUserException
     */
    protected function getSlideTaskView(): ?string
    {
        if (isset($_POST['task']) && !empty($_POST['task'])) {
            try {
                $task = new Task((int)$_POST['task']);
                $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
                return $this->render("slide-task", compact('task', 'connectedUser'));
            } catch (TaskNotExistException $e) {
            }
        }
        return null;
    }

    /**
     * Add task in event
     * @throws NotConnectedUserException
     */
    protected function addTask(): bool
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        if (isset($_POST['task-add-label']) && ($this->event->isCreator(
                    $connectedUser
                ) || $this->event->isOrganizer($connectedUser))) {
            return $this->event->addTask(htmlspecialchars($_POST['task-add-label']));
        }
        return false;
    }

    /**
     * Set user designated as me
     * @return bool
     * @throws NotConnectedUserException
     */
    protected function setUserDesignated(): bool
    {
        try {
            $task = new Task((int)$_POST['task']);
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            return $task->setUserDesignated($connectedUser);
        } catch (TaskNotExistException $e) {
        }
        return false;
    }

    /**
     * Check task
     * @return bool
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
        } catch (TaskNotExistException $e) {
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
            } catch (TaskNotExistException $e) {
            }
        }
        return false;
    }

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
                        } catch (UserNotExistException|UserDeletedException|UserSignaledException $e) {
                            $user = null;
                        }
                        finally {
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
                        $notes = (string)htmlspecialchars($_POST['task-notes']);
                        $task->setNote($notes);
                    }
                }
            }
            return "";
        } catch (TaskNotExistException $e) {
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

