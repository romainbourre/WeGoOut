 <div class="row">
    <div class="no-padding col s12 l8 offset-l2">

        <div class="row">
            <div class="new-publication white col s12">

                <form id="form_new_publication">

                    <div class="row">
                        <div class="new-publication-text input-field col s12">
                            <textarea id="form_new_publication_text" name="form_new_publication_text" class="materialize-textarea" required></textarea>
                            <label for="form_new_publication_text">Exprimez-vous</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="new-publication-cmd input-field right-align col s12">
                            <span id="form_new_publication_send" class="waves-effect waves-light indigo darken-3 btn" >Publier</span>
                        </div>
                    </div>

                </form>

            </div>
        </div>

        <div class="row">
            <div id="list_publications" class="list-publications center-align col s12">

                <?php if(isset($publications)) echo $publications ?>

            </div>
        </div>

    </div>
</div>