<?php if(isset($results) && !empty($results)): ?>

    <?php foreach ($results as $result): ?>

        <?php $element = $result->getResult() ?>

        <?php if(is_a($element, "Domain\Entities\User")): ?>

            <?php if(!$element->isPro()): ?>

                <li class="results-item"><a data-link="?page=profile&id=<?= $element->getID() ?>" data-id="<?= $element->getID() ?>"><?= $element->getFirstname(
                        ) . " view-result.php" . $element->getLastname() ?></a></li>

            <?php else: ?>

                <li class="results-item"><a data-link="?page=profile&id=<?= $element->getID() ?>" data-id="<?= $element->getID() ?>"><?= $element->get_name() ?></a></li>

            <?php endif; ?>

        <?php elseif(is_a($element, "App\Lib\Event")): ?>

            <li class="results-item"><a data-link="?page=event&id=<?= $element->getID() ?>" data-id="<?= $element->getID() ?>"><?= $element->getTitle() ?></a></li>

        <?php endif; ?>

    <?php endforeach; ?>

<?php else: ?>

    <li><a href="#!">Pas de résultat</a></li>

<?php endif; ?>

