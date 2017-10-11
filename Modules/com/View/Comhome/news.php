<?php
foreach ($news as $n) {
    ?>


    <div style="border-radius: 5px; border: 1px solid #ddd; background-color: #fff; padding-left: 0px; padding-right: 0px;">
        <img class="img-responsive" style="border-top-left-radius: 5px; border-top-right-radius: 5px;" src="<?php echo $n["media"] ?>" alt="media not found" width="100%"> 
        <div style="padding: 7px 7px 7px 7px;">
            <p style="font: bold 14px/18px Helvetica, Arial, sans-serif;">
                <b><?php echo $n["title"] ?></b>
                <?php echo $n["content"] ?>
            </p>
        </div>
    </div>


    <?php
}
?> 
