<?php

use Business\UseCases\SearchEvent\Response\FoundedEvent;
use Business\ValueObjects\FrenchDate;

?>
<?php if(!empty($list)): ?>

    <?php foreach ($list as $date => $event): ?>

        <div class="row">
            <?php if (date("d/m/Y", $date) == date("d/m/Y", time())): ?>
                <h2 class="left-align label-date col s12 xl6 push-xl3">Aujourd'hui</h2>
            <?php else: ?>
                <h2 class="left-align label-date col s12 xl6 push-xl3"><?= (new FrenchDate($date))->getDate() ?></h2>
            <?php endif; ?>
        </div>

        <?php /** @var FoundedEvent $element */
        foreach ($event as $element): ?>

            <?php

            $participants = [];
            $friends = [];
            $friendsParticipant = array();
            foreach ($participants as $participant) {
                foreach ($friends as $friend) {
                    if ($participant->equals($friend)) $friendsParticipant[] = $participant;
                }
            }
            $numberFriends = count($friendsParticipant);

            $superCard = ($element->owner->id == $connectedUser->getID() || $element->isParticipant || $element->isAwaitingParticipant || $element->isGuest || $numberFriends > 0);

            if($superCard) {
                $cardSize = "s12";
                $superCardMsg = "";
                if ($element->owner->id == $connectedUser->getID()) {
                    $superCardMsg = "Vous organisez";
                } else if ($element->isParticipant) {
                    $superCardMsg = "Vous participez";
                } else if ($element->isAwaitingParticipant) {
                    $superCardMsg = "Vous avez demandé à participer";
                } else if ($element->isGuest) {
                    $superCardMsg = "Vous êtes invité";
                } else if ($numberFriends > 0) {
                    $superCardMsg = "Vos amis participent";
                } else if ($element->isGuest) {
                    $superCardMsg = "Vous êtes invité";
                } else if ($numberFriends > 0) {
                    $maxDisplayFriends = 2;
                    $diff = $numberFriends - $maxDisplayFriends;
                    for ($i = 0; $i < $maxDisplayFriends - 1; $i++) {
                        $fp = $friendsParticipant[$i];
                        if ($numberFriends == 2) $superCardMsg .= " et ";
                        if ($numberFriends > 2) $superCardMsg .= ", ";
                        $superCardMsg .= "<a href='?page=profile&id=" . $fp->getID() . "'>" . $fp->lastname . " " . $fp->firstname . "</a>";
                    }
                    if ($diff > 0) $superCardMsg .= " et " . $diff . " amis";
                    if ($numberFriends > 1) {
                        $superCardMsg .= " participent à cet évènement";
                    } else if ($numberFriends == 1) {
                        $superCardMsg .= " participe à cet évènement";
                    }
                }
            }
            else {
                $cardSize = "s12 m12 xl6 push-xl3";
            }

            if (!is_null($element->participantsLimit) && 0 == $element->participantsLimit) {
                $cardColor = "grey lighten-2 grey-text";
                $cardColorTextUser = "grey-text";
                $cardColorCategoryText = "grey-text";
                $cardColorTitleText = "grey-text";
                $cardColorDateLocationText = "grey-text";
                $cardEffect = "";
            } else {
                $cardColor = "white black-text";
                $cardColorTextUser = "";
                $cardColorCategoryText = "red-text";
                $cardColorTitleText = "";
                $cardColorDateLocationText = "grey-text";
                $cardEffect  = "z-depth-1 hoverable";
            }

            ?>

            <?php if($superCard): ?>
                <div class="row">
                <div class="super-card white col m12 xl6 push-xl3">
                <p class="left-align"><?= $superCardMsg ?></p>
            <?php endif; ?>

            <div class="row">
                <div class="card-event <?= $cardColor . ' ' . $cardEffect ?> col <?= $cardSize ?>">


                    <div class="card-event-int row">
                        <div class="card-event-user truncate col m9 xl8 left-align">
                            <a class="<?= $cardColorTextUser ?>" href="/profile/<?= $element->owner->id ?>"><img
                                        src="/assets/img/33aeda9.png" alt="..."
                                        class="circle responsive-img"><?= $element->owner->name ?></a>
                        </div>
                        <div class="card-event-category right-align hide-on-med-and-down <?= $cardColorCategoryText ?> col s12 m3 xl4">
                            <?= $element->category ?><i class="material-icons">local_offer</i>
                        </div>
                        <div class="card-event-category left-align hide-on-large-only <?= $cardColorCategoryText ?> col s12 m3 xl4">
                            <?= $element->category ?><i class="material-icons left">local_offer</i>
                        </div>
                    </div>

                    <div class="row card-content">
                        <div class="left-align col s12 m8 xl8">
                            <div class="row">
                                <a href="/events/<?= $element->id ?>"
                                   class="card-event-title <?= $cardColorTitleText ?> col s12 m12 xl12"><?= $element->title ?></a>
                            </div>
                            <div class="row">
                                <div class="card-event-datelocation <?= $cardColorDateLocationText ?> col s12 xl12">
                                    <small>
                                        <i class="material-icons">schedule</i>
                                        <?php if (false): ?>
                                            Terminé
                                        <?php
                                        elseif (false): ?>
                                            Commencé
                                        <?php
                                        elseif (is_null($element->endAt)): ?>
                                            à <?= date("H\hi", $element->startAt->getTimestamp()) ?>
                                        <?php
                                        else: ?>
                                            de <?= date("H\hi", $element->startAt->getTimestamp()) ?><?php
                                            if (date("d/m/Y", $element->endAt->getTimestamp()) == date(
                                                    "d/m/Y",
                                                    $element->startAt->getTimestamp()
                                                )): ?> à <?= date("H\hi", $element->endAt->getTimestamp()) ?><?php
                                            else: ?> au <?= date("d/m/Y", $element->startAt->getTimestamp()) ?> à <?= date(
                                                "H\hm",
                                                $element->endAt
                                            ) ?><?php
                                            endif; ?>
                                        <?php
                                        endif; ?>
                                    </small>
                                    <small>
                                        <i class="material-icons">location_on</i> à <?= $element->city ?>
                                        <?= $element->distance ?> Km
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="col s12 xl4">
                            <?php
                            $price = null;
                            if (!is_null($price)): ?>
                                <div class="row">
                                    <div class="card-event-price left-align col s12 xl12">


                                        <p>
                                            <?php if(!is_null($price) && $price  > 0) {
                                                $price1 = (int)$price;
                                                $price2 = (int)(round($price, 2) * 100) - ($price1 * 100);
                                                //echo "<span class=\"\">" . $price1 . "€" . $price2 . "</span>";
                                                echo "<span class=\"\">" . number_format($price, 2, '€', '.') . "</span>";
                                            }
                                            else if(!is_null($price) && $price  == 0) {
                                                echo "<span class=\"\">" . strtoupper("gratuit") . "</span>";
                                            }
                                            ?>
                                        </p>


                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="row">
                                <div class="card-event-part grey-text left-align col s12 xl12">
                                    <?php

                                    if ($element->visibility == 'public') {
                                        echo "<i class=\"material-icons\">public</i> public";
                                    } else {
                                        echo "<i class=\"material-icons\">vpn_key</i> Privé";
                                    }

                                    ?>
                                </div>
                                <div class="card-event-part grey-text left-align col s12 xl12">
                                    <p class=""><i
                                                class="material-icons label-part-i">people</i> <?= $element->numberOfParticipants ?><?php if (!is_null($element->participantsLimit)) echo "/" . $element->participantsLimit ?>
                                        participant(s)</p>
                                </div>

                            </div>

                        </div>
                    </div>

                </div>


            </div>

            <?php if($superCard): ?>
                </div>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>

    <?php endforeach; ?>

<?php else: ?>

    <h2 class="label-no-event col xl4 offset-xl4">Oops !<br>Pas d'évènement selon vos critères...<br>Soyez le premier à <br><a class="btn waves-effect waves-light modal-trigger" href="#create-event-modal-window">Créer un évènement</a></h2>

<?php endif; ?>
