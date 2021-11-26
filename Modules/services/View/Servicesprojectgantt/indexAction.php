<?php include 'Modules/services/View/layout.php' ?>

<!-- body -->     


<?php startblock('stylesheet') ?>

<link href="http://maxcdn.bootstrapcdn.com/bootstrap/latest/css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="externals/jQueryGantt/css/style.css" type="text/css" rel="stylesheet">
<link href="http://cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.min.css" rel="stylesheet" type="text/css">
<style type="text/css">

    h1 {
        margin: 40px 0 20px 0;
    }
    h2 {
        font-size: 1.5em;
        padding-bottom: 3px;
        border-bottom: 1px solid #DDD;
        margin-top: 50px;
        margin-bottom: 25px;
    }
    table th:first-child {
        width: 150px;
    }
    
    <?php 
      echo $css;
    ?>
</style>

<?php endblock(); ?>

<?php startblock('content') ?>

<div class="pm-table">
    
    <div class="col-md-12">
        <?php 
        if($allPeriod == 1){
            echo "<h3>" . ServicesTranslator::GanttPeriod($lang) . "<h3/>";
        }
        else{
            echo "<h3>" . ServicesTranslator::GanttOpened($lang) . "<h3/>";
        }
        ?>
    </div>    
    <div class="col-md-12">
        <div class="text-center">
            <div class="btn-group btn-group-sm">
                
                <button class="btn btn-default <?php if ($activeGantt == "") {echo "active";} ?>" onclick="location.href = 'servicesprojectgantt/<?php echo $id_space ?>';"><?php echo ServicesTranslator::All_projects($lang) ?></button>
                
                <?php 
                foreach( $personInCharge as $pic ){
                    ?>
                    <button class="btn btn-default <?php if ($activeGantt == $pic["id"]) {echo "active";} ?>" onclick="location.href = 'servicesprojectgantt/<?php echo $id_space ?>/<?php echo $allPeriod ?>/<?php echo $pic["id"] ?>';"><?php echo $pic["user_name"] ?></button>
                <?php
                }
                ?>
                    
            </div>
        </div> 
    </div>    
      
    <div class="col-md-12">
        <div class="gantt"></div>
    </div>
    
</div>

<script src="externals/jQueryGantt/js/jquery.min.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="externals/jQueryGantt/js/jquery.fn.gantt.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/latest/js/bootstrap.min.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.min.js"></script>
<script>
    
    $(function () {

        "use strict";

        $(".gantt").gantt({
            source: <?php echo $projectsjson ?>,
            scale: "weeks",
            minScale: "weeks",
            maxScale: "months",
            itemsPerPage: 100,
            onItemClick: function (data) {
                alert("Item clicked - show some details");
            },
            onAddClick: function (dt, rowId) {
                alert("Empty space clicked - add an item!");
            },
            onRender: function () {
                console.log("chart rendered");
            }
        });
        prettyPrint();
    });
    
</script>

<?php
endblock();
