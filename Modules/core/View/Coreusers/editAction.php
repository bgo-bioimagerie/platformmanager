<?php include_once 'Modules/core/View/layout.php' ?>


    
<?php startblock('content') ?>
<div class="container">
    <div class="row mb-3">
        <div class="col-12">
            <a href="/core/users/impersonate/<?php  echo $data['user']['id'] ?>"><button class="btn btn-warning">Impersonate</button></a>
        </div>
    </div>

    <?php echo $formHtml ?>
    
    <?php echo $formPwdHtml ?>

    <?php echo $rolesTableHtml ?>

</div> <!-- /container -->
<?php echo $script ?>
<?php endblock(); ?>
