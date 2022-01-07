<?php include 'Modules/core/View/spacelayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="container">
    <h3>Last 30 days and upcoming bookings</h3>
    <table class="table" aria-label="last 30 days and future bookings">
    <thead><tr><th scope="col">Id</th><th scope="col">Start time</th><th scope="col">Resource</th><th scope="col">Evaluation</th></tr></thead>
    <?php
    foreach($data['bookings'] as $booking) {
    ?>
    <div class="table">
        <tr><td><?php echo $booking["id"];  ?></td><td><?php echo date('Y-m-d h:m', $booking['start_time']); ?></td><td><?php echo $booking['resource']; ?></td><td>
            <?php if($data['rating'] !== null) {?>
            <?php if (isset($data['rating'][$booking['id']])) { ?>
                <?php echo $data['rating'][$booking['id']]['rate']; ?>
            <?php } else { ?>
                <a href="/rating/<?php echo $context['currentSpace']['id']; ?>/booking/<?php echo $booking['id']; ?>"><button class="btn btn-primary">Evaluate</button></a>
            <?php } ?>
            <?php } ?>
        </td></tr>
    </div>
    <?php
    }
    ?>
    </table>
</div>

<?php endblock();