<?php
//print_r($tweets);
foreach ($tweets as $tweet) {
    ?>
    <div class="col-md-12">
        <?php echo $tweet; ?>
    </div> 
    <?php
}
?>