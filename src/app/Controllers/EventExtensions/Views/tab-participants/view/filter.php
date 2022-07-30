<?php

$a = $event->countParticipant($connectedUser);
$p = $event->getNumbParticipants();
$i = $event->getNumbInvited();
$w = $event->getNumberOfAwaitingParticipant($connectedUser);

?>

<a id="part_filter_all" href="#!" class="btn-flat waves-effect waves-light on">Tous <span class="badge grey darken-3 white-text"><?= $a ?></span></a>
<a id="part_filter_valid" href="#!" class="btn-flat waves-effect waves-light">Participe <span class="badge grey darken-3 white-text"><?= $p ?></span></a>
<a id="part_filter_inv" href="#!" class="btn-flat waves-effect waves-light">Invité(s) <span class="badge grey darken-3 white-text"><?= $i  ?></span></a>
<?php if( $event->isCreator($connectedUser) || $event->isOrganizer($connectedUser) ): ?>
<a id="part_filter_wait" href="#!" class="btn-flat waves-effect waves-light">En attente <span class="badge grey darken-3 white-text"><?= $w ?></span></a>
<?php endif; ?>