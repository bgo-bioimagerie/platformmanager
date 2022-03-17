<?php include 'Modules/booking/View/layoutsettings.php' ?>

    
<?php startblock('content') ?>

    <div class="container pm-form">
        <div class="col-12">
            <form role="form" class="form-horizontal" action="bookingnightweeditq/<?php echo $id_space ?>"
                  method="post">

                <div class="page-header">
                    <h3>
                        <?php echo BookingTranslator::Edit_NightWE($lang) ?>
                        <br> <small></small>
                    </h3>
                </div>

                <div class="form-group mb-3">
                    <label  class="control-label col-2">ID</label>
                    <div class="col-10">
                        <input class="form-control" id="id" type="text" name="id" value="<?php echo $this->clean($pricing['id_belonging']) ?>" readonly
                               />
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label  class="control-label col-2"><?php echo CoreTranslator::Name($lang) ?></label>
                    <div class="col-10">
                        <input class="form-control" id="name" type="text" name="name" value="<?php echo $this->clean($pricing['name']) ?>" readonly
                               />
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label  class="control-label col-2"><?php echo BookingTranslator::Unique_price($lang) ?></label>
                    <div class="col-10">
                        <select class="form-control" name="tarif_unique">
                            <?php $unique = $this->clean($pricing['tarif_unique']) ?>
                            <OPTION value="1" <?php if ($unique == 1) {
                                echo "selected=\"selected\"";
                            } ?>> <?php echo CoreTranslator::Yes($lang) ?> </OPTION>
                            <OPTION value="0" <?php if ($unique == 0) {
                                echo "selected=\"selected\"";
                            } ?>> <?php echo CoreTranslator::No($lang) ?> </OPTION>
                        </select>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label  class="control-label col-2"><?php echo BookingTranslator::Price_night($lang) ?></label>
                    <div class="col-10">
                        <select class="form-control" name="tarif_night">
<?php $tnuit = $this->clean($pricing['tarif_night']) ?>
                            <OPTION value="1" <?php if ($tnuit == 1) {
    echo "selected=\"selected\"";
} ?>> <?php echo CoreTranslator::Yes($lang) ?> </OPTION>
                            <OPTION value="0" <?php if ($tnuit == 0) {
    echo "selected=\"selected\"";
} ?>> <?php echo CoreTranslator::No($lang) ?> </OPTION>
                        </select>
                    </div>
                    <div class="col-10 mt-3">
                        <div class="row">
                        <label  class="control-label col-3"><?php echo BookingTranslator::Night_beginning($lang) ?></label>
                        <div class="col-3">
                            <select class="form-control col-2" name="night_start">
<?php $snight = $this->clean($pricing['night_start']) ?>
                                <OPTION value="18" <?php if ($snight == 18) {
    echo "selected=\"selected\"";
} ?>> 18h </OPTION>
                                <OPTION value="19" <?php if ($snight == 19) {
                                    echo "selected=\"selected\"";
                                } ?>> 19h </OPTION>
                                <OPTION value="20" <?php if ($snight == 20) {
                                    echo "selected=\"selected\"";
                                } ?>> 20h </OPTION>
                                <OPTION value="21" <?php if ($snight == 21) {
                                    echo "selected=\"selected\"";
                                } ?>> 21h </OPTION>
                                <OPTION value="22" <?php if ($snight == 22) {
                                    echo "selected=\"selected\"";
                                } ?>> 22h </OPTION>
                            </select>
                        </div>
                        <label  class="control-label col-3"><?php echo BookingTranslator::Night_end($lang) ?></label>
                        <div class="col-3">
                            <select class="form-control" name="night_end">
