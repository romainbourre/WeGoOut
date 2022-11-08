<div class="sail row">

    <form id="forgot-password-form" class="white z-depth-1 col s12 offset-s0 m6 offset-m3 l4 offset-l4 xl2 offset-xl5" method="post" action="/reset-password">

        <div class="row">
            <div class="intro col s12">
                <h4>Mot de passe oublié ?</h4>
                <p class="helper">
                    Saisissez vote adresse e-mail, vous recevrez alors un nouveau mot de passe dans votre boîte de réception !
                </p>
            </div>
        </div>

        <div class="row forgot-password-email-bloc">
            <div class="input-field col s12 xl12 offset-xl0">
                <label for="forgot-password-email-field">Adresse e-mail</label>
                <input type="email" class="validate" id="forgot-password-email-field" name="forgot-password-email-field" autocomplete="on">
                <div id="forgot-password-email-feedback" class="feedback"></div>
            </div>
        </div>

        <button type="button" class="btn-large waves-effect waves-light disabled" id="forgot-password-form-submit">Réinitialiser</button>

    </form>

</div>