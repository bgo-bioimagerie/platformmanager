<?php include 'Modules/layout.php' ?>

<?php startblock('stylesheet') ?>
<style>
#message {
    min-height: 400px;
}
</style>
<?php endblock() ?>
<?php startblock('content') ?>

<div id="helpdeskreport" class="container">
    <div class="row">
        <div class="col-sm-12">
            <?php echo $form; ?>
        </div>
    </div>
</div>

<?php endblock() ?>