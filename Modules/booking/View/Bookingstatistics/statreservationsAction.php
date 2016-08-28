<?php include 'Modules/statistics/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<br>
<div class="container">
    <div class="col-md-8 col-md-offset-2">
        <form role="form" class="form-horizontal" action="bookingreservationstatsquery/<?php echo $id_space ?>"
              method="post">

            <div class="page-header">
                <h1>
                    <?php echo BookingTranslator::bookingreservationstats($lang) ?>
                    <br> <small></small>
                </h1>
            </div>

            <div class="form-group">
                <label class="control-label col-xs-2"><?php echo BookingTranslator::PeriodBegining($lang) ?></label>
                <div class="col-xs-5">
                    <select class="form-control" name="month_start">
                        <OPTION value="1" > <?php echo BookingTranslator::Jan($lang) ?> </OPTION>
                        <OPTION value="2" > <?php echo BookingTranslator::Feb($lang) ?> </OPTION>
                        <OPTION value="3" > <?php echo BookingTranslator::Mar($lang) ?> </OPTION>
                        <OPTION value="4" > <?php echo BookingTranslator::Apr($lang) ?> </OPTION>
                        <OPTION value="5" > <?php echo BookingTranslator::May($lang) ?> </OPTION>
                        <OPTION value="6" > <?php echo BookingTranslator::Jun($lang) ?> </OPTION>
                        <OPTION value="7" > <?php echo BookingTranslator::July($lang) ?> </OPTION>
                        <OPTION value="8" > <?php echo BookingTranslator::Aug($lang) ?> </OPTION>
                        <OPTION value="9" > <?php echo BookingTranslator::Sept($lang) ?> </OPTION>
                        <OPTION value="10" > <?php echo BookingTranslator::Oct($lang) ?> </OPTION>
                        <OPTION value="11" > <?php echo BookingTranslator::Nov($lang) ?> </OPTION>
                        <OPTION value="12" > <?php echo BookingTranslator::Dec($lang) ?> </OPTION>
                    </select>
                </div>
                <div class="col-xs-5">
                    <select class="form-control" name="year_start">
                        <?php
                        for ($i = 2010; $i <= date('Y') + 1; $i++) {
                            $checked = "";
                            if ($i == date('Y')) {
                                $checked = ' selected="selected"';
                            }
                            ?>
                            <OPTION value="<?php echo $i ?>" <?php echo $checked ?>> <?php echo $i ?> </OPTION>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>	
            <div class="form-group">
                <label class="control-label col-xs-2"><?php echo BookingTranslator::PeriodEnd($lang) ?></label>
                <div class="col-xs-5">
                    <select class="form-control" name="month_end">
                        <OPTION value="1" > <?php echo BookingTranslator::Jan($lang) ?> </OPTION>
                        <OPTION value="2" > <?php echo BookingTranslator::Feb($lang) ?> </OPTION>
                        <OPTION value="3" > <?php echo BookingTranslator::Mar($lang) ?> </OPTION>
                        <OPTION value="4" > <?php echo BookingTranslator::Apr($lang) ?> </OPTION>
                        <OPTION value="5" > <?php echo BookingTranslator::May($lang) ?> </OPTION>
                        <OPTION value="6" > <?php echo BookingTranslator::Jun($lang) ?> </OPTION>
                        <OPTION value="7" > <?php echo BookingTranslator::July($lang) ?> </OPTION>
                        <OPTION value="8" > <?php echo BookingTranslator::Aug($lang) ?> </OPTION>
                        <OPTION value="9" > <?php echo BookingTranslator::Sept($lang) ?> </OPTION>
                        <OPTION value="10" > <?php echo BookingTranslator::Oct($lang) ?> </OPTION>
                        <OPTION value="11" > <?php echo BookingTranslator::Nov($lang) ?> </OPTION>
                        <OPTION value="12" > <?php echo BookingTranslator::Dec($lang) ?> </OPTION>
                    </select>
                </div>
                <div class="col-xs-5">
                    <select class="form-control" name="year_end">
                        <?php
                        for ($i = 2010; $i <= date('Y') + 1; $i++) {
                            $checked = "";
                            if ($i == date('Y')) {
                                $checked = ' selected="selected"';
                            }
                            ?>
                            <OPTION value="<?php echo $i ?>" <?php echo $checked ?>> <?php echo $i ?> </OPTION>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="col-xs-4 col-xs-offset-8" id="button-div">
                <input type="submit" class="btn btn-primary" value="<?php echo CoreTranslator::Ok($lang) ?>" />
            </div>
        </form>
    </div>
</div>

<?php
endblock();
