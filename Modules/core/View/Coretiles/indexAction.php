<?php include 'Modules/core/View/layout.php' ?>

<?php startblock('stylesheet') ?>

<link rel="stylesheet" type="text/css" href="externals/bootstrap/css/bootstrap.min.css">
<link rel='stylesheet' type='text/css' href='data/core/theme/navbar-fixed-top.css' />
<style>
    .bs-glyphicons{margin:0 -10px 20px;overflow:hidden}
    .bs-glyphicons-list{padding-left:0;list-style:none}
    .bs-glyphicons li{float:left;width:25%;height:115px;padding:25px;
                      font-size:10px;line-height:1.4;text-align:center;background-color:#f9f9f9;border:1px solid #fff}

    .bs-glyphicons .glyphicon{margin-top:5px;margin-bottom:10px;font-size:24px}
    .bs-glyphicons .glyphicon-class{display:block;text-align:center;word-wrap:break-word}

    .bs-glyphicons li:hover{color:#fff;background-color:<?php echo $navcolor ?>}@media (min-width:768px){
        .bs-glyphicons{margin-right:0;margin-left:0}
        .bs-glyphicons li{width:12.5%;font-size:12px}
    }

    .bs-glyphicons li a{color:#888888;}
    .bs-glyphicons li a:hover{color:<?php echo $navcolortxt ?>;}

</style>

<?php endblock(); ?>
<!-- body -->     
<?php startblock('content') ?>
<div class="container">
    
    <?php 
    for($i = 0 ; $i < count($toolMenu) ; $i++){
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
        foreach($toolMenu[$i]["items"] as $item){
            ?>
            <li>
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
                        ?>
                        <li>
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

</div> <!-- /container -->
<?php
endblock();
