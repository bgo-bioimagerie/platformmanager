<?php include 'Modules/core/View/spacelayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="row">
<div class="col-md-12 pm-form-short">
    <?php echo $userForm ?>
</div>
<div class="col-md-12 pm-table-short">
    <?php echo $userTable ?>
</div>
</div>
<?php
endblock();
