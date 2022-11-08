<?php

use Business\Entities\Task;

$event = $task->getEvent(); ?>
<ul id="task-slide-out" data-activates="task-slide-out" class="side-nav">

    <form id="task-form-edit" data-task="<?= $task->getId() ?>" class="left-align">

        <div id="task-slide-cmd" class="row">
            <div class="right-align col s12">
                <?php
                if ($event->isCreator($connectedUser) || $event->isOrganizer($connectedUser)): ?>
                    <a id="task-delete" class="red-text"><i class="material-icons">delete</i></a>
                <?php endif; ?>
                <a id="task-close" data-activates="task-slide-out" class="grey-text"><i class="material-icons">close</i></a>
            </div>
        </div>

        <div id="task-slide-content" class="row">

            <div id="task-slide-name" class="col s12">
                <p>
                    <input class="browser-default" type="checkbox" name="task-check" id="task-check" <?php if($task->isCheck($connectedUser)) echo "checked" ?>>
                    <label for="task-check"></label>
                </p>
                <input placeholder="Tâche" id="task-label" name="task-label" type="text" class="validate" value="<?= $task->getLabel() ?>" <?php if(!$event->isCreator($connectedUser) && !$event->isOrganizer($connectedUser)) echo "readonly" ?>>
            </div>

            <div class="task-slide-chapter col s12 left-align">
                <h5>Général</h5>
            </div>

            <div class="input-field col s12">
                <select id="task-category" name="task-category" <?php if(!$event->isCreator($connectedUser) && !$event->isOrganizer($connectedUser)) echo "disabled" ?>>
                    <?php foreach (Task::getCategories() as $key => $label): ?>
                        <option value="<?= $key ?>" <?php if(!is_null($category = $task->getCategory()) && $category == $key) echo "selected" ?>><?= $label ?></option>
                    <?php endforeach; ?>
                    <option value="0" <?php if(is_null($task->getCategory())) echo "selected" ?>>Autres</option>
                </select>
                <label for="task-category">Catégories</label>
            </div>

            <div class="input-field col s12">
                <input id="task-deadline" name="task-deadline" type="text" placeholder="JJ/MM/AAAA" class="validate datepicker" value="<?php if(!is_null($deadline = $task->getDatetimeDeadline())) echo $deadline->format('d/m/Y') ?>"  <?php if(!$event->isCreator($connectedUser) && !$event->isOrganizer($connectedUser)) echo "disabled" ?>>
                <label class="active" for="echeance">Échéance</label>
            </div>

            <div class="input-field col s12">
                <select id="task-visibility" name="task-visibility"  <?php if(!$event->isCreator($connectedUser) && !$event->isOrganizer($connectedUser)) echo "disabled" ?>>
                    <option value="<?= Task::VISIBILITY_ORGANIZER ?>" <?php if($task->getVisibility() == Task::VISIBILITY_ORGANIZER) echo "selected" ?>>Organisateurs</option>
                    <option value="<?= Task::VISIBILITY_ALL ?>" <?php if($task->getVisibility() == Task::VISIBILITY_ALL) echo "selected" ?>>Tout le monde</option>
                </select>
                <label for="task-visibility">Visibilité</label>
            </div>

            <?php if($task->getVisibility() != Task::VISIBILITY_ALL): ?>

            <div class="input-field col s12">
                <select id="task-designation" name="task-designation" <?php if($task->getVisibility() == Task::VISIBILITY_ALL) echo "readonly" ?>>
                    <?php if($task->getVisibility() == Task::VISIBILITY_ORGANIZER): ?>
                        <option value="0">Libre</option>
                        <?php foreach ($task->getEvent()->getOrganizers() as $organizer): ?>
                            <option value="<?= $organizer->getID() ?>" <?php if(!is_null($userDesignated = $task->getUserDesignated()) && $userDesignated->getID() == $organizer->getID()) echo "selected" ?>><?= $organizer->getName("full") ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <label for="task-designation">Attribuer à</label>
                <small class="grey-text">Ceci est une indication pour aider l'utilisateur</small>
            </div>

            <div class="task-slide-chapter col s12 left-align">
                <h5>Budget</h5>
            </div>

            <div class="input-field col s12">
                <input id="task-price" name="task-price" type="number" class="validate" value="<?php if($price = $task->getPrice()) echo $price ?>">
                <label class="active" for="task-price">Coût (€)</label>
            </div>

            <div class="input-field col s12">
                <select id="task-spent-affect" name="task-spent-affect">
                    <option value="<?= Task::SPENT_EVENT ?>" <?php if($task->getPriceAffect() == Task::SPENT_EVENT) echo "selected" ?>>à l'évènement</option>
                    <option value="<?= Task::SPENT_PERSON ?>" <?php if($task->getPriceAffect() == Task::SPENT_PERSON) echo "selected" ?>>par personne</option>
                </select>
                <label for="task-spent-affect">Affecter la dépense</label>
                <small class="grey-text">Ceci est une indication pour aider l'utilisateur</small>
            </div>

            <div class="col s12">
                <p>
                    <input type="checkbox" class="filled-in" id="task-spent-onplace" name="task-spent-onplace" <?php if($task->isSpentOnPlace()) echo "checked" ?>/>
                    <label for="task-spent-onplace">A payer sur place</label>
                </p>
            </div>

            <?php endif; ?>

            <div class="task-slide-chapter col s12 left-align">
                <h5>Notes</h5>
            </div>

            <div id="task-slide-note" class="col s12">
                <textarea id="task-notes" name="task-notes" class="yellow lighten-4 materialize-textarea" placeholder="Notez des informations ou des consignes particulières" <?php if(!$event->isCreator($connectedUser) && !$event->isOrganizer($connectedUser)) echo "readonly" ?>><?= $task->getNote() ?></textarea>
            </div>

        </div>

    </form>

</ul>