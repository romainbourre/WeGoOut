<?php

use App\Librairies\AppSettings;

?>
<div class="row sail justify-content-center align-items-center">

    <div id="registration-user-promotion" class="promo-registration center-align col s12 m6 offset-m3 xl5 offset-xl1">
        <h1>Organisez. Bougez. Partagez !</h1>
    </div>


    <div id="registration-user-registration-card" class="card-registration-user z-depth-0 white col s12 m6 offset-m3 xl4 offset-xl1">

        <div class="intro col s12 l10 offset-l1">
            <h4>Inscription</h4>

            <p class="helper">
                Pour vous inscrire remplissez tout les champs ci-dessous. Vous allez voir, c'est rapide !
            </p>
        </div>

        <form id="registration-user-form" method="post" action="?page=register" class="col s12 l10 offset-l1" data-type="0">

            <div class="row">
                <div class="input-field col s12 l6">
                    <label for="registration-user-lastName-field">Nom</label>
                    <input type="text" id="registration-user-lastName-field" name="registration-user-lastName-field" maxlength="50" data-length="50" required>
                    <div id="registration-user-lastName-feedback" class="feedback"></div>
                </div>
                <div class="input-field col s12 l6">
                    <label for="registration-user-firstName-field">Prénom</label>
                    <input  type="text" id="registration-user-firstName-field" name="registration-user-firstName-field" maxlength="50" data-length="50" required>
                    <div id="registration-user-firstName-feedback" class="feedback"></div>
                </div>
            </div>

            <div class="row">
                <div class="col s12 l6">
                    <label for="registration-user-birthDate-field">Date de naissance</label>
                    <input class="datepicker" type="date" id="registration-user-birthDate-field" name="registration-user-birthDate-field" placeholder="JJ/MM/AAAA" data-minage="<?= (new AppSettings())->getMinAgeUser() ?>" required>
                    <div id="registration-user-birthDate-feedback" class="feedback"></div>
                </div>
                <div class="col s12 l6">
                    <label for="registration-user-sex-select">Sexe</label>
                    <select id="registration-user-sex-select" name="registration-user-sex-select" required>
                        <option selected disabled>Sexe</option>
                        <option value="H">Homme</option>
                        <option value="F">Femme</option>
                    </select>
                    <div id="registration-user-sex-feedback" class="feedback"></div>
                </div>
            </div>

            <div class="row">
                <div class="input-field col s12 l6">
                    <label for="registration-user-email-field">E-mail</label>
                    <input type="email" id="registration-user-email-field" name="registration-user-email-field" required>
                    <div id="registration-user-email-feedback" class="feedback"></div>
                </div>
                <div class="input-field col s12 l6">
                    <label class="form-control-label" for="registration-user-password-field">Mot de passe</label>
                    <input type="password" class="form-control" id="registration-user-password-field" name="registration-user-password-field" data-minlength="5" required>
                    <div id="registration-user-password-feedback" class="feedback"></div>
                </div>
            </div>

            <div id="form_group_cityname" class="row">
                <div class="col s12">
                    <label for="registration-user-location-field">Ville</label>
                    <input type="text" class="form-control autocomplete" id="registration-user-location-field" name="registration-user-location-field" aria-describedby="cityHelp" placeholder="Saisissez votre ville d'habitation" required>
                    <ul class="autocomplete-result autocomplete-content collection"></ul>
                    <div id="registration-user-location-feedback" class="feedback"></div>
                    <input type="hidden" id="registration-user-postalCode-hidden" name="registration-user-postalCode-hidden" readonly>
                    <input type="hidden" id="registration-user-city-hidden" name="registration-user-city-hidden" readonly>
                    <input type="hidden" id="registration-user-country-hidden" name="registration-user-country-hidden" readonly>
                    <input type="hidden" id="registration-user-placeId-hidden" name="registration-user-placeId-hidden" readonly required>
                    <input type="hidden" id="registration-user-latitude-hidden" name="registration-user-latitude-hidden" readonly required>
                    <input type="hidden" id="registration-user-longitude-hidden" name="registration-user-longitude-hidden" readonly required>
                </div>
            </div>

            <div id="form_group" class="form-group">
                <div id="reg_feedback" class="form-control-feedback"></div>
            </div>

            <button type="button" id="registration-user-form-submit" class="btn-large waves-effect waves-light disabled">S'inscrire</button>

        </form>

    </div>

</div>
