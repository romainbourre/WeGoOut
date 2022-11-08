<div class="row right-align">
    <div class="row">
        <div class="col s12">
            <ul class="tabs">
                <?php foreach ($tabs as $order => list($name, $content)): ?>
                    <li class="tab col"><a href="#tab_<?= $order ?>"><?= $name ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>


    <div class="row">
        <div class="tabs-content col s12">
            <?php foreach ($tabs as $order => list($name, $content)): ?>
                <div id="tab_<?= $order ?>" class="col s12"><?= $content ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>