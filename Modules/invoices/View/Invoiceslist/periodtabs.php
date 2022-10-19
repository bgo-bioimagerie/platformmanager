<div  style="border-bottom: 1px solid #e1e1e1; margin-bottom: 14px; padding-bottom: 7px;">
    <form name="projectTab" method="get" action="projectedittab/"><br>
    <div class="offset-3 col-6 text-center">
        <div class="btn-group" data-toggle="buttons">
            <button class="btn btn-outline-dark <?php if ($headerInfo["curentTab"] == "sheet") {
                echo "active";
            } ?>" type="button" onclick="location.href = 'servicesprojectsheet/<?php echo $idSpace."/".$headerInfo["projectId"] ?>'"><?php echo ServicesTranslator::Sheet($lang) ?></button> 
            <button class="btn btn-outline-dark <?php if ($headerInfo["curentTab"] == "followup") {
                echo "active";
            } ?>" type="button" onclick="location.href = 'servicesprojectfollowup/<?php echo $idSpace."/".$headerInfo["projectId"] ?>'"><?php echo ServicesTranslator::FollowUp($lang) ?></button> 
            <?php $_SESSION["id_project"] = $headerInfo["projectId"]; ?>
        </div>
    </div>
    </form>
</div>