<?php $enight = $this->clean($pricing['night_end']) ?>
                                <OPTION value="6" <?php if ($enight == 6) {
    echo "selected=\"selected\"";
} ?>> 6h </OPTION>
                                <OPTION value="7" <?php if ($enight == 7) {
                        echo "selected=\"selected\"";
                    } ?>> 7h </OPTION>
                                <OPTION value="8" <?php if ($enight == 8) {
                        echo "selected=\"selected\"";
                    } ?>> 8h </OPTION>
                                <OPTION value="9" <?php if ($enight == 9) {
                        echo "selected=\"selected\"";
                    } ?>> 9h </OPTION>
                            </select>
                        </div>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label  class="control-label col-2"><?php echo BookingTranslator::Price_weekend($lang) ?></label>
                    <div class="col-10">
                        <select class="form-control" name="tarif_we">
                                    <?php $tarif_we = $this->clean($pricing['tarif_we']) ?>
                            <OPTION value="1" <?php if ($tarif_we == 1) {
                                        echo "selected=\"selected\"";
                                    } ?>> <?php echo CoreTranslator::Yes($lang) ?> </OPTION>
                            <OPTION value="0" <?php if ($tarif_we == 0) {
                                        echo "selected=\"selected\"";
                                    } ?>> <?php echo CoreTranslator::No($lang) ?> </OPTION>
                        </select>
                    </div>


<?php
$jours = $this->clean($pricing['choice_we']);
$list = explode(",", $jours);
if (count($list) < 7) {
    $list[0] = 0;
    $list[1] = 0;
    $list[2] = 0;
    $list[3] = 0;
    $list[4] = 0;
    $list[5] = 1;
    $list[6] = 1;
}
?>
                    <div class="col-10 mt-3">
                        <label  class="control-label col-3"><?php echo BookingTranslator::Weekend_days($lang) ?></label>
                        <div class="col-2">
                            <div class="checkbox">
                                <label class="form-check-label">
<?php $lundi = $list[0]; ?>
                                    <input class="form-check-input" type="checkbox" name="lundi" <?php if ($lundi == 1) {
    echo "checked";
} ?>> <?php echo BookingTranslator::Monday($lang) ?>
                                </label>
                            </div>
                            <div class="checkbox">
                                <label class="form-check-label">
<?php $mardi = $list[1]; ?>
                                    <input class="form-check-input" type="checkbox" name="mardi" <?php if ($mardi == 1) {
    echo "checked";
} ?>> <?php echo BookingTranslator::Tuesday($lang) ?>
                                </label>
                            </div>
                            <div class="checkbox">
                                <label class="form-check-label">
<?php $mercredi = $list[2]; ?>
                                    <input class="form-check-input" type="checkbox" name="mercredi" <?php if ($mercredi == 1) {
    echo "checked";
} ?>> <?php echo BookingTranslator::Wednesday($lang) ?>
                                </label>
                            </div>
                            <div class="checkbox">
                                <label class="form-check-label">
<?php $jeudi = $list[3]; ?>
                                    <input class="form-check-input" type="checkbox" name="jeudi" <?php if ($jeudi == 1) {
    echo "checked";
} ?>> <?php echo BookingTranslator::Thursday($lang) ?>
                                </label>
                            </div>
                            <div class="checkbox">
                                <label class="form-check-label">
<?php $vendredi = $list[4]; ?>
                                    <input class="form-check-input" type="checkbox" name="vendredi" <?php if ($vendredi == 1) {
    echo "checked";
} ?>> <?php echo BookingTranslator::Friday($lang) ?>
                                </label class="form-check-label">
                            </div>
                            <div class="checkbox">
                                <label class="form-check-label">
<?php $samedi = $list[5]; ?>
                                    <input class="form-check-input" type="checkbox" name="samedi" <?php if ($samedi == 1) {
    echo "checked";
} ?>> <?php echo BookingTranslator::Saturday($lang) ?>
                                </label class="form-check-label">
                            </div>
                            <div class="checkbox">
                                <label class="form-check-label">
<?php $dimanche = $list[6]; ?>
                                    <input class="form-check-input" type="checkbox" name="dimanche" <?php if ($dimanche == 1) {
    echo "checked";
} ?>> <?php echo BookingTranslator::Sunday($lang) ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-4 offset-8" id="button-div">
                    <input type="submit" class="btn btn-primary" value="<?php echo CoreTranslator::Save($lang) ?>" />
                    <button type="button" onclick="location.href = 'bookingnightwe/'<?php echo $id_space ?>" class="btn btn-outline-dark"><?php echo CoreTranslator::Cancel($lang) ?></button>
                </div>
            </form>
        </div>
    </div>
<?php endblock(); ?>
