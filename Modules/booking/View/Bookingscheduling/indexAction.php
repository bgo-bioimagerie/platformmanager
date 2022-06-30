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
            <thead><tr><th scope="col"><?php echo $area['name'] ?></th><th scope="col">
                <select id="area<?php echo $area['id']?>" onchange="setAreaCalendar(<?php echo $area['id'] ?>, this.value)" data-cal="<?php echo $area['calendar'] ?>" class="form-select" value="<?php echo $area['calendar'] ?>">
                    <?php foreach ($calendars as $i => $c) { ?>
                        <option value="<?php echo $i ?>"><?php echo $c ?></option>
                    <?php } ?>
                </select>          
            </th></tr></thead>
            <tbody>
                <?php foreach($area['resources'] as $res) { ?>
                    <tr><td><?php echo $res['name'] ?></td>
                    <td>
                    <select id="resource<?php echo $area['id']?>" onchange="setResourceCalendar(<?php echo $res['id'] ?>, this.value)" data-cal="<?php echo $res['calendar'] ?>" class="form-select <?php echo "area".$area['id'] ?>" value="<?php echo $res['calendar'] ?>">
                        <?php foreach ($rescalendars as $i => $c) { ?>
                            <option value="<?php echo $i ?>"><?php echo $c ?></option>
                        <?php } ?>
                    </select>
                    </td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php } ?>

</div>
<script>
    function setResourceCalendar(id, cal) {
        console.log('TODO set resource calendar', id, cal)
    }

    function setAreaCalendar(id, cal) {
        console.log('TODO set area calendar', id, cal)
    }

</script>
<?php endblock(); ?>
