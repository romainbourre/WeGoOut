<p class=""><i class="material-icons label-part-i">people</i> <?= $participantsNumber ?><?php if(!$event->isGuestOnly()) echo "/" . $event->getMaxPart() ?> participant(s)</p>
