<ul class="collection left-align" data-level="<?= $levelName ?>">

    <?php if(!empty($participants)): ?>

        <?php foreach ($participants as $participant): ?>

            <?php if(is_object($participant)): ?>

                <li class="collection-item avatar">
                    <a href="?page=profile&id=<?= $participant->getID() ?>">
                        <img src="<?= $participant->getPicture() ?>" alt="" class="circle">
                        <span class="title"><?= $participant->getLastname() . " " . $participant->getFirstname() ?></span>
                    </a>
                    <?php if($event->isParticipantWait($participant)): ?>
                        <?php if( ( $event->isCreator($_SESSION['USER_DATA']) || $event->isOrganizer($_SESSION['USER_DATA']) ) && !$event->isStarted() && !$event->isOver() ): ?>
                            <p class="part-set-delete btn red" data-id="<?= $participant->getID() ?>"><i class="material-icons ">close</i></p>
                            <p class="part_set_valid btn green" data-id="<?= $participant->getID() ?>"><i class="material-icons">done</i></p>
                        <?php endif; ?>
                        <p class="waves-effect waves-teal btn-flat orange-text">En attente</p>
                    <?php elseif ($event->isParticipantValid($participant)): ?>
                        <?php if( ( $event->isCreator($_SESSION['USER_DATA']) || $event->isOrganizer($_SESSION['USER_DATA']) ) && !$event->isStarted() && !$event->isOver() ): ?>
                            <p class="part-set-delete btn red" data-id="<?= $participant->getID() ?>"><i class="material-icons">close</i></p>
                            <p class="btn disabled"><i class="material-icons">done</i></p>
                        <?php endif; ?>
                        <p class="waves-effect waves-teal btn-flat green-text">Participe</p>
                    <?php elseif ($event->isInvited($participant)): ?>
                        <?php if( ( $event->isCreator($_SESSION['USER_DATA']) || $event->isOrganizer($_SESSION['USER_DATA']) ) && !$event->isStarted() && !$event->isOver() ): ?>
                            <p class="part-set-delete btn red" data-id="<?= $participant->getID() ?>"><i class="material-icons">close</i></p>
                            <p class="btn disabled"><i class="material-icons">done</i></p>
                        <?php endif; ?>
                        <p class="waves-effect waves-teal btn-flat disabled">Invité</p>
                    <?php endif; ?>
                </li>

            <?php else: ?>

                <li class="collection-item avatar">
                    <a>
                        <i class="material-icons circle">email</i>
                        <span class="title"><?= $participant ?></span>
                    </a>
                    <?php if ($event->isEmailInvited($participant)): ?>
                        <?php if( ( $event->isCreator($_SESSION['USER_DATA']) || $event->isOrganizer($_SESSION['USER_DATA']) ) && !$event->isStarted() && !$event->isOver() ): ?>
                            <p class="part-set-delete btn red" data-id="<?= $participant ?>"><i class="material-icons">close</i></p>
                            <p class="btn disabled"><i class="material-icons">done</i></p>
                        <?php endif; ?>
                        <p class="waves-effect waves-teal btn-flat disabled">Invité</p>
                    <?php endif; ?>
                </li>

            <?php endif; ?>

        <?php endforeach; ?>

    <?php else: ?>

        <li class="collection-item avatar nobody">Aucun participant</li>

    <?php endif; ?>

</ul>