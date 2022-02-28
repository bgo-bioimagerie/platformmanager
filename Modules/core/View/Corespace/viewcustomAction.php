<?php include 'Modules/core/View/spacelayout.php' ?>


    
<?php startblock('content') ?>

<div class="row" style="background-color: #fff; height: 2000px;">

    <div class="container" style="background-color: #fff;">

        <?php
        require_once 'Modules/com/Controller/ComtileController.php';
        $navController = new ComtileController(new Request(array(), false));
        echo $navController->indexAction($id_space);
        ?>
        
        <?php
        foreach($sections as $section){    
        ?>
        
            <h3>
                <?php echo $section["name"] ?>
            </h3>
        <div class="pm-tiles" >
            <div class="pm-tiles bs-glyphicons">
                <ul class="pm-tiles bs-glyphicons-list">
                    <?php
                    foreach ($section["items"] as $item) {
                        ?>
                        <li style="background-color:<?php echo $item["bgcolor"] ?>; color:<?php echo $item["color"] ?>">
                            <a href="<?php echo $item["url"] ?>">
                                <span class="pm-tiles glyphicon <?php echo $item["icon"] ?>" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class"><?php echo $item["name"] ?></span>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                    </ul>
            </div>
        </div>
        <?php
        }
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
                        <li style="background-color:<?php echo $space['color'] ?>;">
                            <a href="<?php echo "spaceconfig/" . $space["id"] ?>">
                                <span class="pm-tiles glyphicon glyphicon-cog" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class"><?php echo CoreTranslator::Configuration($lang) ?></span>
                            </a>
                        </li>
                        <li style="background-color:<?php echo $space['color'] ?>;">
                            <a href="<?php echo "spaceconfiguser/" . $space["id"] ?>">
                                <span class="pm-tiles glyphicon glyphicon-cog" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class"><?php echo CoreTranslator::Access($lang) ?></span>
                            </a>
                        </li> 
                        <li style="background-color:<?php echo $space['color'] ?>;">
                            <a href="<?php echo "spacedashboard/" . $space["id"] ?>">
                                <span class="pm-tiles glyphicon glyphicon-th" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class"><?php echo CoreTranslator::Dashboard($lang) ?></span>
                            </a>
                        </li> 
                    </ul>
                </div>
            </div>
            <?php
        }
        ?>

    </div> <!-- /container -->
</div>
<?php endblock(); ?>
