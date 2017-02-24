<?php include 'Modules/core/View/spacelayout.php' ?>

<?php startblock('stylesheet') ?>

<link rel="stylesheet" type="text/css" href="externals/bootstrap/css/bootstrap.min.css">
<link rel='stylesheet' type='text/css' href='data/core/theme/navbar-fixed-top.css' />
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/spacemenu.css' />
<style>
    .pm-tiles .bs-glyphicons{margin:0 -10px 20px;overflow:hidden}
    .pm-tiles .bs-glyphicons-list{padding-left:0;list-style:none}
    .pm-tiles .bs-glyphicons li{float:left;width:25%;height:115px;padding:25px;
                                font-size:10px;line-height:1.4;text-align:center;background-color:#f9f9f9;border:1px solid #fff}

    .pm-tiles .bs-glyphicons .glyphicon{margin-top:5px;margin-bottom:10px;font-size:24px}
    .pm-tiles .bs-glyphicons .glyphicon-class{display:block;text-align:center;word-wrap:break-word}

    .pm-tiles .bs-glyphicons li:hover{color:#fff;background-color:<?php echo $navcolor ?>}@media (min-width:768px){
        .pm-tiles .bs-glyphicons{margin-right:0;margin-left:0}
        .pm-tiles .bs-glyphicons li{width:12.5%;font-size:12px}
    }

    .pm-tiles .bs-glyphicons li a{color:#888888;}
    .pm-tiles .bs-glyphicons li a:hover{color:<?php echo $navcolortxt ?>;}

</style>

<?php endblock(); ?>
<!-- body -->     
<?php startblock('content') ?>

<div class="container">
    <div class="page-header">
        <h2>
            <?php echo CoreTranslator::Tools($lang) ?>
            <br>
        </h2>
    </div>
    <div class="pm-tiles" >
        <div class="pm-tiles bs-glyphicons">
            <ul class="pm-tiles bs-glyphicons-list">
                <?php
                $configModel = new CoreConfig();
                foreach ($spaceMenuItems as $item) {
                    $classTranslator = ucfirst($item["module"]) . "Translator";
                    $TranslatorFile = "Modules/" . $item["module"] . "/Model/" . $classTranslator . ".php";
                    require_once $TranslatorFile;
                    $translator = new $classTranslator();
                    $url = $item["url"];
                    $donfigTitle = $configModel->getParamSpace($url . "menuname", $id_space);
                    if ($donfigTitle != "") {
                        $name = $donfigTitle;
                    } else {
                        $name = $translator->$url($lang);
                    }
                    ?>
                    <li>
                        <a href="<?php echo $url . "/" . $id_space ?>">
                            <span class="pm-tiles glyphicon <?php echo $item["icon"] ?>" aria-hidden="true"></span>
                            <span class="pm-tiles glyphicon-class"><?php echo $name ?></span>
                        </a>
                    </li>
                    <?php
                }
                ?>
                <ul/>
        </div>
    </div>
    <?php
    if ($showAdmMenu) {
        ?>
        <div class="page-header">
            <h2>
                <?php echo CoreTranslator::Admin($lang) ?>
                <br>
            </h2>
        </div>
        <div class="pm-tiles" >
            <div class="pm-tiles bs-glyphicons">
                <ul class="pm-tiles bs-glyphicons-list">
                    <li>
                        <a href="<?php echo "spaceconfig/" . $space["id"] ?>">
                            <span class="pm-tiles glyphicon glyphicon-cog" aria-hidden="true"></span>
                            <span class="pm-tiles glyphicon-class"><?php echo CoreTranslator::Configuration($lang) ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo "spaceconfiguser/" . $space["id"] ?>">
                            <span class="pm-tiles glyphicon glyphicon-cog" aria-hidden="true"></span>
                            <span class="pm-tiles glyphicon-class"><?php echo CoreTranslator::Access($lang) ?></span>
                        </a>
                    </li>  
                </ul>
            </div>
        </div>
        <?php
    }
    ?>

</div> <!-- /container -->
<?php
endblock();
