<div class="row">
    <div class="col s12">

        <form id="edit_event_form" class="col s12">

            <div class="row">
                <div class="right-align col s12">
                    <a class="btn">Enregistrer</a>
                    <a class="btn">Annuler</a>
                </div>
            </div>

            <div class="row">
                <div class="edit-frame  col s12">
                    <label class="" for="edit_event_title">Titre</label>
                    <input class="" id="edit_event_title" type="text" value="<?= $event->getTitle() ?>" maxlength="100"  data-length="100" placeholder="Saisissez un titre court et clair">
                    <small class="red-text" id="feedback_title" style="float:left;margin-top:-18px;font-weight:bold"></small>
                </div>
            </div>


            <div class="row">
                <div class="edit-frame input-field col s6">

                    <h5><i class="left material-icons">local_offer</i>Détails</h5>

                    <div class="input-field col s12">
                        <label class="active" for="edit-event-category-select">Catégorie</label>
                        <select class="validate" id="edit-event-category-select">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->id ?>" <?php if ($category->id == $event->getCategory()[0]) echo "selected" ?>><?= $category->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-field col s6">
                        <textarea id="edit_event_desc"
                                  class="materialize-textarea"><?= $event->description ?></textarea>
                        <label for="edit_event_desc">Description</label>
                    </div>

                </div>


                <div class="edit-frame input-field col s6">

                    <h5><i class="left material-icons">group_work</i>Cercle</h5>

                    <div class="input-field col s12">
                        <p>
                            <input name="group1" type="radio"
                                   id="test1" <?php if ($event->isPublic()) echo "checked" ?>/>
                            <label for="test1">Public</label>
                        </p>
                        <p>
                            <input name="group1" type="radio"
                                   id="test2" <?php if ($event->isPrivate()) echo "checked" ?>/>
                            <label for="test2">Privé</label>
                        </p>
                    </div>

                    <div class="input-field">
                        <div class="switch">
                            <label>
                                <input type="checkbox" <?php if ($event->isGuestOnly()) echo "checked" ?> >
                                <span class="lever"></span>
                                Sur invitation seulement
                            </label>
                        </div>
                    </div>


                    <div class="input-field col s12">
                        <input class="<?php if($event->isGuestOnly()) echo "disabled" ?>" type="number" value="<?= $event->getMaxPart() ?>" <?php if($event->isGuestOnly()) echo "disabled" ?>>
                    </div>

                </div>
            </div>

            <div class="row">
                <div class="edit-frame col s12">

                    <h5><i class="left material-icons">schedule</i>Quand ?</h5>

                    <label class="col s12" for="">Date et heure de début</label>
                    <input id="edit_event_date_begin" class="datepicker col s5" type="date" value="<?= date("d/m/Y",$event->getDatetimeBegin()) ?>">
                    <input id="edit_event_time_begin" class="timepicker col s5 push-s1" type="time" value="<?= date("H:i",$event->getDatetimeBegin()) ?>">
                    <label class="col s12" for="">Date et heure de fin</label>
                    <input id="edit_event_date_end" class="datepicker col s5" type="date" value="<?= date("d/m/Y",$event->getDatetimeEnd()) ?>">
                    <input id="edit_event_time_end" class="timepicker col s5 push-s1" type="time" value="<?= date("H:i",$event->getDatetimeEnd()) ?>">
                    <span id="feedback_datetime" class="red-text"></span>
                </div>
            </div>



            <div class="row">
                <div class="edit-frame col s12">

                    <h5><i class="left material-icons">schedule</i>Où ?</h5>

                    <label class="" for="edit_input_completeAddress">Lieu</label>
                    <input type="text" class="activate autocomplete" name="edit_input_completeAddress" id="edit_input_completeAddress" placeholder="Ajouter une ville ou une adresse" value="<?= $event->getLocation()->getSmartAddress() ?>"
                           autocomplete="off">
                    <ul id="create-location-result" class="autocomplete-result autocomplete-content collection"></ul>
                    <small id="feedback_location" style="float:left;margin-top:-18px;font-weight:bold"></small>
                    <input type="hidden" name="edit_input_address" id="edit_input_address" value="<?= $event->getLocation()->getAddress() ?>">
                    <input type="hidden" name="edit_input_postalCode" id="edit_input_postalCode" value="<?= $event->getLocation()->getPostalCode() ?>">
                    <input type="hidden" name="edit_input_city" id="edit_input_city" value="<?= $event->getLocation()->getCity() ?>">
                    <input type="hidden" name="edit_input_placeId" id="edit_input_placeId" value="<?= $event->getLocation()->getGooglePlaceId() ?>">
                    <input type="hidden" name="edit_input_lat" id="edit_input_lat" value="<?= $event->getLocation()->latitude ?>">
                    <input type="hidden" name="edit_input_lng" id="edit_input_lng" value="<?= $event->getLocation()->longitude ?>">

                    <label class="" for="">Indication sur le lieu</label>
                    <input type="text" value="<?= $event->getLocation()->getComplements() ?>">

                </div>
            </div>


        </form>

    </div>
</div>
