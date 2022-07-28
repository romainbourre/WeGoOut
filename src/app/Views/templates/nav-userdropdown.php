<ul id="dropdown_user_menu" class="dropdown-content">
    <li><a class="grey-text"><?= $_SESSION['USER_DATA']->getEmail(); ?></a></li>
    <?php if(isset($userMenu)) echo $userMenu ?>
</ul>