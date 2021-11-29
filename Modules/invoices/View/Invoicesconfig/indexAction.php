<?php include 'Modules/core/View/spacelayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="row" id="pm-content">

    <div class="col-xs-12 col-md-10 col-md-offset-1">
        <h1><?php echo InvoicesTranslator::configuration($lang) ?></h1>
    </div>

    <?php
    if (isset($_SESSION["message"]) && $_SESSION["message"] != "") {
        $message = $_SESSION["message"];
        ?>
    <?php if (strpos($message, "Error")) { ?>
            <div class="col-xs-12 col-md-10 col-md-offset-1" style="background-color: #e1e1e1;">
                <div class="alert alert-danger" role="alert">
                    <p><?php echo $message ?></p>
                </div>
            </div>
    <?php } else { ?>
            <div class="col-xs-12 col-md-10 col-md-offset-1" style="background-color: #e1e1e1;">
                <div class="alert alert-success" role="alert">
                    <p><?php echo $message ?></p>
                </div>
            </div>
        <?php
        }
        $_SESSION["message"] = "";
    }
    ?>

<?php foreach ($forms as $form) { ?>
        <div class="col-xs-12 col-md-10 col-md-offset-1" style="height: 7px;">
            <p></p>
        </div>
        <div class="col-xs-12 col-md-10 col-md-offset-1" style="background-color: #fff; border-radius: 7px; padding: 7px;">
    <?php echo $form ?>
        </div>
<?php } ?>
    <div class="col-xs-12 col-md-10 col-md-offset-1" style="height: 7px;">
        <p></p>
    </div>
    <div class="col-xs-12 col-md-10 col-md-offset-1" style="background-color: #fff; border-radius: 7px; padding: 7px;">
        <h3><?php echo InvoicesTranslator::PDFTemplate($lang) ?></h3>
        <a class="btn btn-primary" href="invoicepdftemplate/<?php echo $id_space ?>" ><?php echo CoreTranslator::Edit($lang) ?></a>
    </div>

</div>

<?php
endblock();
