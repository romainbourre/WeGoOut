<div class="row">

    <h5>Les évènements auxquels je participe</h5>

    <ul class="collection">
        <?php if(!is_null($participation) && !empty($participation)): ?>
            <?php foreach ( $participation as $event): ?>
                <li class="collection-item"><a href="<?= "/events/{$event->getID()}" ?>"><?= $event->getTitle() ?></a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li  class="collection-item">Aucune participation</li>
        <?php endif; ?>
    </ul>

</div>

<div class="row">

    <h5>Mes évènements</h5>

    <ul class="collection">
        <?php if(!is_null($organisation) && !empty($organisation)): ?>
            <?php foreach ( $organisation as $event): ?>
                <li class="collection-item"><a target="_blank"
                                               href="<?= "/events/{$event->getID()}" ?>"><?= $event->getTitle() ?></a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li  class="collection-item">Aucune organisation</li>
        <?php endif; ?>
    </ul>

</div>