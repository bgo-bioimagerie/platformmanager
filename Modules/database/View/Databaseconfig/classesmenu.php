<div class="col-md-2 col-xs-12" id="pm-form" style="min-height: 2000px;">

    <!-- <h3><?php echo DatabaseTranslator::Classes($lang) ?></h3> -->

    <div style="text-align: center;">
        <button onclick="location.href = 'databaseconfigclasses/<?php echo $id_space ?>/<?php echo $id_database ?>/0'" class="btn btn-primary btn-block">
            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
            <?php echo DatabaseTranslator::NewClass($lang) ?>
        </button>
    </div>

    <div style="text-align: center; height: 12px;"></div>
        <?php
        foreach ($classes as $class) {
            ?>
            <button type="button" onclick="location.href = 'databaseconfigclasses/<?php echo $id_space . "/" . $id_database . "/" . $class["id"] ?>'" class="btn btn-default btn-block"><?php echo $class["print_name"] ?></button>
            <?php
        }
        ?>
    </div>
</div>  
