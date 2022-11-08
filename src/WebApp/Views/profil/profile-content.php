<?php

use Business\ValueObjects\FrenchDate;

?>
<div class="row">
    <div class="panel-top col s12">


        <div class="panel-top-card center-align col s2">
            <p class="card-content"><?= $user->getNumberOfEventsWhichUserParticipate() ?></p>
            <span class="card-title grey-text">
                    Participation
                </span>
        </div>



        <div class="panel-top-card center-align col s2">
            <p class="card-content"><?= $user->getNumberOfEventsWhichUserOrganize() ?></p>
            <span class="card-title grey-text">
                    Organisation
                </span>
        </div>


    </div>
</div>

<div class="row">

    <div class="col s12 l4">

        <?php if(!empty($desc = $user->description)): ?>
            <div class="row">
                <div class="card light-blue">
                    <div class="card-content white-text">
                <span class="card-title">
                    A propos de moi !
                </span>
                        <p>
                            <?= $desc ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <ul class="profile-details collection">
                <li class="collection-item">
                    <div class="row">
                        <div class="col s7 grey-text darken-1">
                            <i class="left material-icons"><i class="material-icons">wc</i></i>
                            Sexe
                        </div>
                        <div class="col s5 grey-text text-darken-4 right-align">
                            <?= $user->computeGenre() ?>
                        </div>
                    </div>
                </li>
                <li class="collection-item">
                    <div class="row">
                        <div class="col s7 grey-text darken-1">
                            <i class="left material-icons"><i class="material-icons">location_city</i></i>
                            Ville
                        </div>
                        <div class="col s5 grey-text text-darken-4 right-align">
                            <?= $user->getLocation()->getCity() ?>
                        </div>
                    </div>
                </li>
                <li class="collection-item">
                    <div class="row">
                        <div class="col s7 grey-text darken-1">
                            <i class="left material-icons">cake</i>
                            Date de naissance
                        </div>
                        <div class="col s5 grey-text text-darken-4 right-align">
                            <?= $user->getBirthDate()->getDate(FrenchDate::SHORT_FORMAT) ?>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <div class="row">
            <div class="card center-align amber darken-2">
                <div class="card-content white-text">
                <span class="card-title">
                    <i class="material-icons">group_add</i>
                    <?= $user->getNumberOfFriends() ?>
                </span>
                    <p>
                        Amis
                    </p>
                </div>
            </div>
        </div>

        <?= $friendsList ?>

    </div>

    <div class="col s12 l6 push-l1">

        <?= $friendsRequestList ?>

        <?= $contentProfileEvents ?>

    </div>

</div>