<?php

if(!empty($notification->getAction())) $link = 'href="' . $notification->getAction() . '"'; else $link = "";
if($notification->isUnread()) $background = "blue lighten-5"; else $background = "";

?>

<li class="<?= $background ?>"><a <?= $link ?>><span class="notification-msg"><?= $notification->getMessage() ?><br><small class="grey-text"><?= $notification->getDatetimeSend()->getFrenchSmartDate() ?></small></span><i class="material-icons right"><?= $notification->icon ?></i></a></li><li class="divider"></li>
