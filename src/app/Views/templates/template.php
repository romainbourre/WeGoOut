<!DOCTYPE html>

<html lang="fr">

<head>

    <title><?php if (isset($titleWebPage)) echo $titleWebPage ?></title>

    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0"/>

    <link rel="stylesheet" href="/assets/materialize/css/materialize.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/toolkit.css">
    <link rel="stylesheet" href="/assets/css/create-event.css">
    <?php if (isset($css)) echo $css ?>

</head>

<body>

<header>

    <!-- Dropdown Structure -->
    <?php if (isset($navUserDropDown)) echo $navUserDropDown ?>

    <nav style="">
        <div class="nav-wrapper row indigo darken-3"
             style="display:flex !important;flex-wrap: nowrap;flex-direction: row;">

            <a href="#" data-activates="mobile-demo" class="button-collapse"
               style="float:left;position: absolute !important;"><i class="material-icons">menu</i></a>

            <div style="display:flex;flex: auto; width:auto">
                <a href="/" class="brand-logo"
                   style="display:inline-block;float:left;position:relative"><?= CONF['Application']['Name']; ?></a>
                <div class="hide-on-med-and-down"
                     style="display:flex;flex-direction: row;flex-wrap: nowrap;margin-left:40px;margin-right:10px;width:100%">
                    <i class="material-icons left">search</i>
                    <input type="text"
                           class="search-autocomplete"
                           data-search="all"
                           data-link="on"
                           style="background:white;color:black;padding-left:5px;padding-right:5px;border-radius:5px;height:36px;flex:auto;width:auto;max-width:500px;margin-top: auto;margin-bottom:auto"
                           placeholder="Rechercher">
                </div>
            </div>

            <?php if (isset($navAddEvent)): ?>
                <a href="#create-event-modal-window"
                   class="modal-trigger hide-on-large-only btn-floating btn-large halfway-fab waves-effect waves-light teal green darken-2">
                    <i class="material-icons">add</i>
                </a>
            <?php endif; ?>

            <ul class="right hide-on-med-and-down" style="width:670px;text-align: right">
                <div style="float:right">
                    <?php if (isset($navAddEvent)) echo $navAddEvent ?>
                    <?php if (isset($navItems)) echo $navItems ?>
                    <?php if (isset($navUserDropDown)): ?>
                        <?php if (isset($navUserDropDown)): ?>
                            <li><a class="dropdown-button" href="#!"
                                   data-activates="dropdown_user_menu"><?= $connectedUser->firstname . " " . $connectedUser->lastname ?>
                                <i class="material-icons right">arrow_drop_down</i></a></li><?php endif; ?>
                    <?php endif; ?>
                </div>
            </ul>
            <ul class="side-nav" id="mobile-demo">
                <?php if (isset($navItems)) echo $navItems ?>
                <?php if (isset($userMenu)) echo $userMenu ?>
            </ul>


        </div>
    </nav>

</header>

<main>

    <?php if (isset($content)) echo $content ?>

</main>

<footer class="page-footer red lighten-1">
    <div class="container">
        <div class="row">
            <div class="col l6 s12">
                <h5 class="white-text"><?= CONF['Application']['Name'] ?></h5>
                <p class="grey-text text-lighten-4"><?= CONF['Application']['Name'] ?> a vocation à vous faire bouger et
                    rencontrer du monde avec qui partager toujours plus d'activités.</p>
            </div>
            <div class="col l4 offset-l2 s12">
                <h5 class="white-text">Aidez nous</h5>
                <ul>
                    <li>Faites nous des retours et des propositions en nous laissant un <a class="blue-text" target="_blank" href="https://myfeedbacks.fr/fr/projects/4b9e3924-6860-4be7-a103-a90c640ad011"><b>feedback</b></a> !</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-copyright">
        <div class="container">
            © 2017 <?= CONF['Application']['Name'] ?>
        </div>
    </div>
</footer>

<script src="/assets/jquery/jquery-3.2.1.js"></script>
<script src="/assets/elastic/jquery.elastic.source.js"></script>
<script src="/assets/materialize/js/materialize.js"></script>
<script type="module" src="//ajax/ctrl-a.js"></script>
<script type="module" src="/assets/js/js-global.js"></script>
<?php if (isset($js)) echo $js ?>

</body>
</html>
