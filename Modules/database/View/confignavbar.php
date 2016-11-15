<div class="col-md-2 col-xs-12" id="pm-form" style="min-height: 2000px;">

    <!-- <h3><?php echo DatabaseTranslator::database($lang) ?></h3> -->
    <div style="text-align: center;">
        <button onclick="location.href = 'databaseconfiginfo/<?php echo $id_space ?>/0'" class="btn btn-primary btn-block">
            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
            <?php echo DatabaseTranslator::NewDatabase($lang) ?>
        </button>
    </div>

    <div style="text-align: center; height: 12px;"></div>
    <?php
    foreach ($databases as $database) {
        ?>
        <button type="button" onclick="location.href = 'databaseconfiginfo/<?php echo $id_space . "/" . $database["id"] ?>'" class="btn btn-default btn-block"><?php echo $database["print_name"] ?></button>
        <?php
    }
    ?>
</div>    
