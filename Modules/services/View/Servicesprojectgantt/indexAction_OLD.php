
<?php include 'Modules/services/View/layout.php' ?>

    


<?php startblock('stylesheet') ?>



<link href="externals/node_modules/@taitems/jquery-gantt/css/style.css" type="text/css" rel="stylesheet">
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

    .fn-gantt .nav-link {
        padding: 0px;
    }
    
    <?php 
      echo $css;
    ?>
</style>

<?php endblock(); ?>

<?php startblock('content') ?>

<div class="pm-table">
    
    <div class="col-12">
        <?php 
        if($allPeriod == 1){
            echo "<h3>" . ServicesTranslator::GanttPeriod($lang) . "<h3/>";
        }
        else{
            echo "<h3>" . ServicesTranslator::GanttOpened($lang) . "<h3/>";
        }
        ?>
    </div>    
    <div class="col-12">
        <div class="text-center">
            <div class="btn-group btn-group-sm">
                
                <button class="btn btn-outline-dark <?php if ($activeGantt == "") {echo "active";} ?>" onclick="location.href = 'servicesprojectgantt/<?php echo $id_space ?>';"><?php echo ServicesTranslator::All_projects($lang) ?></button>
                
                <?php 
                foreach( $personInCharge as $pic ){
                    ?>
                    <button class="btn btn-outline-dark <?php if ($activeGantt == $pic["id"]) {echo "active";} ?>" onclick="location.href = 'servicesprojectgantt/<?php echo $id_space ?>/<?php echo $allPeriod ?>/<?php echo $pic["id"] ?>';"><?php echo $pic["user_name"] ?></button>
                <?php
                }
                ?>
                    
            </div>
        </div> 
    </div>    
      
    <div class="col-12">
        <div class="gantt"></div>
    </div>
    
</div>


<script src="externals/node_modules/@taitems/jquery-gantt/js/jquery.fn.gantt.min.js"></script>
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
                //alert("Item clicked - show some details");
            },
            onAddClick: function (dt, rowId) {
                //alert("Empty space clicked - add an item!");
            },
            onRender: function () {
                // console.log("chart rendered");
            }
        });
        //prettyPrint();
    });
    
</script>

<?php endblock(); ?>