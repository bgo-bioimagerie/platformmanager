<?php include_once 'Modules/statistics/View/layout.php' ?>

<?php startblock('content') ?>
<button onclick="location.reload()" type="button" class="m-3 btn btn-sm btn-info">Refresh</button>

<div class="container">
    <?php echo $stats ?>
</div>

<?php endblock(); ?>