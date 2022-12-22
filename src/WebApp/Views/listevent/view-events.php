<?php

use Business\ValueObjects\FrenchDate;

?>
<?php if(!empty($list)): ?>

    <?php foreach ($list as $date => $event): ?>

        <div class="row">
            <?php if(date("d/m/Y", $date) == date("d/m/Y", time())): ?>
                <h2 class="left-align label-date col s12 xl6 push-xl3">Aujourd'hui</h2>
            <?php else: ?>
                <h2 class="left-align label-date col s12 xl6 push-xl3"><?= (new FrenchDate($date))->getDate() ?></h2>
            <?php endif; ?>
        </div>

        <?php foreach ($event as $element): ?>

            <?php

            $participants = $element->getParticipants($connectedUser);
            $friends = $connectedUser->getFriends();
            $friendsParticipant = array();
            foreach($participants as $participant) {
                foreach($friends as $friend) {
                    if($participant->equals($friend)) $friendsParticipant[] = $participant;
                }
            }
            $numberFriends = count($friendsParticipant);

            $superCard = ($element->getUser()->getID() == $connectedUser->getID() || $element->isParticipant($connectedUser) || $element->isInvited($connectedUser) || $numberFriends > 0);

            if($superCard) {
                $cardSize = "s12";
                $superCardMsg = "";
                if($element->getUser()->getID() == $connectedUser->getID()) { if($element->isOver()) $superCardMsg = "Vous avez organisé"; else $superCardMsg = "Vous organisez"; }
                else if($element->isParticipant($connectedUser)) { if($element->isOver()) $superCardMsg = "Vous avez participé"; else $superCardMsg = "Vous participez"; }
                else if($element->isInvited($connectedUser)) { if(!$element->isOver()) $superCardMsg = "Vous êtes invité"; }
                else if($numberFriends > 0) {
                    $maxDisplayFriends = 2;
                    $diff = $numberFriends - $maxDisplayFriends;
                    for($i = 0; $i < $maxDisplayFriends-1; $i++) {
                        $fp = $friendsParticipant[$i];
                        if($numberFriends == 2) $superCardMsg .= " et ";
                        if($numberFriends > 2) $superCardMsg .= ", ";
                        $superCardMsg .= "<a href='?page=profile&id=" . $fp->getID() . "'>" . $fp->lastname . " " . $fp->firstname . "</a>";
                    }
                    if($diff > 0) $superCardMsg .= " et " . $diff . " amis";
                    if($numberFriends > 1 && $element->isOver()) {
                        $superCardMsg = "ont participé à cet évènement";
                    }
                    else if($numberFriends > 1 && !$element->isOver()) {
                        $superCardMsg .= " participent à cet évènement";
                    }
                    else if($numberFriends == 1 && $element->isOver()) {
                        $superCardMsg = "a participé à cet évènement";
                    }
                    else if($numberFriends == 1 && !$element->isOver()) {
                        $superCardMsg .= " participe à cet évènement";
                    }
                }
            }
            else {
                $cardSize = "s12 m12 xl6 push-xl3";
            }

            if($element->isStarted() || $element->isOver() || ($element->isPrivate() && !$element->isGuestOnly() &&  $element->getNumbParticipants() == $element->getMaxPart())) {
                $cardColor = "grey lighten-2 grey-text";
                $cardColorTextUser = "grey-text";
                $cardColorCategoryText = "grey-text";
                $cardColorTitleText = "grey-text";
                $cardColorDateLocationText = "grey-text";
                $cardEffect = "";
            }
            else {
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
                            <a class="<?= $cardColorTextUser ?>" href="/profile/<?= $element->getUser()->getID() ?>" ><?php if(($profilPicture = $element->getUser()->getPicture()) != ""): ?> <img src="<?= $profilPicture ?>" alt="..." class="circle responsive-img"><?php endif; ?><?= $element->getUser(
                                )->lastname . " " . $element->getUser()->firstname ?></a>
                        </div>
                        <div class="card-event-category right-align hide-on-med-and-down <?= $cardColorCategoryText ?> col s12 m3 xl4">
                            <?= $element->getCategory()[1] ?><i class="material-icons">local_offer</i>
                        </div>
                        <div class="card-event-category left-align hide-on-large-only <?= $cardColorCategoryText ?> col s12 m3 xl4">
                            <?= $element->getCategory()[1] ?><i class="material-icons left">local_offer</i>
                        </div>
                    </div>

                    <div class="row card-content">
                        <div class="left-align col s12 m8 xl8">
                            <div class="row">
                                <a href="/events/<?= $element->getID() ?>" class="card-event-title <?= $cardColorTitleText ?> col s12 m12 xl12"><?= $element->getTitle() ?></a>
                            </div>
                            <div class="row">
                                <div class="card-event-datelocation <?= $cardColorDateLocationText ?> col s12 xl12">
                                    <small>
                                        <i class="material-icons">schedule</i>
                                        <?php if($element->isOver()): ?>
                                            Terminé
                                        <?php
                                        elseif ($element->isStarted()): ?>
                                            Commencé
                                        <?php
                                        elseif (is_null($element->getDatetimeEnd())): ?>
                                            à <?= date("H\hi", $element->getDatetimeBegin()) ?>
                                        <?php
                                        else: ?>
                                            de <?= date("H\hi", $element->getDatetimeBegin()) ?><?php
                                            if (date("d/m/Y", $element->getDatetimeEnd()) == date(
                                                    "d/m/Y",
                                                    $element->getDatetimeBegin()
                                                )): ?> à <?= date("H\hi", $element->getDatetimeEnd()) ?><?php
                                            else: ?> au <?= date("d/m/Y", $element->getDatetimeEnd()) ?> à <?= date(
                                                "H\hm",
                                                $element->getDatetimeEnd()
                                            ) ?><?php
                                            endif; ?>
                                        <?php
                                        endif; ?>
                                    </small>
                                    <small>
                                        <i class="material-icons">location_on</i> à <?= $element->getCity() ?>
                                        <?php
                                        $distance = $location->getKilometersDistance($element->getLocation());
                                        if ($distance < 5) {
                                            echo floor($distance * 100) / 100 . " Km";
                                        } else {
                                            echo round($distance) . " km";
                                        }
                                        ?>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="col s12 xl4">
                            <?php
                            $price = $element->getPrice();
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

                                    if($element->getStatus() == 1) {
                                        echo "<i class=\"material-icons\">public</i> public";
                                    }
                                    else {
                                        echo "<i class=\"material-icons\">vpn_key</i> Privé";
                                    }

                                    ?>
                                </div>
                                <div class="card-event-part grey-text left-align col s12 xl12">
                                    <p class=""><i class="material-icons label-part-i">people</i> <?= $element->getNumbParticipants() ?><?php if(!$element->isGuestOnly()) echo "/" . $element->getMaxPart() ?> participant(s)</p>
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
