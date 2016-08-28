<?php include 'Modules/mailer/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-6 col-md-offset-2">

    <div class="page-header">
        <h1>
            <?php echo MailerTranslator::Send_email($lang) ?>
            <br> <small></small>
        </h1>
    </div>

    <div>
        <p> <?php echo $message ?></p>
    </div>

    <div class="col-lg-2 col-lg-offset-10">
        <button type="button" onclick="location.href = 'mailer/<?php echo $id_space ?>'" class="btn btn-default"><?php echo CoreTranslator::Ok($lang) ?></button>
    </div>
</div>

<?php
endblock();
