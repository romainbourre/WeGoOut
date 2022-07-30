<?php if(isset($results) && !empty($results)): ?>

    <?php foreach ($results as $result): ?>

        <?php $element = $result->getResult() ?>

        <?php if(is_a($element, "Domain\Entities\User")): ?>
            <li class="results-item"><a data-link="?page=profile&id=<?= $element->getID() ?>" data-id="<?= $element->getID() ?>"><?= $element->firstname . " " . $element->lastname ?></a></li>
        <?php elseif(is_a($element, "App\Lib\Event")): ?>

            <li class="results-item"><a data-link="?page=event&id=<?= $element->getID() ?>" data-id="<?= $element->getID() ?>"><?= $element->getTitle() ?></a></li>

        <?php endif; ?>

    <?php endforeach; ?>

<?php else: ?>

    <li><a href="#!">Pas de résultat</a></li>

<?php endif; ?>

