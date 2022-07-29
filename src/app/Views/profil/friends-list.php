<?php if(!is_null($friends) && !empty($friends)): ?>
    <div class="row">
        <ul class="friends-list collection">
            <?php foreach ($friends as $friend): ?>
                <li class="collection-item avatar">
                    <a href="?page=profile&id=<?= $friend->getID() ?>">
                        <img src="<?= $friend->getPicture() ?>" alt="" class="circle">
                        <span class="title grey-text text-darken-4"><?= $friend->getLastname(
                            ) . " friends-list.php" . $friend->getFirstname() ?></span>
                        <p class="grey-text"><?= $friend->getLocation()->getCity() ?></p>
                        <?php if($connectedUser->equals($user)): ?>
                            <a class="user_delete secondary-content grey-text" data-id="<?= $friend->getID() ?>"><i class="material-icons">close</i></a>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>