<div class="row no-margin">

    <div id="create-event-modal-window" class="white modal modal-fixed-footer col s12 m12 l6 offset-l3 xl4 offset-xl4">
        <div class="modal-content row">


            <div class="intro">
                <!-- TITLE OF MODAL -->
                <h4>Création d'évènement</h4>

                <!-- INDICATION MESSAGE FOR USER -->
                <p class="helper">
                    Pour créer un nouvel évènement il vous suffit de remplir les rubriques ci-dessous, à la fois simple et rapide !
                </p>
            </div>

            <form id="create-event-modal-form" class="col s12">

                <!-- CIRCLE CHOICE OF EVENT -->
                <div id="create-event-circle-group" class="">
                    <p>
                        <input type="radio" name="create-event-circle-switch" id="create-event-public-switch" value="1">
                        <label for="create-event-public-switch">Évènement public</label>
                        <br/><span class="helper grey-text">Tout le monde pourra voir votre évènement et s'y inscrire</span>
                    </p>
                    <p>
                        <input type="radio" name="create-event-circle-switch" id="create-event-private-switch" value="2">
                        <label for="create-event-private-switch">Évènement privé</label>
                        <br/><span class="helper grey-text">Seul vos amis pourront voir et s'inscrire à votre évènement, ou seulement les invités si vous le souhaitez</span>

                    </p>
                </div>

                <!-- DROP DOWN TABS -->
                <ul id="create-event-modal-tabs" class="collapsible" data-collapsible="accordion">
                    <li id="create-event-generals-group">
                        <div class="collapsible-header active"><i class="material-icons">event_note</i>Général</div>
                        <div class="collapsible-body">

                            <div class="row"> <!-- TITLE OF EVENT -->
                                <div class="input-field col s12">
                                    <label class="active" for="create-event-title-field">Nom de l'évènement</label>
                                    <input type="text" class="form-control col-12 validate" name="create-event-title-field" id="create-event-title-field" maxlength="65" data-length="65" placeholder="Choisissez un nom court et clair" autocomplete="off">
                                    <div id="create-event-title-feedback" class="feedback form-control-feedback"></div>
                                </div>
                            </div>

                            <div class="row"> <!-- CATEGORY OF EVENT -->

                                <div class="input-field col s12">
                                    <label class="active" for="create-event-category-select">Catégorie</label>
                                    <select class="validate" name="create-event-category-select"
                                            id="create-event-category-select">
                                        <option selected disabled>Sélèctionner une catégorie</option>
                                        <?php

                                        use WebApp\Librairies\AppSettings;

                                        foreach ($eventCategories as $category): ?>
                                            <option value="<?= $category[0] ?>"><?= $category[1] ?></option>
                                        <?php
                                        endforeach; ?>
                                    </select>
                                    <div id="create-event-category-feedback"
                                         class="feedback form-control-feedback"></div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="input-field col s12">
                                    <textarea name="create-event-desc-text" id="create-event-desc-text" class="materialize-textarea" placeholder="Apportez des précisions sur votre évènement, si vous le souhaitez"></textarea>
                                    <label for="create-event-desc-text" class="active">Description</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="right-align col s12">
                                    <p class="next-index btn waves-effect waves-light indigo darken-3" data-next="1">Suivant</p>
                                </div>
                            </div>

                        </div>
                    </li>
                    <li id="create-event-participants-group">
                        <div id="tab_1" class="collapsible-header"><i class="material-icons">group</i>Participants</div>
                        <div class="collapsible-body">

                            <div class="row">
                                <div class="col s12">
                                    <div class="switch">
                                        <label>
                                            <input type="checkbox" name="create-event-guest-check" id="create-event-guest-check" value="1" disabled>
                                            <span class="lever" style="margin-left:0"></span>
                                            Sur invitation seulement
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">

                                <div id="form_group_part" class="input-field col s12">
                                    <label class="active" for="create-event-participants-number">Nombre de participant :</label>
                                    <input class="form-control validate" type="number" value="<?= (new AppSettings())->getParticipantMinNumber() ?>" min="<?= (new AppSettings("DEFAULT"))->getParticipantMinNumber() ?>" max="<?= (new AppSettings("DEFAULT"))->getParticipantMaxNumber() ?>" name="create-event-participants-number" id="create-event-participants-number">
                                    <div id="create-event-participants-feedback" class="feedback form-control-feedback"></div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="right-align col s12">
                                    <p class="next-index btn waves-effect waves-light indigo darken-3" data-next="2">Suivant</p>
                                </div>
                            </div>

                        </div>
                    </li>
                    <li id="create-event-datetime-group">
                        <div class="collapsible-header"><i class="material-icons">schedule</i>Date et heure</div>
                        <div class="collapsible-body">

                            <div class="row">

                                <div class="input-field col s8">
                                    <input class="datepicker" type="date" name="create-event-dateBegin-field" id="create-event-dateBegin-field" placeholder="JJ/MM/AAAA">
                                    <label for="create-event-dateBegin-field" class="active">Date de début</label>
                                    <div id="create-event-datetimeBegin-feedback" class="feedback form-control-feedback"></div>
                                </div>

                                <div class="input-field col s4">
                                    <input class="timepicker" type="time" name="create-event-timeBegin-field" id="create-event-timeBegin-field" placeholder="HH:MM">
                                    <label for="create-event-timeBegin-field" class="active">Heure de début</label>
                                </div>

                            </div>

                            <div id="create-event-datetimeEnd-bloc" class="row">

                                <input name="create-event-datetimeEnd-active" id="create-event-datetimeEnd-active" type="hidden" value="false">

                                <div class="input-field col s8">
                                    <input class="datepicker" type="date" name="create-event-dateEnd-field" id="create-event-dateEnd-field" placeholder="JJ/MM/AAAA">
                                    <label for="create-event-dateEnd-field" class="active">Date de fin</label>
                                    <div id="create-event-datetimeEnd-feedback" class="feedback form-control-feedback"></div>
                                </div>

                                <div class="input-field col s4">
                                    <input class="timepicker" type="time" name="create-event-timeEnd-field" id="create-event-timeEnd-field" placeholder="HH:MM">
                                    <label for="create-event-timeEnd-field" class="active">Heure de fin</label>
                                </div>

                            </div>

                            <div class="col S12">
                                <a class="link" id="create-event-datetimeEnd-button" data-end="false">+ Ajouter une fin</a>
                            </div>

                            <div class="row">
                                <div class="right-align col s12">
                                    <p class="next-index btn waves-effect waves-light indigo darken-3" data-next="3">Suivant</p>
                                </div>
                            </div>

                        </div>
                    </li>
                    <li id="create-event-location-group">
                        <div class="collapsible-header"><i class="material-icons">place</i>Localisation</div>
                        <div class="collapsible-body">

                            <div class="row">
                                <div class="input-field col s12">
                                    <label for="create-event-location-field" class="active">Lieu</label>
                                    <input type="text" class="autocomplete" name="create-event-location-field" id="create-event-location-field" placeholder="Ajouter une ville ou une adresse" autocomplete="off">
                                    <ul id="create-location-result" class="autocomplete-result autocomplete-content collection"></ul>
                                    <div id="create-event-location-feedback" class="feedback form-control-feedback col-12"></div>
                                    <input type="hidden" name="create-event-address-hidden" id="create-event-address-hidden" value="">
                                    <input type="hidden" name="create-event-postal-hidden" id="create-event-postal-hidden" value="">
                                    <input type="hidden" name="create-event-city-hidden" id="create-event-city-hidden" value="">
                                    <input type="hidden" name="create-event-country-hidden" id="create-event-country-hidden" value="">
                                    <input type="hidden" name="create-event-place-hidden" id="create-event-place-hidden" value="">
                                    <input type="hidden" name="create-event-latitude-hidden" id="create-event-latitude-hidden" value="">
                                    <input type="hidden" name="create-event-longitude-hidden" id="create-event-longitude-hidden" value="">
                                </div>
                            </div>

                            <div class="row">
                                <div class="input-field col s12">
                                    <label for="create-event-comAddress-text" class="active">Précisions</label>
                                    <input type="text" name="create-event-compAddress-text" id="create-event-compAddress-text" data-length="100" placeholder="Ajouter une indication sur le lieu, si vous le souhaitez">
                                    <div id="create-event-compAddress-feedback" class="feedback form-control-feedback col-12"></div>
                                </div>
                            </div>

                        </div>
                    </li>
                </ul>


                <div class="row"> <!-- FEEDBACK CONTROL OF FORM -->
                    <div id="form_group_create" class="input-field col s12">
                        <div id="create_feedback" class="form-control-feedback"></div>
                    </div>
                </div>

            </form>

        </div>

        <div class="modal-footer"> <!-- VALIDATION OR CANCEL OF EVENT CREATION -->
            <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat ">Annuler</a>
            <a id="create-event-modal-submit" class="modal-action green darken-2 waves-effect waves-green btn disabled">Créer</a>
        </div>

    </div>

</div>