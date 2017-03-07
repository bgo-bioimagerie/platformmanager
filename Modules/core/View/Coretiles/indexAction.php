<?php include 'Modules/core/View/layout.php' ?>

<?php startblock('stylesheet') ?>

<link rel="stylesheet" type="text/css" href="externals/bootstrap/css/bootstrap.min.css">
<link rel='stylesheet' type='text/css' href='data/core/theme/navbar-fixed-top.css' />
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/core.css' />
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/space.css' />

<?php endblock(); ?>
<!-- body -->     
<?php startblock('content') ?>
<div class="col-xs-12 pm-tile-container">
    <div class="container">
        <?php
        for ($i = 0; $i < count($toolMenu); $i++) {
            ?>
            <div class="page-header">
                <h2>
                    <?php echo $toolMenu[$i]["name"] ?>
                    <br>
                </h2>
            </div>
            <div class="bs-glyphicons">
                <ul class="bs-glyphicons-list">
                    <?php
                    foreach ($toolMenu[$i]["items"] as $item) {
                        $color = '#428bca';
                        if (isset($item['color']) && $item['color'] != "") {
                            $color = $item['color'];
                        }
                        ?>
                        <li style="background-color:<?php echo $color ?>">
                            <a href="<?php echo $item["link"] ?>">
                                <span aria-hidden="true"><img src="<?php echo $item["icon"] ?>" height="50px" alt="" /></span>
                                <span class="glyphicon-class"><?php echo $item["name"] ?></span>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                    <ul/>
            </div>
            <?php
        }
        ?>

        <?php
        if ($_SESSION["user_status"] >= CoreStatus::$ADMIN) {
            ?>
            <div class="page-header">
                <h2>
                    <?php echo CoreTranslator::Admin($lang) ?>
                    <br>
                </h2>
            </div>
            <div class="bs-glyphicons">
                <ul class="bs-glyphicons-list">

                    <?php
                    if (isset($toolAdmin)) {
                        foreach ($toolAdmin as $tool) {
                            $key = $tool['link'];
                            $value = $tool['name'];
                            $icon = $tool['icon'];
                            $color = '#428bca';
                            if (isset($tool['color']) && $tool['color'] != "") {
                                $color = $tool['color'];
                            }
                            ?>
                            <li style="background-color:<?php echo $color ?>">
                                <a href="<?php echo $key ?>">
                                    <span class="glyphicon <?php echo $icon ?>" aria-hidden="true"></span>
                                    <span class="glyphicon-class"><?php echo $value ?></span>
                                </a>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
                <?php
            }
            ?>
        </div>
    </div>

</div> <!-- /container -->
<?php
endblock();
