<?php require_once 'Framework/ti.php' ?>
<?php include 'Modules/layout.php' ?>

<!-- header -->
<?php startblock('title') ?>
Platform-Manager
<?php endblock() ?>
<?php startblock('stylesheet') ?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self'">
<meta http-equiv="X-Frame-Options" content="DENY">
<meta http-equiv="X-XSS-Protection" content="1; mode=block">
<meta http-equiv="X-Content-Type-Options" content="nosniff">

<!-- Bootstrap core CSS -->
<link href="externals/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<!-- Custom styles for this template -->
<link href="signin.css" rel="stylesheet">

<!-- Bootstrap core CSS -->
<script src="Modules/core/Theme/caroussel/ie-emulation-modes-warning.js"></script>
<link href="Modules/core/Theme/caroussel/carousel.css" rel="stylesheet"> 
<?php endblock() ?>

<!-- body -->
<?php startblock('content') ?>

<div class="col-md-12" style="background-color: #fff; height:100%">
    <div class="row">
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

        <!-- Carousel and Login -->
        <div class="col-md-12">
            <?php if ($viewCarousel) { ?>
                <!-- Carousel -->
                <div class="col-md-8" style="max-width: 200px;">
                    <?php include "Modules/core/View/Coreconnection/carousel.php" ?>
                </div>
                <!-- Login -->
                <div class="col-md-4">

                <?php } else { ?>
                    <div class="col-xs-12 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
                    <?php } ?>

                    <div class="col-xs-12" style="height:100px;">
                        <p></p>
                    </div>
                    <div class="col-xs-12">
                        <div class="account-wall">
                            <?php if (isset($msgError) && $msgError != ""): ?>
                                <div class="alert alert-danger">
                                    <p><?php echo $msgError ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-xs-12 col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2">
                            <br></br>

                            <form class="form-signin" action="corelogin" method="post">
                                <input name="redirection" type="hidden" value="<?php echo $redirection ?>">
                                <input name="login" type="text" class="form-control" placeholder="<?php echo CoreTranslator::Login($language) ?>" required autofocus>
                                <input name="pwd" type="password" class="form-control" placeholder="<?php echo CoreTranslator::Password($language) ?>" required>
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

                        <!--
                        <div class="col-xs-12 col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2">
                            <br></br>
                            <p class="text-center">
                                <b><?php echo CoreTranslator::or_($language) ?></b>
                            </p>
                        </div>
                        <div class="col-xs-12 col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2">
                            <a class="btn btn-lg btn-primary btn-block" href="corecreateaccount"> <?php echo CoreTranslator::CreateAccount($language) ?> </a>
                        </div>
                        -->

                        <!--
                        <div style="padding-top: 25px; margin-bottom: 25px;">
                                <b>
                            <p class="text-center"><?php echo CoreTranslator::or_($language) ?>
                            </b>
                            </p>
                            </div>
                            <a class="btn btn-lg btn-primary btn-block" href="corecreateaccount"> <?php echo CoreTranslator::CreateAccount($language) ?> </a>
                         -->
                    </div>




                </div>
            </div>
        </div>
    </div>
    <?php
    endblock();
