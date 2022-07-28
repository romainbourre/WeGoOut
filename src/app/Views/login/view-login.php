<div class="sail row">

    <form id="login-user-form" class="white z-depth-1 col s12 offset-s0 m6 offset-m3 l4 offset-l4 xl2 offset-xl5" method="post">

        <div class="row">
            <div class="intro col s12">
                <h4>Connexion</h4>
            </div>
        </div>

        <div class="row login-user-connect-bloc">
            <div class="input-field col s12 xl12 offset-xl0">
                <label for="login-user-email-field">Adresse e-mail</label>
                <input type="email" class="validate" id="login-user-email-field" name="login-user-email-field" autocomplete="on">
                <div id="login-user-email-feedback" class="feedback"></div>
            </div>
            <div class=" input-field col s12 xl12 offset-xl0">
                <label for="login-user-password-field" data-error="Saisie incorrect" data-success="right">Mot de passe</label>
                <input type="password" class="validate" id="login-user-password-field" name="login-user-password-field" autocomplete="on">
                <div id="login-user-password-feedback" class="feedback"></div>
            </div>
        </div>


        <div class="row login-user-remember-bloc">
            <div class="col s12">
                <p>
                    <input type="checkbox" id="login-user-remember-checkbox" checked/>
                    <label for="login-user-remember-checkbox">Rester connecté</label>

                    <button type="button" id="login-user-form-submit" class="btn-large waves-effect waves-light disabled">Connexion</button>
                    <a class="forgot-pwd-label" href="/reset-password">Mot de passe oublié ?</a>

                </p>
            </div>




        </div>

    </form>

</div>



