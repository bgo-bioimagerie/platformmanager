<div class="col-12 col-md-10 col-md-offset-1 pm-form-short" style="background-color: #fff; border-radius: 7px; padding: 7px;">
    <div class="page-header">
        <h3>
            <?php echo BookingTranslator::Booking_summary_options($lang) ?> <br> <small></small>
        </h3>
    </div>

    <?php
    if (isset($bookingSettings) && $bookingSettings != "") {
        if(empty($bookingSettings)) {
            $bookingSettings = [];
            for($i=0;$i<4;$i++) {
                $bookingSettings[] = [
                    'is_visible' => 0,
                    'is_tag_visible' => 0,
                    'display_order' => $i+1,
                    'font' => 'normal'
                ];
            }
        }
        ?>
        <form role="form" class="form-horizontal" action="bookingconfig/<?php echo $id_space ?>"
              method="post">

            <div class="col-10">
                <input class="form-control" type="hidden" name="setbookingoptionsquery" value="yes"
                       />
            </div>

            <!-- recipient name -->
            <?php
            //$tagName = $this->clean($bookingSettings[$i]['tag_name']);
            $i = 0;
            $tag_visible = 0;
            $tag_title_visible = 0;
            $tag_position = 0;
            $tag_font = 'normal';
            if(!empty($bookingSettings)) {
                $tag_visible = $this->clean($bookingSettings[$i]['is_visible'] ?? 1);
                $tag_title_visible = $this->clean($bookingSettings[$i]['is_tag_visible'] ?? 1);
                $tag_position = $this->clean($bookingSettings[$i]['display_order'] ?? 1);
                $tag_font = $this->clean($bookingSettings[$i]['font'] ?? 'normal');
            }
            ?>
            <div class="col-12">
                <div class="col-3"><label class="control-label">Recipient name:</label></div>
                <div class="col-2"><select class="form-control" name="tag_visible_rname">
                        <OPTION value="1" <?php if ($tag_visible == 1) {
            echo "selected=\"selected\"";
        } ?>> Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_visible == 0) {
            echo "selected=\"selected\"";
        } ?>> Hidden </OPTION>
                    </select></div>
                <div class="col-3"><select class="form-control" name="tag_title_visible_rname">
                        <OPTION value="1" <?php if ($tag_title_visible == 1) {
            echo "selected=\"selected\"";
        } ?>> Tag Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_title_visible == 0) {
                        echo "selected=\"selected\"";
                    } ?>> Tag Hidden </OPTION>
                    </select></div>
                <div class="col-2"><select class="form-control" name="tag_position_rname">
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
                <div class="col-2"><select class="form-control" name="tag_font_rname">
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
    $tag_visible = 0;
    $tag_title_visible = 0;
    $tag_position = 0;
    $tag_font = 'normal';
    if(!empty($bookingSettings)) {
        $tag_visible = $this->clean($bookingSettings[$i]['is_visible']);
        $tag_title_visible = $this->clean($bookingSettings[$i]['is_tag_visible']);
        $tag_position = $this->clean($bookingSettings[$i]['display_order']);
        $tag_font = $this->clean($bookingSettings[$i]['font']);
    }
    ?>
            <div class="col-12">
                <div class="col-3"><label class="control-label">Recipient phone:</label></div>
                <div class="col-2"><select class="form-control" name="tag_visible_rphone">
                        <OPTION value="1" <?php if ($tag_visible == 1) {
                        echo "selected=\"selected\"";
                    } ?>> Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_visible == 0) {
                        echo "selected=\"selected\"";
                    } ?>> Hiden </OPTION>
                    </select></div>
                <div class="col-3"><select class="form-control" name="tag_title_visible_rphone">
                        <OPTION value="1" <?php if ($tag_title_visible == 1) {
                        echo "selected=\"selected\"";
                    } ?>> Tag Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_title_visible == 0) {
                        echo "selected=\"selected\"";
                    } ?>> Tag Hiden </OPTION>
                    </select></div>
                <div class="col-2"><select class="form-control" name="tag_position_rphone">
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
                <div class="col-2"><select class="form-control" name="tag_font_rphone">
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
        $tag_visible = 0;
        $tag_title_visible = 0;
        $tag_position = 0;
        $tag_font = 'normal';
        if(!empty($bookingSettings)) {
            $tag_visible = $this->clean($bookingSettings[$i]['is_visible']);
            $tag_title_visible = $this->clean($bookingSettings[$i]['is_tag_visible']);
            $tag_position = $this->clean($bookingSettings[$i]['display_order']);
            $tag_font = $this->clean($bookingSettings[$i]['font']);
        }
                        ?>
            <div class="col-12">
                <div class="col-3"><label class="control-label">Short description:</label></div>
                <div class="col-2"><select class="form-control" name="tag_visible_sdesc">
                        <OPTION value="1" <?php if ($tag_visible == 1) {
                            echo "selected=\"selected\"";
                        } ?>> Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_visible == 0) {
            echo "selected=\"selected\"";
        } ?>> Hiden </OPTION>
                    </select></div>
                <div class="col-3"><select class="form-control" name="tag_title_visible_sdesc">
                        <OPTION value="1" <?php if ($tag_title_visible == 1) {
            echo "selected=\"selected\"";
        } ?>> Tag Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_title_visible == 0) {
            echo "selected=\"selected\"";
        } ?>> Tag Hiden </OPTION>
                    </select></div>
    <div class="col-2"><select class="form-control" name="tag_position_sdesc">
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
    <div class="col-2"><select class="form-control" name="tag_font_sdesc">
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
        $tag_visible = 0;
        $tag_title_visible = 0;
        $tag_position = 0;
        $tag_font = 'normal';
        if(!empty($bookingSettings)) {
            $tag_visible = $this->clean($bookingSettings[$i]['is_visible']);
            $tag_title_visible = $this->clean($bookingSettings[$i]['is_tag_visible']);
            $tag_position = $this->clean($bookingSettings[$i]['display_order']);
            $tag_font = $this->clean($bookingSettings[$i]['font']);
        }
        ?>
            <div class="col-12">
                <div class="col-3"><label class="control-label">Description:</label></div>
                <div class="col-2"><select class="form-control" name="tag_visible_desc">
                        <OPTION value="1" <?php if ($tag_visible == 1) {
            echo "selected=\"selected\"";
        } ?>> Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_visible == 0) {
            echo "selected=\"selected\"";
        } ?>> Hiden </OPTION>
                    </select></div>
                <div class="col-3"><select class="form-control" name="tag_title_visible_desc">
                        <OPTION value="1" <?php if ($tag_title_visible == 1) {
            echo "selected=\"selected\"";
        } ?>> Tag Visible </OPTION>
                        <OPTION value="0" <?php if ($tag_title_visible == 0) {
            echo "selected=\"selected\"";
        } ?>> Tag Hiden </OPTION>
                    </select></div>
                <div class="col-2"><select class="form-control" name="tag_position_desc">
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
                <div class="col-2"><select class="form-control" name="tag_font_desc">
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
                <div class="col-2 col-offset-10" id="button-div">
                    <input type="submit" class="btn btn-primary" value="save" />
                </div>
        </form>
    <?php
}
?>

</div>