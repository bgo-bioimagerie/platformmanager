<div  class="row" style="margin-bottom: 15px">
    <form name="projectTab" method="get" action="projectedittab/"><br>
    <div class="offset-3 col-6 text-center">
        <div class="btn-group" data-toggle="buttons">
            <button class="btn btn-outline-dark <?php if ($headerInfo["curentTab"] == "sheet") {
                echo "active";
            } ?>" type="button" onclick="location.href = '/servicesprojectsheet/<?php echo $id_space."/".$headerInfo["projectId"] ?>'"><?php echo ServicesTranslator::Sheet($lang) ?></button> 
            <button class="btn btn-outline-dark <?php if ($headerInfo["curentTab"] == "followup") {
                echo "active";
            } ?>" type="button" onclick="location.href = '/servicesprojectfollowup/<?php echo $id_space."/".$headerInfo["projectId"] ?>'"><?php echo ServicesTranslator::FollowUp($lang) ?></button> 
            <button class="btn btn-outline-dark <?php if ($headerInfo["curentTab"] == "closing") {
                echo "active";
            } ?>" type="button" onclick="location.href = '/servicesprojectclosing/<?php echo $id_space."/".$headerInfo["projectId"] ?>'"><?php echo ServicesTranslator::Closing($lang) ?></button> 
            <button class="btn btn-outline-dark <?php if ($headerInfo["curentTab"] == "samplereturn") {
                echo "active";
            } ?>" type="button" onclick="location.href = '/servicesprojectsample/<?php echo $id_space."/".$headerInfo["projectId"] ?>'"><?php echo ServicesTranslator::SamplesStock($lang) ?></button>
            <button class="btn btn-outline-dark <?php if ($headerInfo["curentTab"] == "kanban") {
                echo "active";
            } ?>" type="button" onclick="location.href = '/servicesprojects/kanban/<?php echo $id_space."/".$headerInfo["projectId"] ?>'"><?php echo ServicesTranslator::KanbanBoard($lang) ?></button> 
            <button class="btn btn-outline-dark
                <?php if ($headerInfo["curentTab"] == "gantt") {
                    echo "active";
                } ?>"
                type="button"
                onclick="location.href = '/servicesprojectgantt/<?php echo $id_space."/0/".$headerInfo["personInCharge"]."/".$headerInfo["projectId"] ?>'"><?php echo ServicesTranslator::Gantt($lang) ?>
            </button> 
            <?php $_SESSION["id_project"] = $headerInfo["projectId"]; ?>
        </div>
    </div>
    </form>
</div>