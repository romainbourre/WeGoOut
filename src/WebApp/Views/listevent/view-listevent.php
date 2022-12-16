<div class="row">
    <form class="col s12 white" id="list_form_filter">
        <div class="row no-margin">
            <div class="input-field col s12 xl2">
                <div class="input-container">
                    <input placeholder="Choisissez une ville" id="list_input_city" type="text" class="browser-default list-input-city validate">
                    <i id="filter_city_clear" class="material-icons">clear</i>
                    <ul id="search-location-result" class="autocomplete-result autocomplete-content collection"></ul>
                </div>
                <!--<label for="list_input_city">Ville</label>-->
                <input id="list_input_lng" name="lng" type="hidden" value="">
                <input id="list_input_lat" name="lat" type="hidden" value="">
            </div>

            <div class="input-field col s12 xl2">
                <div class="input-container">
                    <select name="cat" id="list_select_category" class="browser-default list-input-category">
                        <option class="default" value="" style="color: rgb(153, 153, 153)" selected>Toutes les
                            catégories
                        </option>
                        <?php

                        use WebApp\Librairies\AppSettings;

                        foreach ($categories as $category): ?>
                            <option value="<?= $category->id ?>"><?= $category->name ?></option>
                        <?php
                        endforeach; ?>
                    </select>
                    <i id="filter_select_down" class="material-icons">keyboard_arrow_down</i>
                </div>
                <!--<label for="create_select_category">Catégories</label>-->
            </div>

            <div class="input-field col s12 l2 xl1">
                <div class="input-container close-input-container">
                    <input placeholder="JJ/MM/AAAA" name="date" id="list_input_date" type="date" class="datepicker validate">
                    <i id="filter_date_clear" class="material-icons">clear</i>
                </div>
                <!--<label class="active" for="first_name">Date</label>-->
            </div>

            <div class="distance-container input-field col s12 xl1 push-xl1">
                <div>Rayon :  <span id="list_label_distance">30</span> Km</div>
            </div>

            <div class="input-field col s12 xl3 push-xl1">
                <p class="range-field">
                    <input type="range" id="list_range_distance" name="dist" value="<?= (new AppSettings())->getDefaultDistance() ?>" min="10" max="60" />
                </p>
            </div>


            <div class="input-field right-align col s12 xl2 push-xl1">
                <button class="waves-effect waves-light btn" id="list_button_reset" type="reset">Effacer filtre</button>
            </div>

        </div>

    </form>
</div>

<div class="row">

    <div id="list_events" class="center-align col s12">

        <?= $contentEvents ?>

    </div>

</div>
