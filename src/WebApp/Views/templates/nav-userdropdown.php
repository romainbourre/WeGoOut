<ul id="dropdown_user_menu" class="dropdown-content">
    <li><a class="grey-text"><?= $connectedUser->getEmail(); ?></a></li>
    <?php if(isset($userMenu)) echo $userMenu ?>
</ul>