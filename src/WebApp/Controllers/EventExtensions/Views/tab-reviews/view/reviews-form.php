<div class="new-review white col s12">
    <form id="form_new_review">

        <p class="review-star">
            <span><b>Note : </b></span>
            <input id="form_new_review_note" name="form_new_review_note" type="hidden" required>
            <i class='star-note one material-icons amber-text accent-3' data-note="1">star_border</i>
            <i class='star-note two material-icons amber-text accent-3' data-note="2">star_border</i>
            <i class='star-note three material-icons amber-text accent-3' data-note="3">star_border</i>
            <i class='star-note for material-icons amber-text accent-3' data-note="4">star_border</i>
            <i class='star-note five material-icons amber-text accent-3'data-note="5">star_border</i>
        </p>

        <div class="left-align row">
            <div class="new-review-text input-field col s12">
                <textarea id="form_new_review_text" name="form_new_review_text" maxlength="200" placeholder="Laissez un commentaire si vous le souhaitez" data-length="200"></textarea>
            </div>
            <span id="feedback_review" class="red-text"></span>
        </div>

        <div class="row">
            <div class="review-cmd input-field right-align col s12">
                <span id="form_new_review_send" class="waves-effect waves-light indigo darken-3 btn" >Envoyer</span>
            </div>
        </div>

    </form>
</div>