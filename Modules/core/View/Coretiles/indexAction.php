<?php include 'Modules/core/View/layout.php' ?>

<?php startblock('stylesheet') ?>

<link rel="stylesheet" type="text/css" href="externals/bootstrap/css/bootstrap.min.css">
<?php
$headless = Configuration::get("headless");
if (!$headless) {
    ?>
    <link href="data/core/theme/navbar-fixed-top.css" rel="stylesheet">
    <?php
}
?>
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/core.css' />
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/space.css' />

<style>
    .modulebox{
        border: solid 1px #e1e1e1; 
        border-bottom: solid 3px #e1e1e1; 
        height:325px; 
        width:220px; 
        margin-left: 25px;
        margin-top: 25px;
    }    
</style>

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
                        ?>
                        <div class="col-xs-12 col-md-4 col-lg-2 modulebox">
                            <!-- IMAGE -->
                            <a href="<?php echo $item["link"] ?>">
                                <img src="<?php echo $item["icon"] ?>" alt="logo" style="margin-left: -15px;width:218px;height:150px">
                            </a>
                            <p>
                            </p>
                            <!-- TITLE -->
                            <p style="color:#018181; ">
                                <a href="<?php echo $item["link"] ?>"> <?php echo $item["name"] ?> </a>
                            </p>

                            <!-- DESC -->
                            <p style="color:#a1a1a1; font-size:12px;">
                                <?php echo $item["description"] ?>
                            </p>

                            
                        </div>   
                        <?php
                        /*
                          $color = '#428bca';
                          if (isset($item['color']) && $item['color'] != "") {
                          $color = $item['color'];
                          }
                          ?>
                          <li style="background-color:<?php echo $color ?>">
                          <a href="<?php echo $item["link"] ?>">
                          <span aria-hidden="true"><img src="<?php echo $item["icon"] ?>" width="90px" alt="" /></span>
                          <span class="glyphicon-class"><?php echo $item["name"] ?></span>
                          </a>
                          </li>
                          <?php
                         */
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
