<?php include 'Modules/booking/View/layoutsettings.php' ?>

    
<?php startblock('content') ?>
<div class="container">
<div class="pm-form row mb-3">
    <form role="form" class="form-horizontal" action="bookingblockquery/<?php echo $id_space ?>"
          method="post">


        <div class="page-header">
            <h3>
                <?php echo BookingTranslator::block_resources($lang) ?>
                <br> <small></small>
            </h3>
        </div>

        <div class="form-group mb-3">
            <label for="reason" class="control-label col-3"><?php echo BookingTranslator::Reason($lang) ?></label>
                <select class="form-select" id="reason" name="reason">
                    <option value="<?php echo BkCalendarEntry::$REASON_BOOKING; ?>"><?php echo BookingTranslator::BlockReason(BkCalendarEntry::$REASON_BOOKING, $lang) ?></option>
                    <option selected value="<?php echo BkCalendarEntry::$REASON_HOLIDAY; ?>"><?php echo BookingTranslator::BlockReason(BkCalendarEntry::$REASON_HOLIDAY, $lang) ?></option>
                    <option value="<?php echo BkCalendarEntry::$REASON_MAINTENANCE; ?>"><?php echo BookingTranslator::BlockReason(BkCalendarEntry::$REASON_MAINTENANCE, $lang) ?></option>
                </select>
        </div>

        <div class="form-group mb-3">
            <label for="name" class="control-label col-3"><?php echo BookingTranslator::Short_description($lang) ?></label>
                <input class="form-control" id="name" type="text" name="short_description"
                       value=""
                       />
        </div>
        <div class="form-group mb-3">
            <label class="control-label col-3"><?php echo ResourcesTranslator::Resources($lang) ?></label>
                <select class="form-control" name="resources[]" size="10" multiple="multiple">
                    <?php
                    foreach ($resources as $resource) {
                        ?>
                        <option value="<?php echo $resource["id"] ?>"><?php echo $resource["name"] ?></option>
                        <?php
                    }
                    ?>
                </select>
        </div>

        <div class="form-group mb-3">
            <label for="begin_date" class="control-label col-3"><?php echo BookingTranslator::Beginning_of_the_reservation($lang) ?>:</label>
                    <input type='date' class="form-control" id="begin_date" name="begin_date"
                           value=""/>
        </div>
        <div class="form-group mb-3">    
        <div class="row mb-3">
                <!-- time -->

                <label for="begin_hour" class="control-label col-1"><?php echo BookingTranslator::time($lang) ?>:</label>

                <div class="col-3">
                    <input class="form-control" id="begin_hour" type="text" name="begin_hour"
                           value="" 
                           />
                </div>
                <div class="col-1">
                    :
                </div>
                <div class="col-3">
                    <input class="form-control" id="begin_min" type="text" name="begin_min"
                           value=""
                           />
                </div>
            </div>
                
        </div>

        <div class="form-group mb-3">
            <label for="end_date" class="control-label col-3"><?php echo BookingTranslator::End_of_the_reservation($lang) ?>:</label>
            <div class="col-8">
                <div class='input-group date'>
                    <input type='date' class="form-control" id="end_date" name="end_date"
                           value=""/>
                </div>
            </div>
        </div>
        <div class="form-group">    
            <div class="row mb-3">
                <!-- time -->

                <label for="end_hour" class="control-label col-1"><?php echo BookingTranslator::time($lang) ?>:</label>

                <div class="col-3">
                    <input class="form-control" id="end_hour" type="text" name="end_hour"
                           value="" 
                           />
                </div>
                <div class="col-1">
                    :
                </div>
                <div class="col-3">
                    <input class="form-control" id="end_min" type="text" name="end_min"
                           value=""
                           />
                </div>
            </div>
        </div>

        <!-- color code -->
        <div class="form-group">
            <label for="color_code_id" class="control-label col-3"><?php echo BookingTranslator::Color_code($lang) ?></label>
            <div class="col-8 mb-3">
                <select class="form-select" id="color_code_id" name="color_code_id">
                    <?php
                    $colorID = 1;
                    foreach ($colorCodes as $colorCode) {
                        $codeID = $this->clean($colorCode["id"]);
                        $codeName = $this->clean($colorCode["name"]);
                        $selected = "";
                        if ($codeID == $colorID) {
                            $selected = "selected=\"selected\"";
                        }
                        ?>
                        <OPTION value="<?php echo $codeID ?>" <?php echo $selected ?>> <?php echo $codeName ?> </OPTION>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="col-3"></div>
        <div class="col-9" id="button-div">
            <input type="submit" class="btn btn-primary" value="<?php echo CoreTranslator::Save($lang) ?>" />
            <button type="button" onclick="location.href = 'bookingblock/<?php echo $id_space ?>'" class="btn btn-outline-dark"><?php echo CoreTranslator::Cancel($lang) ?></button>
        </div>
    </form>
</div>
</div>

<div class="pm-form row">
    <?php echo $blocked; ?>
</div>

</div>

<?php endblock(); ?>
