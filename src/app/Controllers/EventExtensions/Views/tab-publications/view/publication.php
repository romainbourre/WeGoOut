<?php use Domain\Entities\Date;

if(isset($publicationsContent)) foreach ($publicationsContent as $publication): ?>
    <div class="row">
        <div class="card-publication left-align white col s12">

            <div class="row">

                <div class="col s1">
                    <a href=""><img src="<?= $publication->getUser()->getPicture() ?>" alt="..." class="circle responsive-img"></a>
                </div>

                <div class="col s11">

                    <div class="row">
                        <div class="col s12">
                            <a href="/profile/<?= $publication->getUser()->getID()  ?>"><?= $publication->getUser(
                                )->getLastName() . " " . $publication->getUser()->getFirstName() ?></a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="grey-text col s12">
                            <small><?= (new Date($publication->getDateTime()))->getFrenchSmartDate() ?></small>
                        </div>
                    </div>

                </div>
            </div>

            <div class="row">
                <div class="card-publication-text col s12">
                    <?= $publication->getText() ?>
                </div>
            </div>

        </div>
    </div>
<?php endforeach; ?>