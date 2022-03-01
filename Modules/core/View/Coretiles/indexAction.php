<?php include 'Modules/core/View/layout.php' ?>

<?php startblock('stylesheet') ?>

<style>
    .modulebox{
        border: solid 1px #e1e1e1; 
        border-bottom: solid 3px #e1e1e1; 
        min-height:325px; 
        width:220px; 
        margin-left: 25px;
        margin-top: 25px;
        background-color: white;
    }    
</style>

<?php endblock(); ?>
    
<?php startblock('content') ?>

<?php if ($showSubBar){ ?> 
<div class="row">
<div class="col-12 pm-nav">
    <?php include('Modules/core/View/Coretiles/navbar.php'); ?>
</div>
</div>
<?php } ?>

<div class="row">
    <div class="container"<?php if ($showSubBar){echo 'style="margin-top: 50px;"';} ?> >
        <h3><?php echo $title ?></h3>    
    </div>
</div>

<div class="container"  >

    <div class="row">

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
                        <div class="card">
                        <?php if(isset($icon)) {?><img class="card-img-top" src="<?php echo $icon ?>" alt="logo" style="margin-top: -10px;width:100px;height:75px"><?php } ?>
                        <div class="card-header" style="background-color:<?php echo $color ?>">
                            <a href="<?php echo $key ?>"><?php echo $value ?></a>
                        </div>
                        </div>

                        <?php
                    } else {
                        ?>
                        <div class="col-12 col-md-4 m-2">
                            <div class="card">
                            <div class="card-header">
                                <a href="<?php echo "corespace/" . $item["id"] ?>"> <?php echo $item["name"] ?></a>
                                <?php if(isset($_SESSION["login"])) { ?>
                                    <?php if(isset($star[$item["id"]])) { ?>
                                        <a aria-label="remove from favorites" href="<?php echo "coretiles/1/".$submenu."/unstar/".$item["id"] ?>"><span class="bi-star-fill"></span></a>
                                    <?php } else { ?>
                                        <a aria-label="add to favorites" href="<?php echo "coretiles/1/".$submenu."/star/".$item["id"] ?>"><span class="bi-star"></span></a>
                                    <?php } ?>
                                    <?php if($item["status"] == 0) { echo '<span class="bi-lock-fill" aria-hidden="true" aria-label="private"></span>'; } ?>
                                <?php } ?>
                            </div>

                            <!-- DESC -->
                            <div class="card-body">
                            <?php if(isset($item["image"])) {?><img class="card-img-top" onerror="this.style.display='none'" src="<?php echo $item["image"] ?>" alt="logo" style="width:218px;height:150px"><?php } ?>
                                <?php echo $item["description"] ?>
                            </div>
                            <div class="card-footer">
                                <small class="mb-1">
                                <?php if($item["support"]) {  echo 'support: <a href="mailto:'.$item["support"].'">'.$item["support"].'</a>'; } ?>
                                </small>

                            <!-- JOIN BUTTON -->
                            <?php
                                if ($item['status'] == 1 && !in_array($item["id"], $spacesUserIsAdminOf) && (isset($_SESSION["id_user"]) && $_SESSION["id_user"] > 0)) {
                                    if (!in_array($item["id"], $userPendingSpaces)) {
                                        $isMemberOfSpace = (in_array($item["id"], $userSpaces)) ? true : false;
                                        if(!$isMemberOfSpace) {
                            ?>
                                        <div>
                                            <a href="<?php echo "coretilesselfjoinspace/". $item["id"] ?>">
                                                <button type="button" class="btn btn-md btn-success">
                                                    <?php echo CoreTranslator::RequestJoin($isMemberOfSpace, $lang) ?>
                                                </button>
                                            </a>
                                        </div>
                                    <?php
                                        }
                                    } else if (!isset($_SESSION["id_user"]) || $_SESSION["id_user"] <= 0) {
                                    ?>
                                        <div>
                                            <button type="button" class="btn btn-md btn-info" disabled>
                                                <?php echo CoreTranslator::JoinRequested($lang) ?>
                                            </button>
                                        </div>
                            <?php
                                    }
                                }
                            ?>
                            </div>
                            </div>
                        </div>   
                        <?php
                    }
                }
                ?>
    </div>
</div> <!-- /container -->
<?php endblock(); ?>
