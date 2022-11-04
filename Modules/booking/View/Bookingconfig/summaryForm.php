<div class="col-12 pm-form-short" style="background-color: #fff; border-radius: 7px; padding: 7px;">
    <div class="page-header">
        <h3>
            <?php echo BookingTranslator::Booking_summary_options($lang) ?> <br> <small></small>
        </h3>
    </div>

    <?php
    if (isset($bookingSettings) && $bookingSettings != "") {
        if (empty($bookingSettings)) {
            $modelBkSettings = new BkBookingSettings();
            $bookingSettings = $modelBkSettings->getDefaultBkSettings();
        }
        ?>
        <form role="form" class="form-horizontal" action="bookingconfig/<?php echo $id_space ?>"
              method="post">
            <div>
                <input class="form-control" type="hidden" name="setbookingoptionsquery" value="yes"/>
            </div>

            <?php
                $setSelected = "selected=\"selected\"";
        foreach ($bookingSettings as $bkSetting) {
            $tag_visible = $this->clean($bkSetting['is_visible'] ?? 1);
            $tag_title_visible = $this->clean($bkSetting['is_tag_visible'] ?? 1);
            $tag_position = $this->clean($bkSetting['display_order'] ?? 1);
            $tag_font = $this->clean($bkSetting['font'] ?? 'normal');
            $optTag = $bkSetting['tag_name'];
            $trimOptTag = str_replace(' ', '', $optTag);
            ?>

                <div class="row">
                    <div class="col-3">
                        <label class="control-label">
                            <?php echo BookingTranslator::BkSettingDisplayName($optTag, $lang) ?>
                        </label>
                    </div>

                    <div class="col-2">
                        <select class="form-control" name="tag_visible_<?php echo $trimOptTag ?>">
                            <option value="1" <?php if ($tag_visible == 1) {
                                echo $setSelected;
                            } ?>> Visible </option>
                            <option value="0" <?php if ($tag_visible == 0) {
                                echo $setSelected;
                            } ?>> Hidden </option>
                            <option value="2" <?php if ($tag_visible == 2) {
                                echo $setSelected;
                            } ?>> Managers </option>
                        </select>
                    </div>

                    <div class="col-3">
                        <select class="form-control" name="tag_title_visible_<?php echo $trimOptTag ?>">
                            <option value="1" <?php if ($tag_title_visible == 1) {
                                echo $setSelected;
                            } ?>> Tag Visible </option>
                            <option value="0" <?php if ($tag_title_visible == 0) {
                                echo $setSelected;
                            } ?>> Tag Hidden </option>
                        </select>
                    </div>

                    <div class="col-2">
                        <select class="form-control" name="tag_position_<?php echo $trimOptTag ?>">
                            <?php
                            for ($j = 0; $j < count($bookingSettings); $j++) {
                                $selected = "";
                                if ($tag_position == $j + 1) {
                                    $selected = $setSelected;
                                }
                                ?>
                                <option
                                    value="<?php echo $j + 1 ?>" <?php echo $selected ?>> position <?php echo $j + 1 ?>
                                </option>
                            <?php
                            }
            ?>
                        </select>
                    </div>

                    <div class="col-2">
                        <select class="form-control" name="tag_font_<?php echo $trimOptTag ?>">
                            <option value="normal" <?php if ($tag_font == "normal") {
                                echo $setSelected;
                            } ?>> normal </option>
                            <option value="bold" <?php if ($tag_font == "bold") {
                                echo $setSelected;
                            } ?>> bold </option>
                            <option value="italic" <?php if ($tag_font == "italic") {
                                echo $setSelected;
                            } ?>> italic </option>
                        </select>
                    </div>
                </div>

            <?php
        }
        ?>
                <br></br>
                <div class="col-2 offset-1" id="button-div">
                    <input type="submit" class="btn btn-primary" value="save" />
                </div>
            </form>
    <?php
    }
            ?>
</div>