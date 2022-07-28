<div class="profile-head  row">
    <div class="global col s12" style="background-image: url('/assets/img/pexels-photo-430207.jpeg')">
        <div class="layer">

            <div class="row">
                <div class="head-user col s12">

                    <img src="<?= $user->getPicture() ?>" alt="" class="circle">
                    <span class="white-text"><?= $user->getLastname() . " " . $user->getFirstname() ?></span><p id="head_user_friend"><?= $cmdFriend ?></p>

                </div>
            </div>

        </div>
    </div>
</div>

<div class="row">
    <div id="profile_content" class="profile-content col s12">

            <?= $profileContent ?>

    </div>
</div>