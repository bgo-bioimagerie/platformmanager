<div class="col-12" id="pm-form">
    <div class="col-md-4 text-left">
        <div class="btn-group" role="group" aria-label="...">
            <button type="submit" class="btn btn-outline-dark" onclick="location.href = 'bjmigrationsmonthbefore/<?php echo $id_space ?>/<?php echo $year ?>/<?php echo $month ?>'"> &lt; </button>
            <button type="submit" class="btn btn-outline-dark" onclick="location.href = 'bjmigrationsmonthafter/<?php echo $id_space ?>/<?php echo $year ?>/<?php echo $month ?>'"> > </button>
            <button type="submit" class="btn btn-outline-dark" onclick="location.href = 'bjmigrations/<?php echo $id_space ?>/0/0'"><?php echo BulletjournalTranslator::ThisMonth($lang) ?></button>
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
