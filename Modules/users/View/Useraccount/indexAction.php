<?php include_once 'Modules/core/View/layout.php' ?>

    
<?php startblock('content') ?>
    
<div class="container pm-form">
    <div class="row">
        <div class="col-12 mb-3">
            <?php echo $formHtml ?>
        </div>

        <div class="col-12 mb-3">
            <?php echo $formApi ?>
        </div>

        <div class="col-12 mb-3">
            <?php echo $rolesTableHtml ?>
        </div>

        <div class="col-12 mb-3">
            <h2>External connection providers</h2>
        <?php
        foreach ($providers as $provider) {
            ?>
            <a href="<?php echo $provider['login']; ?>?client_id=<?php echo $provider['client_id']; ?>&response_type=code&scope=openid&redirect_uri=<?php echo $provider['callback']; ?>&nonce=<?php echo $provider['nonce']; ?>">
                <button type="button" class="btn btn-sm"><?php if ($provider['icon']) {
                    echo '<img style="max-width:200px" src="'.$provider['icon'].'"/>';
                } else {
                    echo $provider['name'];
                } ?></button>
            </a>
        <?php
        }
    ?>
        </div>
        <div class="col-12 mb-3">
            <h2>Linked providers</h2>
            <table class="table" aria-label="external providers linked to account">
            <thead><tr><th scope="col">Provider</th><th scope="col">ID</th></tr></thead>
            <tbody>
        <?php
    foreach ($linked as $link) {
    ?>
            <tr><td><?php echo $link['provider'];?></td><td><?php echo $link['oid'];?></td></tr>
        <?php
    }
    ?>
            </tbody>
            </table>
        </div>

    </div>

</div>
    

<?php endblock(); ?>