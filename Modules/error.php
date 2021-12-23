<?php include 'Modules/layout.php' ?>

<!-- header -->
<?php startblock('title') ?>
Error - Platform-Manager
<?php endblock() ?> 

<!-- body -->     
<?php startblock('content') ?>
<div class="container">
    <div id="error" class="jumbotron" style="margin-top: 50px;">
        <h1 id="errorheader"> 
        Error: <?php echo $this->clean($type) ?>
        </h1>
        <p id="errorcontent">
        <?php echo $this->clean($message) ?>
        </p>
    </div>
</div>
<?php endblock();
