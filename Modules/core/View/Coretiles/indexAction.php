<?php include 'Modules/core/View/layout.php' ?>

<?php startblock('stylesheet') ?>

<link rel="stylesheet" type="text/css" href="externals/bootstrap/css/bootstrap.min.css">
<?php
$headless = Configuration::get("headless");
if (!$headless) {
    ?>
    <link href="externals/core/theme/navbar-fixed-top.css" rel="stylesheet">
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

<?php if ($showSubBar){ ?> 
<div class="col-md-12 pm-nav">
    <?php include('Modules/core/View/Coretiles/navbar.php'); ?>
</div>  
<?php } ?>


<div class="col-xs-12" style="background-color: #fff">
    <div class="container"<?php if ($showSubBar){echo 'style="margin-top: 50px;"';} ?> >
        <h3><?php echo $title ?></h3>    
    </div>
</div>

<div class="col-xs-12 pm-tile-container"  >

    <div class="container">

        
        <div class="bs-glyphicons">
            <ul class="bs-glyphicons-list">
                <?php
                foreach ($items as $item) {

                    if ($iconType == 1) {

                        $key = "corespace/" . $item['id'];
                        $value = $item['name'];
                        $icon = $item['image'];
                        $color = '#428bca';
                        if (isset($item['color']) && $item['color'] != "") {
                            $color = $item['color'];
                        }
                        ?>
                        <li style="background-color:<?php echo $color ?>">
                            <a href="<?php echo $key ?>">

                                <?php if(isset($icon)) {?><img src="<?php echo $icon ?>" alt="logo" style="margin-top: -10px;width:100px;height:75px"><?php } ?>
                                <span class="glyphicon-class"><?php echo $value ?></span>
                            </a>
                        </li>

                        <?php
                    } else {
                        ?>
                        <div class="col-xs-12 col-md-4 col-lg-2 modulebox">
                            <!-- IMAGE -->
                            <a href="<?php echo "corespace/" . $item["id"] ?>">
                            <?php if(isset($item["image"])) {?><img onerror="this.style.display='none'" src="<?php echo $item["image"] ?>" alt="logo" style="margin-left: -15px;width:218px;height:150px"><?php } ?>
                            </a>
                            <p>
                            </p>
                            <!-- TITLE -->
                            <p style="color:#018181; ">
                                <a href="<?php echo "corespace/" . $item["id"] ?>"> <?php echo $item["name"] ?></a>
                                <?php if(isset($_SESSION["login"])) { ?>
                                    <?php if(isset($star[$item["id"]])) { ?>
                                        <a aria-label="remove from favorites" href="<?php echo "coretiles/1/".$submenu."/unstar/".$item["id"] ?>"><span class="glyphicon glyphicon-star"></span></a>
                                    <?php } else { ?>
                                        <a aria-label="add to favorites" href="<?php echo "coretiles/1/".$submenu."/star/".$item["id"] ?>"><span class="glyphicon glyphicon-star-empty"></span></a>
                                    <?php } ?>
                                    <?php if($item["status"] == 0) { echo '<span class="glyphicon glyphicon-lock" aria-hidden="true" aria-label="private"></span>'; } ?>
                                <?php } ?>
                            </p>

                            <!-- DESC -->
                            <p style="color:#a1a1a1; font-size:12px;">
                                <?php echo $item["description"] ?>
                            </p>
                            <div ><small>
                            <?php if($item["support"]) {  echo 'support: <a href="mailto:'.$item["support"].'">'.$item["support"].'</a>'; } ?>
                            </small></div>

                            <!-- JOIN BUTTON -->
                            <?php
                                if (!in_array($item["id"], $spacesUserIsAdminOf) && isset($_SESSION["login"])) {
                                    if (!in_array($item["id"], $userPendingSpaces)) {
                                        $isMemberOfSpace = (in_array($item["id"], $userSpaces)) ? true : false;
                                        if(!$isMemberOfSpace) {
                            ?>
                                        <div style="position: absolute; bottom: 20px; right: 10px">
                                            <a href="<?php echo "coretilesselfjoinspace/". $item["id"] ?>">
                                                <button type="button" class="btn btn-md btn-success">
                                                    <?php echo CoreTranslator::RequestJoin($isMemberOfSpace, $lang) ?>
                                                </button>
                                            </a>
                                        </div>
                                    <?php
                                        }
                                    } else {
                                    ?>
                                        <div style="position: absolute; bottom: 20px; right: 10px">
                                            <button type="button" class="btn btn-md btn-info" disabled>
                                                <?php echo CoreTranslator::JoinRequested($lang) ?>
                                            </button>
                                        </div>
                            <?php
                                    }
                                }
                            ?>
                        </div>   
                        <?php
                    }
                }
                ?>
            </ul>
        </div>
    </div>
</div> <!-- /container -->
<?php
endblock();
