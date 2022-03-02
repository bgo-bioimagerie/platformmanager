<?php include 'Modules/core/View/layout.php' ?>

    
<?php startblock('content') ?>
    
    <div class="container pm-form">
        <?php echo $formHtml ?>

        <?php echo $formApi ?>

        <div class="col-md-12">
            <h2>External connection providers</h2>
        <?php
        foreach ($providers as $provider) {
        ?>
            <a href="<?php echo $provider['login']; ?>?client_id=<?php echo $provider['client_id']; ?>&response_type=code&scope=openid&redirect_uri=<?php echo $provider['callback']; ?>&nonce=<?php echo $provider['nonce']; ?>">
                <button type="button" class="btn btn-primary"><?php if ($provider['icon']){echo '<img style="width:200px" src="'.$provider['icon'].'"/>';} else{echo $provider['name'];} ?></button>
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



    <div class="col-12">
        <h2>External connection providers</h2>
    <?php
    foreach ($providers as $provider) {
    ?>
        <a href="<?php echo $provider['login']; ?>?client_id=<?php echo $provider['client_id']; ?>&response_type=code&scope=openid&redirect_uri=<?php echo $provider['callback']; ?>&nonce=<?php echo $provider['nonce']; ?>">
            <button type="button" class="btn btn-primary"><?php if ($provider['icon']){echo '<img style="width:200px" src="'.$provider['icon'].'"/>';} else{echo $provider['name'];} ?></button>
        </a>
    <?php
    }
    ?>
    </div>
    <div class="col-12">
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
    

<?php endblock(); ?>