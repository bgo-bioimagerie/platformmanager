<?php include_once 'Modules/core/View/spacelayout.php' ?>

    
<?php startblock('content') ?>

<div class="container pm-table">
    <h3>Last 30 days and upcoming bookings</h3>
    <table class="table" aria-label="last 30 days and future bookings">
    <thead><tr><th scope="col">Id</th><th scope="col">Start time</th><th scope="col">Resource</th><th scope="col"></th></tr></thead>
    <?php
    foreach ($data['bookings'] as $booking) {
        ?>
    <div class="table">
        <tr><td><?php echo $booking["id"];  ?></td><td><?php echo date('Y-m-d h:m', $booking['start_time']); ?></td><td><?php echo $booking['resource']; ?></td><td></td></tr>
    </div>
    <?php
    }
?>
    </table>
</div>

<?php endblock(); ?>