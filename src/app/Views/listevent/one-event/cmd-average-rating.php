<div class="sheet-avg-rating row center-align">
    <div class="no-padding col s12">
     <?php if(!is_null($averageRating)): ?>
        <div class="row">
            <div class="text-note col s12">
                <span>Moyenne : <b><?= number_format($averageRating, 1, ",", " ") ?>/5</b></span>
            </div>
        </div>

        <div class="row">
            <div class="col s12">
                <span>
                    <?php

                    for($i = 0; $i < 5; $i++) {

                        if($i >= round($averageRating)) {
                            echo "<i class='material-icons amber-text accent-3'>star_border</i>";
                        }
                        else {
                            echo "<i class='material-icons amber-text accent-3'>star</i>";
                        }

                    }

                    ?>
                </span>
            </div>
            <span>(<?= $event->getNumbReviews() ?> avis)</span>
        </div>

        <!--<div class="row">
            <div class="col s12">
                <a>Laisser un avis</a>
            </div>
        </div>-->
        <?php else: ?>
        <div class="row">
            <div class="col s12">
                <b>Pas encore de note</b>
            </div>
        </div>
         <!--<div class="row">
             <div class="col s12">
                 <a>Laisser le premier avis</a>
             </div>
         </div>-->
        <?php endif; ?>
    </div>
</div>