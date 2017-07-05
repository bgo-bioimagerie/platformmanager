<?php include 'Modules/ecosystem/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-12 pm-form-short">
    
    <?php if(isset($_SESSION["message"])){
      ?>
        <div class="alert alert-success">
            <?php echo $_SESSION["message"] ?>
        </div>
      <?php  
    }
    ?>
    
    <?php echo $formHtml ?>
</div>
<?php echo $script ?>

<?php if ($id > 0) { ?>
    <br>
    <div class="col-md-12 pm-form-short">
        <div class="page-header">
            <h1>
                <?php echo CoreTranslator::Change_password($lang) ?>
                <br> <small></small>
            </h1>
        </div>
        <div class="row">
            <div class="col-xs-4" id="button-div">
                <button type="button" onclick="location.href = 'ecuserschangepwd/<?php echo $id_space . "/" . $id ?>'" class="btn btn-default"><?php echo CoreTranslator::Change_password($lang) ?></button>
            </div>
        </div>
    </div>

<?php } ?>
<?php
endblock();
