<?php if(!is_null($users) && !empty($users)): ?>
    <div class="row">
        <h5>Ils me demandent en amis</h5>
        <ul class="friends-list collection">
            <?php foreach ($users as $friend): ?>
                <a href="?page=profile&id=<?= $friend->getID() ?>">
                    <li class="collection-item avatar">
                        <img src="<?= $friend->getPicture() ?>" alt="" class="circle">
                        <div>
                            <span class="title grey-text text-darken-4"><?= $friend->getLastname(
                                ) . " friends-request-list.php" . $friend->getFirstname() ?></span>
                            <p class="city grey-text"><?= $friend->getLocation()->getCity() ?></p>
                        </div>
                        <div class="friends-list-choice">
                            <a class="user_accept part_set_valid btn waves-effect waves-light green" data-id="<?= $friend->getID() ?>"><i class="material-icons">done</i></a>
                            <a class="user_refuse part-set-delete btn waves-effect waves-light red" data-id="<?= $friend->getID() ?>"><i class="material-icons ">close</i></a>
                        </div>
                    </li>
                </a>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>