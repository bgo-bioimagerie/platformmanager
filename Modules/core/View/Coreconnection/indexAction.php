<?php require_once 'Framework/ti.php' ?>
<?php include 'Modules/layout.php' ?>


<?php startblock('stylesheet') ?>

<meta http-equiv="X-XSS-Protection" content="1; mode=block">
<meta http-equiv="X-Content-Type-Options" content="nosniff">

<!-- Custom styles for this template -->
<link href="Modules/core/Theme/signin.css" rel="stylesheet">

<?php endblock(); ?>

<!-- body -->
<?php startblock('content') ?>

<div class="container">

    <div class="row">
        <!-- Title -->
        <div class="col-12">
            <h1 class="text-center login-title"><?php echo $home_title ?></h1>
        </div>

        <!-- Message -->
        <div class="col-12">
            <p></p>
            <h3 style="text-align:center;"><?php echo $home_message ?></h3>
            <p></p>
        </div>

        <!-- Login -->
        <div class="col-12 col-md-8">
            <div class="row">
                <div class="col-12">
                    <?php if (isset($msgError) && $msgError != "") { ?>
                        <div role="alert" class="alert alert-danger">
                            <p><?php echo $msgError ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="row justify-content-md-center">
                <div class="col-12 col-md-6">
                    <form class="form form-signin" action="corelogin" method="post">
                        <input class="form-control" name="redirection" type="hidden" value="<?php echo $redirection ?>">
                        <input class="form-control mb-3" name="login" autocomplete="username" type="text" class="form-control" placeholder="<?php echo CoreTranslator::Login($language) ?>" required autofocus>
                        <input class="form-control mb-3" name="pwd" autocomplete="current-password" type="password" class="form-control" placeholder="<?php echo CoreTranslator::Password($language) ?>" required>
                        <div class="checkbox mb-3">
                            <label class="form-check-label" for="remember"><?php echo CoreTranslator::RememberMe($language) ?></label>
                            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="">
                        </div>
                        <div class="row mb-3">
                            <div class="col-12 col-md-3"><button class="btn btn-primary" type="submit"> <?php echo CoreTranslator::Ok($language) ?> </button></div>
                            <div class="col-12 col-md-9">
                                <div><a href="corepasswordforgotten" class="m-3"><?php echo CoreTranslator::PasswordForgotten($language) ?></a></div>
                                <div><a href="mailto:<?php echo $admin_email ?>" class="m-3"><?php echo CoreTranslator::Contact_the_administrator($language) ?></a></div>
                            </div>
                        </div>
                    </form>

                </div>


            </div>
        </div>
        <?php if(Configuration::get('allow_registration', false)) { ?>
            <div class="col-12 col-md-4">
                <a class="btn btn-lg btn-primary btn-block" href="corecreateaccount"> <?php echo CoreTranslator::CreateAccount($language) ?> </a>
            </div>
        <?php }  ?>
    </div>
    <div class="row justify-content-md-center">

        <?php if (!empty($providers)) { ?>
        <div class="col-12" style="text-align:center;">
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

</div>
<?php endblock(); ?>
