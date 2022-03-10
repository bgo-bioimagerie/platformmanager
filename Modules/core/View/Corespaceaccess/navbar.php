<div >
    <div  style="height: 50px; padding-top: 15px; background-color:<?php echo $space["color"] ?>; border-bottom: 1px solid #fff;">
        <a  style="background-color:<?php echo $space["color"] ?>; color: #fff;" href=""> <?php echo CoreTranslator::Users($lang) ?> 
            <span style="color: #fff; font-size:16px; float:right;" class=" hidden-xs showopacity bi-person-fill"></span>
        </a>
    </div>

    <div class=" pm-inline-div" style="background-color:<?php echo $space["color"] ?>;">
        <a style="color: #fff" id="menu-button" href="corespacependingusers/<?php echo $space["id"] ?>"><?php echo CoreTranslator::PendingUsers($lang) ?></a> 
    </div>

    <div  style="background-color:<?php echo $space["color"] ?>;">
        <br/>
    </div>
    <div class=" pm-inline-div" style="background-color:<?php echo $space["color"] ?>;">
        <a style="color: #fff" id="menu-button" href="corespaceaccessusers/<?php echo $space["id"] ?>"><?php echo CoreTranslator::Active_Users($lang) ?></a>
    </div>
    <div class=" pm-inline-div" style="background-color:<?php echo $space["color"] ?>;">
        <a style="color: #fff" id="menu-button" href="corespaceaccessusersinactifs/<?php echo $space["id"] ?>"><?php echo CoreTranslator::Inactive($lang) ?></a>
    </div>
    <div class=" pm-inline-div" style="background-color:<?php echo $space["color"] ?>;">
        <a style="color: #fff" id="menu-button" href="corespaceaccessuseradd/<?php echo $space["id"] ?>"><?php echo CoreTranslator::Add($lang) ?></a>
    </div>

        

</div>