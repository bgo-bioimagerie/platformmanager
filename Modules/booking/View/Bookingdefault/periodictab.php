<div class="col-md-12">
    <div class="col-md-12" style="height:7px;">
    </div>
    <div class="text-center">
        <div class="btn-group btn-group-sm">


            <button class="btn btn-default <?php
                    if ($bookingType == "single") {
                        echo "active";
                    }
                    ?>" onclick="location.href = 'bookingeditreservation/<?php echo $id_space ?>/<?php echo $args ?>';"><?php echo BookingTranslator::Single($lang) ?></button>
            <button class="btn btn-default <?php
                    if ($bookingType == "periodic") {
                        echo "active";
                    }
                    ?>" onclick="location.href = 'bookingeditreservationperiodic/<?php echo $id_space ?>/<?php echo $args ?>';"><?php echo BookingTranslator::Periodic($lang) ?></button>


        </div>
    </div>
</div>