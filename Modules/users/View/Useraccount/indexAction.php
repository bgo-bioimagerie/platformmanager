<?php include 'Modules/core/View/layout.php' ?>


<!-- body -->     
<?php startblock('content') ?>

<?php include( "Modules/core/View/Coreusers/navbar.php" ); ?>

<div class="col-md-12" style="margin-top:50px;">
    
    
    <div class="container pm-form">
        
            <div class="col-sm-10 col-sm-offset-1 text-center">
             <?php
        if (isset($_SESSION["message"])) {
            if (substr($_SESSION["message"], 0, 3) === "Err") {
                ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION["message"] ?>
                </div>
                <?php
            } else {
                ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION["message"] ?>
                </div>
                <?php
            }
            unset($_SESSION["message"]);
        }
        ?>
        </div>
        
        <?php echo $formHtml ?>


        <div class="col-md-12">
            <h2>External connection providers</h2>
        <?php
        foreach ($providers as $provider) {
        ?>
            <a href="<?php echo $provider['login']; ?>?client_id=<?php echo $provider['client_id']; ?>&response_type=code&scope=openid&redirect_uri=<?php echo $provider['callback']; ?>">
                <button type="button" class="btn btn-primary"><?php echo $provider['name']; ?></button>
            </a>
        <?php
        }
        ?>
        </div>
        <div class="col-md-12">
            <h2>Linked providers</h2>
            <table class="table" aria-label="external providers linked to account">
            <thead><tr><th scope="col">Provider</th><th scope="col">ID</th></tr></thead>
        <?php
        foreach ($linked as $link) {
        ?>
            <thead><tr><td><?php echo $link['provider'];?></td><td><?php echo $link['oid'];?></td></tr></thead>
        <?php
        }
        ?>
            </table>
        </div>



    </div>
    
</div> <!-- /container -->
<?php
endblock();