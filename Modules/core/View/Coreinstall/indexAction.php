<?php include 'Modules/layout.php' ?>

<!-- header -->
<?php startblock('title') ?>
Install - Platform-Manager
<?php endblock() ?> 

    
<?php startblock('content') ?>

<?php if ($message != "") {?>
<div class="container">
    <div class="alert alert-danger">
        <h1>Error:</h1>
        <p> <?php echo $message ?> </p>
    </div>
</div> 
<?php } ?>

<div class="container pm-form-short">
    <?php echo $formHtml ?> 
</div>
<?php endblock(); ?>
