<div class="row left-align">
    <div class="white col s12">


                <?php if(isset($invitationForm)) echo $invitationForm ?>


        <div class="row">
            <div id="coll_part_filter" class="part-filter-select col s12">
                <?php if(isset($participantsFilter)) echo $participantsFilter ?>
            </div>
        </div>

        <div class="row">
            <div id="coll_part_list" class="center-align col s12">
                <?php if(isset($participantsList)) echo $participantsList ?>
            </div>
        </div>

    </div>
</div>