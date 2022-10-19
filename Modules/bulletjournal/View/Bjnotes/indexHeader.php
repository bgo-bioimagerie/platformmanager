<div class="col-12 col-10" id="pm-form">
    <div class="col-4 text-left">
        <div class="btn-group" role="group" aria-label="...">
            <button type="submit" class="btn btn-outline-dark" onclick="location.href = 'bjnotesmonthbefore/<?php echo $idSpace ?>/<?php echo $year ?>/<?php echo $month ?>'"> &lt; </button>
            <button type="submit" class="btn btn-outline-dark" onclick="location.href = 'bjnotesmonthafter/<?php echo $idSpace ?>/<?php echo $year ?>/<?php echo $month ?>'"> > </button>
            <button type="submit" class="btn btn-outline-dark" onclick="location.href = 'bjnotes/<?php echo $idSpace ?>/0/0'"><?php echo BulletjournalTranslator::ThisMonth($lang) ?></button>
        </div>
    </div>  

    <div class="col-2 text-left">
        <p>
            <strong>
                <?php echo date("F Y", mktime(0, 0, 0, $month, 1, $year)) ?>
            </strong>
        </p>
    </div>
</div>
