<input id="event_id" type="hidden" value="<?= $event->getId() ?>">
<div class="row">
    <div class="sheet-event col s12">

        <div class="sheet-event-panel white col s12 l3">

            <div class="no-margin row">
                <div class="sheet-event-head col s12" style="background-image: url('https://static.pexels.com/photos/33129/popcorn-movie-party-entertainment.jpg')">

                    <div class="layer">


                        <div class="row">
                            <div class="sheet-event-category left-align col s12">
                        <span class="red white-text">
                        <?= $event->getCategory()[1] ?><i class="material-icons">local_offer</i>
                        </span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="sheet-event-global-title white-text col s12">
                                <h4 class="sheet-event-title"><?= $event->getTitle() ?></h4>
                                <p class="sheet-event-user">
                                    <a class="white-text" href="/profile/<?= $event->getUser()->getID() ?>" >
                                        <?php if(($profilPicture = $event->getUser()->picture) != ""): ?> <img src="<?= $profilPicture ?>" alt="..." class="circle responsive-img"><?php endif; ?>
                                        <?= $event->getUser()->lastname . " " . $event->getUser()->firstname ?>
                                        <?php if($event->isInvited($connectedUser)): ?><small class=""> vous a invité à participer</small><?php endif; ?>
                                    </a>
                                </p>
                            </div>
                        </div>

                        <div class="no-margin row">
                            <div class="sheet-event-price right-align col s12">

                                <?php
                                $price = $event->getPrice();

                                if(!is_null($price)): ?>
                                    <p class="">
                                        <?php if (!is_null($price) && $price > 0) {
                                            $price1 = (int)$price;
                                            $price2 = (int)(round($price, 2) * 100) - ($price1 * 100);
                                            //echo "<span class=\"\">" . $price1 . "€" . $price2 . "</span>";
                                            echo "<span class=\"\">" . number_format($price, 2, '€', '.') . "</span>";
                                        } else if (!is_null($price) && $price == 0) {
                                            echo "<span class=\"\">" . strtoupper("gratuit") . "</span>";
                                        } ?>
                                    </p>
                                <?php endif; ?>

                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <div class="no-margin row">
                <div class="sheet-event-details white col s12">

                    <div class="item row">
                        <div id="sheet_event_cmd" class="sheet-event-cmd center-align col s12">
                            <?php if(isset($registrationCmd)) echo $registrationCmd ?>
                        </div>
                    </div>

                    <div class="item row">
                        <div class="col s12">
                            <i class="material-icons">schedule</i>
                            <?php

                            if( date("d/m/Y", $event->getDateTimeBegin()) == date("d/m/Y", time()) ) {
                                if(is_null($event->getDateTimeEnd())) {
                                    echo "Aujourd'hui  à " . date("H\hi", $event->getDateTimeBegin());
                                }
                                else if( date("d/m/Y", $event->getDateTimeBegin()) == date("d/m/Y", $event->getDateTimeEnd()) ) {
                                    echo "Ajourd'hui de " . date("H:i", $event->getDateTimeBegin()) . " à " . date("H\hi", $event->getDateTimeEnd());
                                }
                                else {
                                    echo "Ajourd'hui jusqu'au " . date("d/m/Y", $event->getDateTimeEnd()) . " à " . date("H\hi", $event->getDateTimeEnd());
                                }
                            }
                            else {
                                if(is_null($event->getDateTimeEnd())) {
                                    echo "le " . date('d/m/Y', $event->getDateTimeBegin()) . " à " . date("H\hi", $event->getDateTimeBegin());
                                }
                                else if( date("d/m/Y", $event->getDateTimeBegin()) == date("d/m/Y", $event->getDateTimeEnd()) ) {
                                    echo "le " . date("d/m/Y", $event->getDateTimeBegin()) . " de "  . date("H\hi", $event->getDateTimeBegin()) . " à " . date("H:i", $event->getDateTimeEnd());
                                }
                                else {
                                    echo "du "  . date("d/m/Y", $event->getDateTimeBegin()) . " à "  . date("H\hi", $event->getDateTimeBegin()) . " au " . date("d/m/Y", $event->getDateTimeEnd()) . " à " . date("H:i", $event->getDateTimeEnd());
                                }
                            }

                            ?>
                        </div>
                    </div>

                    <div class="item row">
                        <div class="col s12">
                            <i class="material-icons">location_on</i> à <?= $event->getCity() ?>
                            <?php
                            $distance = $userLocation->getDistance($event->getLocation());
                            if ($distance < 5) {
                                echo floor($distance * 100) / 100 . " Km";
                            } else {
                                echo round($distance) . " km";
                            }
                            ?>
                        </div>
                    </div>

                    <div class="item row">
                        <div class="sheet-event-part col s12">
                            <?php

                            if ($event->getStatus() == 1) {
                                echo "<i class=\"material-icons\">public</i> public";
                            }
                            else {
                                echo "<i class=\"material-icons\">vpn_key</i> Privé";
                            }

                            ?>
                        </div>
                    </div>

                    <div class="last-item row">
                        <div id="sheet_event_part" class="sheet-event-part col s12">
                            <?php if(isset($numbPartItem)) echo $numbPartItem ?>
                        </div>
                    </div>

                    <?php if(!empty($desc = $event->getDescription())): ?>
                    <div class="sheet-event-desc row">
                        <div class="col s10 push-s1">
                                <b>Description : </b><?= $desc ?>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>



        </div>


        <div id="sheet_event_window" class="sheet-event-window center-align col s12 l9">
            <?php if(isset($contentWindow)) echo $contentWindow ?>
        </div>


    </div>
</div>

