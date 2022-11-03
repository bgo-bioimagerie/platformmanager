<div>
    <form name="projectTab" method="get" action="resourcesedittab/"><br>
    <div class="offset-3 col-6 text-center">
        <div class="btn-group" data-toggle="buttons">
            <button class="btn btn-outline-dark <?php if ($headerInfo["curentTab"] == "info") {
                echo "active";
            } ?>" type="button" onclick="location.href = 'resourcesedit/<?php echo $id_space."/".$headerInfo["resourceId"] ?>'"><?php echo ResourcesTranslator::Infos($lang) ?></button> 
            <button class="btn btn-outline-dark <?php if ($headerInfo["curentTab"] == "resps") {
                echo "active";
            } ?>" type="button" onclick="location.href = 'resourcesresp/<?php echo $id_space."/".$headerInfo["resourceId"] ?>'"><?php echo CoreTranslator::Responsible($lang) ?></button> 
            <button class="btn btn-outline-dark <?php if ($headerInfo["curentTab"] == "events") {
                echo "active";
            } ?>" type="button" onclick="location.href = 'resourcesevents/<?php echo $id_space."/".$headerInfo["resourceId"] ?>'"><?php echo ResourcesTranslator::Events($lang) ?></button> 
            <?php $_SESSION["id_resource"] = $headerInfo["resourceId"]; ?>
        </div>
    </div>
    </form>
</div>