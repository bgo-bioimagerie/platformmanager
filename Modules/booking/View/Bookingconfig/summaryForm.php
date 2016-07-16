<div class="col-xs-12 col-md-10 col-md-offset-1">
    <div class="page-header">
        <h3>
            <?php echo BookingTranslator::Booking_summary_options($lang) ?> <br> <small></small>
        </h3>
    </div>

    <?php
    if (isset($bookingSettings) && $bookingSettings != "") {
        ?>
        <form role="form" class="form-horizontal" action="bookingconfig"
              method="post">

            <div class="col-xs-10">
                <input class="form-control" type="hidden" name="setbookingoptionsquery" value="yes"
                       />
            </div>

            <!-- recipient name -->
            <?php
            //$tagName = $this->clean($bookingSettings[$i]['tag_name']);
            $i = 0;
            $tag_visible = $this->clean($bookingSettings[$i]['is_visible']);
            $tag_title_visible = $this->clean($bookingSettings[$i]['is_tag_visible']);
            $tag_position = $this->clean($bookingSettings[$i]['display_order']);
            $tag_font = $this->clean($bookingSettings[$i]['font']);
            ?>
            <div class="col-xs-12">
                <div class="col-xs-3"><label class="control-label">Recipient name:</label></div>
                <div class="col-xs-2"><select class="form-control" name="tag_visible_rname">
                        <OPTION value="1" <?php if ($tag_visible == 1) {
            echo "selected=\"selected\"";
        } ?>> Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_visible == 0) {
            echo "selected=\"selected\"";
        } ?>> Hiden </OPTION>
                    </select></div>
                <div class="col-xs-3"><select class="form-control" name="tag_title_visible_rname">
                        <OPTION value="1" <?php if ($tag_title_visible == 1) {
            echo "selected=\"selected\"";
        } ?>> Tag Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_title_visible == 0) {
                        echo "selected=\"selected\"";
                    } ?>> Tag Hiden </OPTION>
                    </select></div>
                <div class="col-xs-2"><select class="form-control" name="tag_position_rname">
                        <?php
                        for ($j = 0; $j < count($bookingSettings); $j++) {
                            $selected = "";
                            if ($tag_position == $j + 1) {
                                $selected = "selected=\"selected\"";
                            }
                            ?>
                            <OPTION value="<?php echo $j + 1 ?>" <?php echo $selected ?>> position <?php echo $j + 1 ?> </OPTION>
        <?php
    }
    ?>
                    </select></div>
                <div class="col-xs-2"><select class="form-control" name="tag_font_rname">
                        <OPTION value="normal" <?php if ($tag_font == "normal") {
        echo "selected=\"selected\"";
    } ?>> normal </OPTION>
                        <OPTION value="bold" <?php if ($tag_font == "bold") {
            echo "selected=\"selected\"";
        } ?>> bold </OPTION>
                        <OPTION value="italic" <?php if ($tag_font == "italic") {
            echo "selected=\"selected\"";
        } ?>> italic </OPTION>
                    </select></div>
            </div> 

            <!-- recipient phone - rphone-->
    <?php
    $i = 1;
    $tag_visible = $this->clean($bookingSettings[$i]['is_visible']);
    $tag_title_visible = $this->clean($bookingSettings[$i]['is_tag_visible']);
    $tag_position = $this->clean($bookingSettings[$i]['display_order']);
    $tag_font = $this->clean($bookingSettings[$i]['font']);
    ?>
            <div class="col-xs-12">
                <div class="col-xs-3"><label class="control-label">Recipient phone:</label></div>
                <div class="col-xs-2"><select class="form-control" name="tag_visible_rphone">
                        <OPTION value="1" <?php if ($tag_visible == 1) {
                        echo "selected=\"selected\"";
                    } ?>> Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_visible == 0) {
                        echo "selected=\"selected\"";
                    } ?>> Hiden </OPTION>
                    </select></div>
                <div class="col-xs-3"><select class="form-control" name="tag_title_visible_rphone">
                        <OPTION value="1" <?php if ($tag_title_visible == 1) {
                        echo "selected=\"selected\"";
                    } ?>> Tag Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_title_visible == 0) {
                        echo "selected=\"selected\"";
                    } ?>> Tag Hiden </OPTION>
                    </select></div>
                <div class="col-xs-2"><select class="form-control" name="tag_position_rphone">
            <?php
            for ($j = 0; $j < count($bookingSettings); $j++) {
                $selected = "";
                if ($tag_position == $j + 1) {
                    $selected = "selected=\"selected\"";
                }
                ?>
                            <OPTION value="<?php echo $j + 1 ?>" <?php echo $selected ?>> position <?php echo $j + 1 ?> </OPTION>
        <?php
    }
    ?>
                    </select></div>
                <div class="col-xs-2"><select class="form-control" name="tag_font_rphone">
                        <OPTION value="normal" <?php if ($tag_font == "normal") {
        echo "selected=\"selected\"";
    } ?>> normal </OPTION>
                        <OPTION value="bold" <?php if ($tag_font == "bold") {
        echo "selected=\"selected\"";
    } ?>> bold </OPTION>
                        <OPTION value="italic" <?php if ($tag_font == "italic") {
                        echo "selected=\"selected\"";
                    } ?>> italic </OPTION>
                    </select></div>
            </div> 


            <!-- short description - sdesc -->
                        <?php
                        $i = 2;
                        $tag_visible = $this->clean($bookingSettings[$i]['is_visible']);
                        $tag_title_visible = $this->clean($bookingSettings[$i]['is_tag_visible']);
                        $tag_position = $this->clean($bookingSettings[$i]['display_order']);
                        $tag_font = $this->clean($bookingSettings[$i]['font']);
                        ?>
            <div class="col-xs-12">
                <div class="col-xs-3"><label class="control-label">Short description:</label></div>
                <div class="col-xs-2"><select class="form-control" name="tag_visible_sdesc">
                        <OPTION value="1" <?php if ($tag_visible == 1) {
                            echo "selected=\"selected\"";
                        } ?>> Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_visible == 0) {
            echo "selected=\"selected\"";
        } ?>> Hiden </OPTION>
                    </select></div>
                <div class="col-xs-3"><select class="form-control" name="tag_title_visible_sdesc">
                        <OPTION value="1" <?php if ($tag_title_visible == 1) {
            echo "selected=\"selected\"";
        } ?>> Tag Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_title_visible == 0) {
            echo "selected=\"selected\"";
        } ?>> Tag Hiden </OPTION>
                    </select></div>
                <div class="col-xs-2"><select class="form-control" name="tag_position_sdesc">
    <?php
    for ($j = 0; $j < count($bookingSettings); $j++) {
        $selected = "";
        if ($tag_position == $j + 1) {
            $selected = "selected=\"selected\"";
        }
        ?>
                            <OPTION value="<?php echo $j + 1 ?>" <?php echo $selected ?>> position <?php echo $j + 1 ?> </OPTION>
                            <?php
                        }
                        ?>
                    </select></div>
                <div class="col-xs-2"><select class="form-control" name="tag_font_sdesc">
                        <OPTION value="normal" <?php if ($tag_font == "normal") {
                        echo "selected=\"selected\"";
                    } ?>> normal </OPTION>
                        <OPTION value="bold" <?php if ($tag_font == "bold") {
                        echo "selected=\"selected\"";
                    } ?>> bold </OPTION>
                        <OPTION value="italic" <?php if ($tag_font == "italic") {
                        echo "selected=\"selected\"";
                    } ?>> italic </OPTION>
                    </select></div>
            </div> 

            <!-- description - desc -->
        <?php
        $i = 3;
        $tag_visible = $this->clean($bookingSettings[$i]['is_visible']);
        $tag_title_visible = $this->clean($bookingSettings[$i]['is_tag_visible']);
        $tag_position = $this->clean($bookingSettings[$i]['display_order']);
        $tag_font = $this->clean($bookingSettings[$i]['font']);
        ?>
            <div class="col-xs-12">
                <div class="col-xs-3"><label class="control-label">Description:</label></div>
                <div class="col-xs-2"><select class="form-control" name="tag_visible_desc">
                        <OPTION value="1" <?php if ($tag_visible == 1) {
            echo "selected=\"selected\"";
        } ?>> Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_visible == 0) {
            echo "selected=\"selected\"";
        } ?>> Hiden </OPTION>
                    </select></div>
                <div class="col-xs-3"><select class="form-control" name="tag_title_visible_desc">
                        <OPTION value="1" <?php if ($tag_title_visible == 1) {
            echo "selected=\"selected\"";
        } ?>> Tag Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_title_visible == 0) {
            echo "selected=\"selected\"";
        } ?>> Tag Hiden </OPTION>
                    </select></div>
                <div class="col-xs-2"><select class="form-control" name="tag_position_desc">
    <?php
    for ($j = 0; $j < count($bookingSettings); $j++) {
        $selected = "";
        if ($tag_position == $j + 1) {
            $selected = "selected=\"selected\"";
        }
        ?>
                            <OPTION value="<?php echo $j + 1 ?>" <?php echo $selected ?>> position <?php echo $j + 1 ?> </OPTION>
        <?php
    }
    ?>
                    </select></div>
                <div class="col-xs-2"><select class="form-control" name="tag_font_desc">
                        <OPTION value="normal" <?php if ($tag_font == "normal") {
        echo "selected=\"selected\"";
    } ?>> normal </OPTION>
                        <OPTION value="bold" <?php if ($tag_font == "bold") {
        echo "selected=\"selected\"";
    } ?>> bold </OPTION>
                        <OPTION value="italic" <?php if ($tag_font == "italic") {
        echo "selected=\"selected\"";
    } ?>> italic </OPTION>
                    </select></div>
                <br></br>
                <div class="col-xs-2 col-xs-offset-10" id="button-div">
                    <input type="submit" class="btn btn-primary" value="save" />
                </div>
        </form>
    <?php
}
?>

</div>