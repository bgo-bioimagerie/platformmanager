<?php include 'Modules/seek/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div>
    <iframe src="<?php echo $seekUrl ?>" width="100%" height="2000px" sandbox style="border:none;">
    </iframe> 
</div>

<?php
endblock();
