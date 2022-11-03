
<style>
#colorparagraph{
    height:25px;
    border-radius:5px;
}

</style>

<?php
require_once 'Modules/booking/Model/BookingTranslator.php';
?>

<div class="col-12">
<br/>
<br/>
    <div class="page-header">
        <h4>
            <?php echo BookingTranslator::color_code($lang) ?>
            <br> <small></small>
        </h4>
    </div>

    <?php
    $cmpt = 0;
for ($i = 0 ; $i < count($colorcodes) ; $i++) {
    $colorcode = $colorcodes[$i];
    $name = $this->clean($colorcode["name"]);
    $color = $this->clean($colorcode["color"]);
    $txtcolor = $this->clean($colorcode["text"]);
    $cmpt++;
    if ($cmpt == 1) {
        ?>
        <div class="">
        <?php
    }
    ?>
    
    <div class="">
        <p class="text-center" id="colorparagraph" style="background-color: <?php echo $color?>; color: <?php echo $txtcolor?>"><?php echo $name?></p>
    </div>
    <?php
        if ($cmpt == 6 || $i == count($colorcodes)-1) {
            ?>
            </div>
            <?php
                $cmpt=0;
        }
}
?>

</div>

