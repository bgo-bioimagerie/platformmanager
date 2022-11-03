<?php include_once 'Modules/layout.php' ?>

<!-- header -->
<?php startblock('title') ?>
Error - Platform-Manager
<?php endblock() ?> 

    
<?php startblock('content') ?>
<div class="container">
    <div class="jumbotron" style="margin-top: 50px;">
        <h1> 
        Error: <?php echo $this->clean($type) ?>
        </h1>
        <p>
        <?php echo $this->clean($message) ?>
        </p>
    </div>
</div>
<?php endblock(); ?>
