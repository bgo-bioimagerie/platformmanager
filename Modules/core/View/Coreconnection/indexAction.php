<?php require_once 'Framework/ti.php' ?>
<?php include 'Modules/layout.php' ?>


<?php startblock('stylesheet') ?>

<?php
if (getenv('PFM_MODE') != 'dev') {
  echo "<meta http-equiv=\"Content-Security-Policy\" content=\"default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'\">\n";
} else {
    echo "<meta http-equiv=\"Content-Security-Policy-Report-Only\" content=\"default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'\">\n";
}
?>
<meta http-equiv="X-XSS-Protection" content="1; mode=block">
<meta http-equiv="X-Content-Type-Options" content="nosniff">

<!-- Custom styles for this template -->
<link href="Modules/core/Theme/signin.css" rel="stylesheet">

<?php endblock() ?>

<!-- body -->
<?php startblock('content') ?>

<div class="row" style="background-color: #fff; height:100%">
        <!-- Title -->
        <div class="col-sm-12">
            <h1 class="text-center login-title"><?php echo $home_title ?></h1>
        </div>

        <!-- Message -->
        <div class="col-sm-10 col-sm-offset-1 text-center">
            <p></p>
            <h3 style="text-align:center;"><?php echo $home_message ?></h3>
            <p></p>
        </div>

        <!-- Login -->
        <div class="col-sm-12">


                    <div class="col-xs-12 col-md-4 col-md-offset-4 col-lg-8 col-lg-offset-2">
                        <div class="account-wall">
                            <?php if (isset($msgError) && $msgError != "") { ?>
                                <div class="alert alert-danger">
                                    <p><?php echo $msgError ?></p>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-xs-12  col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">
                            <br></br>

                            <form class="form-signin" action="corelogin" method="post">
                                <input name="redirection" type="hidden" value="<?php echo $redirection ?>">
                                <input name="login" autocomplete="username" type="text" class="form-control" placeholder="<?php echo CoreTranslator::Login($language) ?>" required autofocus>
                                <input name="pwd" autocomplete="current-password" type="password" class="form-control" placeholder="<?php echo CoreTranslator::Password($language) ?>" required>
                                <div class="checkbox">
                                    <label><input type="checkbox" name="remember" value=""><?php echo CoreTranslator::RememberMe($language) ?></label>
                                </div>

                                <button class="btn btn-lg btn-primary btn-block" type="submit"> <?php echo CoreTranslator::Ok($language) ?> </button>
                            </form>

                        </div>
                        <br/>
                        <div class="col-xs-12 col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2">
                            <a href="corepasswordforgotten" class="text-center new-account"><?php echo CoreTranslator::PasswordForgotten($language) ?></a>
                        </div>

                        <div class="col-xs-12 col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2">
                            <a href="mailto:<?php echo $admin_email ?>" class="text-center new-account"><?php echo CoreTranslator::Contact_the_administrator($language) ?></a>
                        </div>

                        <?php if(Configuration::get('allow_registration', false)) { ?>
                        <div class="col-xs-12 col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2">
                            <br/>
                            <p class="text-center">
                                <strong><?php echo CoreTranslator::or_($language) ?></strong>
                            </p>
                        </div>
                        <div class="col-xs-12 col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2">
                            <a class="btn btn-lg btn-primary btn-block" href="corecreateaccount"> <?php echo CoreTranslator::CreateAccount($language) ?> </a>
                        </div>
                        <?php }  ?>

                    </div>
        </div>

        <?php if (!empty($providers)) { ?>
        <div class="col-sm-12" style="text-align:center;">
            <h2>Log with external connection providers</h2>
            <p><small>You must have link provider with your account before in account settings.</small></p>
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
        <?php } ?>
</div>
    <?php
    endblock();
