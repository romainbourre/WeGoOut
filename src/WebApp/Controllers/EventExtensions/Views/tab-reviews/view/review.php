<?php if(isset($reviewsContent)) foreach ($reviewsContent as $review): ?>
    <div class="row">
        <div class="card-review left-align white col s12">

            <div class="review-user-picture col S1">
                <a class="" href="?page=profile&id=<?= $review->getUser()->getID() ?>"><img src="<?= $review->getUser()->getPicture() ?>" alt="..." class="circle responsive-img"></a>
            </div>

            <div class="col s10">

                <div class="row">
                    <div class="review-title col s12">

                <span class="review-star">
                    <?php

                    for($i = 0; $i < 5; $i++) {

                        if($i >= round($review->getNote())) {
                            echo "<i class='material-icons amber-text accent-3'>star_border</i>";
                        }
                        else {
                            echo "<i class='material-icons amber-text accent-3'>star</i>";
                        }

                    }

                    ?>
                </span>
                        <span class="review-note"><b>(<?= number_format($review->getNote(), 1, ",", " ") ?>/5)</b></span>
                    </div>
                </div>

                <?php if(!is_null($review->getComment()) && !empty($review->getComment())): ?>
                    <div class="review-content row">
                        <div class="col s12">
                            <a href="?page=profile&id=<?= $review->getUser()->getID() ?>"><?= $review->getUser(
                                )->lastname . " " . $review->getUser()->firstname ?></a>
                            : <?= $review->getComment() ?>
                        </div>
                    </div>
                <?php
                endif; ?>

                <div class="row">
                    <div class="grey-text col s12">
                        <small><?= $review->getDatetimeLeave()->getRelativeDateAndHours() ?></small>
                    </div>
                </div>


            </div>

        </div>
    </div>
<?php endforeach; ?>