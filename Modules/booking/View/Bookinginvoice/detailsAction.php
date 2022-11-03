<?php include_once 'Modules/invoices/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-table">
    <?php echo $tableHtml ?>
</div>
<div class="table-responsive">
    <table aria-label="list of invoiced bookings" class="table table-sm">
        <thead>
            <tr>
                <th scope="col">Id</th>
                <th scope="col"><?php echo BookingTranslator::Fromdate($lang) ?></th>
                <th scope="col"><?php echo BookingTranslator::ToDate($lang) ?></th>
                <th scope="col"><?php echo BookingTranslator::Resource($lang) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($entries as $entry) { ?>
            <tr>
                <td><a href="bookingeditreservation/<?php echo $id_space ?>/r_<?php echo $entry['id'] ?>"><?php echo $entry['id'] ?></a></td>
                <td><?php echo CoreTranslator::dateFromEn(date('Y-m-d', $entry['start_time']), $lang).' '.date('h:i', $entry['start_time']) ?></td>
                <td><?php echo CoreTranslator::dateFromEn(date('Y-m-d', $entry['end_time']), $lang).' '.date('h:i', $entry['end_time']) ?></td>
                <td><?php echo $resources[$entry['resource_id']] ?></td>
            </tr>
        <?php } ?>
        </tobody>
    </table>
</div>
<?php endblock(); ?>
