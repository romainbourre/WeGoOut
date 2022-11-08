<?php

switch ($action) {

    case "task.load":
        echo $this->getSlideTaskView();
        break;
    case "task.save":
        $this->saveFormTask();
        break;
    case "task.add":
        echo $this->addTask();
        break;
    case "task.list.update":
        echo $this->getTasksListView();
        break;
    case "task.user.set":
        echo $this->setUserDesignated();
        break;
    case "task.delete":
        echo $this->deleteTask();
        break;
    case "task.check":
        $this->checkTask();
        break;

}