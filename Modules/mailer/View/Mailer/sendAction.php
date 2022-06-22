<?php include 'Modules/mailer/View/layout.php' ?>

    
<?php startblock('content') ?>
<div>
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
        <button type="button" onclick="location.href = 'mailer/<?php echo $id_space ?>'" class="btn btn-outline-dark"><?php echo CoreTranslator::Ok($lang) ?></button>
    </div>
</div>

<?php endblock(); ?>
