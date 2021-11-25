<?php include 'Modules/booking/View/layoutsettings.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-10 pm-form">
    <form role="form" class="form-horizontal" action="bookingblockquery/<?php echo $id_space ?>"
          method="post">


        <div class="page-header">
            <h3>
                <?php echo BookingTranslator::block_resources($lang) ?>
                <br> <small></small>
            </h3>
        </div>

        <div class="col-md-10 col-md-offset-1">
            <?php if ($errormessage != "") {
                ?>
                <div class="alert alert-danger text-center">
                    <p><?php echo $errormessage ?></p>
                </div>
            <?php } ?>
        </div>

        <div class="form-group">
            <label for="inputEmail" class="control-label col-xs-4"><?php echo BookingTranslator::Short_description($lang) ?></label>
            <div class="col-xs-8">
                <input class="form-control" id="name" type="text" name="short_description"
                       value=""
                       />
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-xs-4"><?php echo ResourcesTranslator::Resources($lang) ?></label>
            <div class="col-xs-8">
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
        </div>

        <div class="form-group">
            <label for="inputEmail" class="control-label col-xs-4"><?php echo BookingTranslator::Beginning_of_the_reservation($lang) ?>:</label>
            <div class="col-xs-8">
                <div class='input-group date form_date_<?php echo $lang ?>'>
                    <input type='text' class="form-control" name="begin_date"
                           value=""/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-group">    
            <div class="col-xs-8 col-xs-offset-4">
                <!-- time -->

                <label for="inputEmail" class="control-label col-xs-4"><?php echo BookingTranslator::time($lang) ?>:</label>

                <div class="col-xs-3">
                    <input class="form-control" id="name" type="text" name="begin_hour"
                           value="" 
                           />
                </div>
                <div class="col-xs-1">
                    <b>:</b>
                </div>
                <div class="col-xs-3">
                    <input class="form-control" id="name" type="text" name="begin_min"
                           value=""
                           />
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="inputEmail" class="control-label col-xs-4"><?php echo BookingTranslator::End_of_the_reservation($lang) ?>:</label>
            <div class="col-xs-8">
                <div class='input-group date form_date_<?php echo $lang ?>'>
                    <input type='text' class="form-control" name="end_date"
                           value=""/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-group">    
            <div class="col-xs-8 col-xs-offset-4">
                <!-- time -->

                <label for="inputEmail" class="control-label col-xs-4"><?php echo BookingTranslator::time($lang) ?>:</label>

                <div class="col-xs-3">
                    <input class="form-control" id="name" type="text" name="end_hour"
                           value="" 
                           />
                </div>
                <div class="col-xs-1">
                    <b>:</b>
                </div>
                <div class="col-xs-3">
                    <input class="form-control" id="name" type="text" name="end_min"
                           value=""
                           />
                </div>
            </div>
        </div>

        <!-- color code -->
        <div class="form-group">
            <label for="inputEmail" class="control-label col-xs-4"><?php echo BookingTranslator::Color_code($lang) ?></label>
            <div class="col-xs-8">
                <select class="form-control" name="color_code_id" <?php echo $readOnlyGlobal ?>>
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

        <br></br>
        <div class="col-xs-4 col-xs-offset-8" id="button-div">
            <input type="submit" class="btn btn-primary" value="<?php echo CoreTranslator::Save($lang) ?>" />
            <button type="button" onclick="location.href = 'bookingblock/<?php echo $id_space ?>'" class="btn btn-default"><?php echo CoreTranslator::Cancel($lang) ?></button>
        </div>
    </form>
</div>
</div>

<?php include "Framework/timepicker_script.php" ?>

<?php
endblock();
