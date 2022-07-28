<?php use Domain\Entities\Task; ?>
<?php foreach ($tasksList as $task): ?>

    <div class="white task-item" data-task="<?= $task->getId() ?>">
        <div class="truncate task-name"><?= $task->getLabel() ?></div>
        <div class="task-deadline center-align <?php if($task->deadlineIsOver()) echo "red-text"; else "grey-text" ?>"><?php if( !is_null($deadline = $task->getDatetimeDeadline()) ) echo $deadline->format("d/m/Y") ?></div>
        <p class="task-actor center-align">
            <?php if ($task->getVisibility() != Task::VISIBILITY_ALL): ?>
                <?php if(!is_null($user = $task->getUserDesignated())): ?>
                    <a href="/profile/<?= $user->getID() ?>"><?= $user->getName("full") ?></a>
                <?php else: ?>
                    <a class="btn">Je m'en occupe</a>
                <?php endif; ?>
            <?php else: ?>
                <span>Tout le monde</span>
            <?php endif; ?>
        </p>
        <p class="task-checkbox"><input class="filled-in" type="checkbox" data-task="<?= $task->getId() ?>" id="check-<?= $task->getId() ?>" <?php if($task->isCheck($_SESSION['USER_DATA'])) echo "checked" ?>><label for="check-<?= $task->getId() ?>"></label></p>
    </div>

<?php endforeach; ?>

