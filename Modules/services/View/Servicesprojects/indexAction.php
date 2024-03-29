<?php include_once 'Modules/services/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="container pm-table">
    
    <?php
    if (count($years) > 0) {
        ?>
    <div class="col-12" style="margin-bottom: 14px; padding-bottom: 7px; border-bottom: 1px solid #e1e1e1;">
        <div class="text-center">
            <div class="btn-group btn-group-md">
        <?php
        foreach ($years as $yea) {
            $activeYear = "";
            if ($year == $yea) {
                $activeYear = "active";
            }
            ?>
            <a class="btn btn-outline-dark <?php echo $activeYear ?>" href="<?php echo $yearsUrl . "/" .$id_space ."/". $yea ?>"><?php echo $yea ?></a>
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

<?php endblock(); ?>