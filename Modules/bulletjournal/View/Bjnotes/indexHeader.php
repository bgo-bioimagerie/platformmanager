<div class="col-xs-12" id="pm-form">
    <div class="col-md-4 text-left">
        <div class="btn-group" role="group" aria-label="...">
            <button type="submit" class="btn btn-default" onclick="location.href = 'bjnotesmonthbefore/<?php echo $id_space ?>/<?php echo $year ?>/<?php echo $month ?>'"> &lt; </button>
            <button type="submit" class="btn btn-default" onclick="location.href = 'bjnotesmonthafter/<?php echo $id_space ?>/<?php echo $year ?>/<?php echo $month ?>'"> > </button>
            <button type="submit" class="btn btn-default" onclick="location.href = 'bjnotes/<?php echo $id_space ?>/0/0'"><?php echo BulletjournalTranslator::ThisMonth($lang) ?></button>
        </div>
    </div>  

    <div class="col-md-2 text-left">
        <p>
            <b>
                <?php echo date("F Y", mktime(0, 0, 0, $month, 1, $year)) ?>
            </b>
        </p>
    </div>
</div>
