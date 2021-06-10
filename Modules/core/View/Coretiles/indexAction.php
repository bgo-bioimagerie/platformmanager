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

                                <img src="<?php echo $icon ?>" alt="logo" style="margin-top: -10px;width:100px;height:75px">
                                <span class="glyphicon-class"><?php echo $value ?></span>
                            </a>
                        </li>

                        <?php
                    } else {
                        ?>
                        <div class="col-xs-12 col-md-4 col-lg-2 modulebox">
                            <!-- IMAGE -->
                            <a href="<?php echo "corespace/" . $item["id"] ?>">
                                <img src="<?php echo $item["image"] ?>" alt="logo" style="margin-left: -15px;width:218px;height:150px">
                            </a>
                            <p>
                            </p>
                            <!-- TITLE -->
                            <p style="color:#018181; ">
                                <a href="<?php echo "corespace/" . $item["id"] ?>"> <?php echo $item["name"] ?> </a>
                            </p>

                            <!-- DESC -->
                            <p style="color:#a1a1a1; font-size:12px;">
                                <?php echo $item["description"] ?>
                            </p>

                            <!-- JOIN BUTTON -->
                            <?php    
                                if (!in_array($item["id"], $userSpaces)) {
                            ?>
                                <div style="position: absolute; bottom: 10px; right: 10px">
                                    <a href="<?php echo "coretilesjoinspace/". $item["id"]."/", $_SESSION["id_user"] ?>">
                                        <input type="button" value="Join">
                                    </a>
                                </div>
                            <?php
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
        
    <!-- Alert message => TODO: find a cleaner way to do that? -->
    <div class="col-md-12"> 
        <?php if (isset($_SESSION["message"])) { ?>
            <div class="alert alert-success alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <?php echo $_SESSION["message"] ?>
            </div>
        <?php 
            unset($_SESSION["message"]);
        } ?>
    </div>

</div> <!-- /container -->
<?php
endblock();
