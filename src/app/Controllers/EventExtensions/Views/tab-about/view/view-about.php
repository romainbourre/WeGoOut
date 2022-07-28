<div class="left-align row">
    <div class="sheet-about white col s12">

        <div class="row">
            <div class="col s10 push-s1">
                <i class="left material-icons">pin_drop</i>
                <b>Adresse : </b>
                <?= $event->getLocation()->getSmartAddress() ?>
            </div>
        </div>

        <div class="row">
            <div class="col s10 push-s1">
                <i class="left material-icons">location_searching</i>
                <b>Précision sur le lieu de rendez-vous : </b>
                <?php if(!empty($desc = $event->getAddressComplements())): ?>
                    <?= $desc ?>
                <?php else: ?>
                    pas de précision.
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>