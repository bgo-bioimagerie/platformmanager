<?php
$modelConfig = new CoreConfig();
$menuUrl = $modelConfig->getParam("menuUrl");
$margin = "";
if ($menuUrl != "") {
    $margin = "style=\"margin-top: 50px;\"";
}
?>

<!-- Fixed navbar -->
<nav class="navbar navbar-default navbar-fixed-top" <?php echo $margin ?> role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li><a href="coretiles"><span class="glyphicon glyphicon-th"></span></a></li>
                <?php
                for ($i = 0; $i < count($toolMenu); $i++) {

                    if (count($toolMenu[$i]["items"]) == 0) {
                        ?>
                        <li><a href="<?php echo $toolMenu[$i]["url"] ?>"><span><?php echo $toolMenu[$i]["name"] ?></span></a></li>
                        <?php
                    } else {
                        ?>

                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"> <?php echo $toolMenu[$i]["name"] ?> <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                foreach ($toolMenu[$i]["items"] as $tool) {
                                    $key = $tool['link'];
                                    $value = $tool['name'];
                                    ?>
                                    <li><a href="<?php echo $key ?>" > <?php echo CoreTranslator::MenuItem($value, $lang) ?> </a></li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </li>
                        <?php
                    }
                }
                ?>

                <?php
                if ($toolAdmin) {
                    ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo CoreTranslator::Admin($lang) ?> <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <?php
                            foreach ($toolAdmin as $tool) {
                                $key = $tool['link'];
                                $value = $tool['name'];
                                echo "<li><a href= $key > $value </a></li>";
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"> <?php echo $userName ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href=coremyaccount > <?php echo CoreTranslator::My_Account($lang) ?> </a></li>
                        <li><a href=coresettings > <?php echo CoreTranslator::Settings($lang) ?> </a></li>
                        <li class="divider"></li>
                        <li><a href=corelogout> <?php echo CoreTranslator::logout($lang) ?> </a></li>
                    </ul>
                </li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>
<!-- Bootstrap core JavaScript -->
<script src="externals/jquery-1.11.1.js"></script>
<script src="externals/bootstrap/js/bootstrap.min.js"></script> 
