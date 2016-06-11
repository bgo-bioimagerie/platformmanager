<div class="col-xs-12">
    <form name="projectTab" method="get" action="resourcesedittab/"><br>
    <div class="col-sm-offset-3 col-sm-6 text-center">
        <div class="btn-group" data-toggle="buttons">
            <button class="btn btn-default <?php if($headerInfo["curentTab"] == "info"){echo "active";} ?>" type="button" onclick="location.href = 'resourcesedit/<?php echo $headerInfo["resourceId"] ?>'"><?php echo ResourcesTranslator::Infos($lang) ?></button> 
            <button class="btn btn-default <?php if($headerInfo["curentTab"] == "events"){echo "active";} ?>" type="button" onclick="location.href = 'resourcesevents/<?php echo $headerInfo["resourceId"] ?>'"><?php echo ResourcesTranslator::Events($lang) ?></button> 
            <?php $_SESSION["id_resource"] = $headerInfo["resourceId"]; ?>
        </div>
    </div>
    </form>
</div>