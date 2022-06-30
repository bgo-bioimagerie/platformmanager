<?php include 'Modules/booking/View/layoutsettings.php' ?>

    
<?php startblock('content') ?>

<div class="container pm-table">
    <?php echo $tableHtml ?>

    <div class="table-responsive mb-3">
        <h2>Calendars</h2>
        <table class="table" aria-label="list of calendars">
            <thead><tr><th scope="col"></th><th scope="col">Name</th></tr></thead>
            <tbody>
        <?php
        foreach ($calendars as $i => $cal) {
            echo '<tr><td><a href="/bookingschedulingedit/'.$id_space.'/'.$i.'"><button type="button" class="btn btn-primary">Edit</button></a></td><td>'. $cal.'</td></tr>';
        }
                //Configuration::getLogger()->debug('????? DEBUG OSALLOU ???????', ['map' => $mareas, 'calendars' => $bklist]);
        ?>
            </tbody>
        </table>
    </div>

    <?php foreach ($areas as $area) { ?>

    <div class="table-responsive mb-3">
        <table class="table" aria-label="list of calendars">
            <thead><tr><th scope="col"><?php echo $area['name'] ?></th><th scope="col"><?php echo $area['calendar'] ?></th></tr></thead>
            <tbody>
                <?php foreach($area['resources'] as $res) { ?>
                    <tr><td><?php echo $res['name'] ?></td><td><?php echo $res['calendar'] ?></td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php } ?>

</div>
<?php endblock(); ?>
