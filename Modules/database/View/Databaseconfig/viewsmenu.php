<div class="col-md-2 col-xs-12" id="pm-form" style="min-height: 2000px;">

    <!-- <h3><?php echo DatabaseTranslator::Classes($lang) ?></h3> -->

    <div style="text-align: center;">
        <button onclick="location.href = 'databaseconfigviews/<?php echo $id_space ?>/<?php echo $id_database ?>/0'" class="btn btn-primary btn-block">
            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
            <?php echo DatabaseTranslator::NewView($lang) ?>
        </button>
    </div>

    <div style="text-align: center; height: 12px;"></div>
        <?php
        foreach ($views as $view) {
            ?>
            <button type="button" onclick="location.href = 'databaseconfigviews/<?php echo $id_space . "/" . $id_database . "/" . $view["id"] ?>'" class="btn btn-default btn-block"><?php echo $view["name"] ?></button>
            <?php
        }
        ?>
    </div>
</div>  
