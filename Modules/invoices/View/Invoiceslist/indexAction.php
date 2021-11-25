<?php include 'Modules/invoices/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-10 pm-table">
    
    <?php 
    if(count($years) > 0){
        ?>
    <div class="col-xs-10" style="margin-bottom: 14px; padding-bottom: 7px; border-bottom: 1px solid #e1e1e1;">
        <div class="text-center">
            <div class="btn-group btn-group-md">
        <?php
        foreach($years as $yea){
            $activeYear = "";
            if($year == $yea){
               $activeYear = "active"; 
            }
            ?>
            <a class="btn btn-default <?php echo $activeYear ?>" href="<?php echo "invoices/" .$id_space ."/". $sent . "/" . $yea ?>"><?php echo $yea ?></a>
           <?php    
        }
        ?>
            </div>
        </div>
    </div>
    <?php
    }
    ?>
    
    <?php echo $tableHtml ?>
</div>

<?php endblock();