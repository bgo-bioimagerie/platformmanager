<?php require_once 'Framework/ti.php' ?>

<!DOCTYPE html>
<html lang="<?php if(isset($lang)) {echo $lang;} else {echo "en";} ?>">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php
            if (isset($metadesc)) {echo "<meta name=\"description\" content=\"$metadesc\"/>\n";}
        ?>
        <meta name="mode" description="<?php echo $context['dev'] ? 'dev' : 'prod' ?>">
        <base href="<?php echo  $context['rootWeb'] ?>" >
        <title>
            <?php startblock('title') ?>
            Platform-Manager
            <?php endblock() ?>
        </title>
        <?php
        if($context['dev']) {
            echo '<script src="externals/vuejs/vue.js"></script>';
            echo $context['_debugbarRenderer']->renderHead();
        } else {
            echo '<script src="externals/vuejs/vue.min.js"></script>';
        }
        ?>
        <link rel="stylesheet" href="externals/bootstrap/css/bootstrap.min.css">
        
        <link href="externals/core/theme/navbar-fixed-top.css" rel="stylesheet">
        <script src="externals/jquery-1.11.1.js"></script>
        <script src="externals/bootstrap/js/bootstrap.min.js"></script>

        <?php startblock('stylesheet') ?>
        <?php endblock() ?>

        <link rel="stylesheet" href="Modules/core/Theme/core.css">
        <link rel="stylesheet" href="Modules/core/Theme/space.css">
        <link rel='stylesheet' href='/Modules/core/Theme/spacemenu.css' />

    </head>
    <body style="background-color: #e7ecf0;">

        <?php startblock('navbar') ?>
            <?php
            $nav = new Navbar($context['lang']);
            echo $nav->get();
            ?>
        <?php endblock() ?>

        <div id="mainmenu">
        <?php
        if ($context['mainMenu']) {
            echo $context['mainMenu'];
        }
        ?>
        </div>

        <div class="row">
                <div id="app" >
                    <?php if(isset($context['maintenance']) && $context['maintenance']) { ?>
                        <div class="container">
                            <div class="alert alert-warning alert-dismissible  show" role="alert">
                                <?php echo $context['maintenance']; ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>                        
                    <?php } ?>
                    <?php if (isset($flash) && $flash) { ?>
                        <div class="container">
                            <div class="alert alert-<?php echo $flash['class']; ?> alert-dismissible  show" role="alert">
                                <?php echo $flash['msg']; ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                    <?php }?>
                    <div class="col-md-12 col-lg-12">
                        <?php startblock('spacemenu') ?>
                        <?php
                        if ($context['spaceMenu']) {
                            echo $context['spaceMenu'];
                        } ?>
                        <?php endblock() ?>
                    </div>
                    <?php
                    if ($context['sideMenu']) {
                    ?>
                    <div class="col-md-2 col-lg-2" id="sidemenu">
                    <?php
                        echo $context['sideMenu'];
                    ?>
                    </div>
                    <div class="col-md-10 col-lg-10" id="content">
                    <?php } else { ?>
                    <div class="col-md-12 col-lg-12" id="content">
                    <?php } ?>
                    <?php startblock('content') ?>
                    <?php endblock() ?>
                    </div>

            </div>
        </div>

        <?php startblock('footer') ?>
        <footer>
        <div class="container">
            <div class="row">
                <div class="col-sm-4"><a href="http://bgo-bioimagerie.github.io/platformmanager/">Documentation</a></div>
                <div class="col-sm-4"><a href="core/about">About</a></div>
                <div class="col-sm-4"><a href="core/privacy">Privacy</a></div>
            </div>
        </div>
        </footer>
        <?php endblock() ?>

        <?php
        if($context['dev']) {
           echo $context['_debugbarRenderer']->render();
        }
        ?>
    </body>
</html>
