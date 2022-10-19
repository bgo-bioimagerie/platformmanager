<?php
include_once 'Modules/invoices/View/layout.php';
require_once 'Framework/Constants.php';
?>

    
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
            <a class="btn btn-outline-dark <?php echo $activeYear ?>" href="<?php echo "invoices/" .$id_space ."/". $sent . "/" . $yea ?>"><?php echo $yea ?></a>
           <?php
        }
        ?>
            </div>
        </div>
    </div>
    <?php
    }
?>
    
    <?php if (!empty($requests)) {
        ?>
    <table aria-label="list of invoice generation requests" class="table">
        <thead><tr><th scope="col">Date</th><th scope="col">Invoice requests</th><th scope="col">Status  <button onclick="location.reload()" type="button" class="btn btn-sm btn-info">Refresh</button></th></tr></thead>
        <tbody>
        <?php foreach ($requests as $req => $value) { ?>
            <tr><td><?php $ts = explode(':', $req)[0];
            echo date(Constants::DATETIME_FORMAT, $ts); ?></td><td><?php echo $req ?></td><td><span <?php if (str_starts_with($value, 'error')) {
                echo 'class="label label-danger"';
            }  ?>><?php echo $value?></span></td></tr>
        <?php } ?>
        </tbody>
    </table>
    <?php } ?>
    
    <?php echo $tableHtml ?>
</div>



<?php endblock(); ?>