<?php include 'Modules/core/View/spacelayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-12" id="pm-content">
    <div class="col-md-10 col-md-offset-1" id="pm-form">
            <?php echo $userForm ?>
        </div>
        <div class="col-md-10 col-md-offset-1" id="pm-form">
            <?php echo $userTable ?>
        </div>
</div>
<?php
endblock();
