<?php include 'Modules/com/View/layout.php' ?>

<!-- body --> 
<?php startblock('content') ?>
<div class="col-md-12">

    <div class="col-md-12 pm-table-short"> 
        <?php echo $message ?>    
    </div>
    <?php
    foreach ($news as $n) {
        ?>
        <div class="col-md-12 pm-table-short">
            <div class="col-md-12 text-center" >
                <h3 style="color:#555; font:times;font-weight: normal;"><?php echo $this->clean($n["title"]) ?></h3>
            </div>
            <?php
            if ($n["media"] != "") {
                ?>
                <div class="col-md-12 text-center">
                    <img src="<?php echo $n["media"] ?>" alt="media not found" height="400px"> 
                </div>
                <?php
            }
            ?>
            <div class="col-md-12" style="text-align: justify;">
                <p><?php echo $n["content"] ?></p>
            </div>
        </div>
        <?php
    }
    ?> 
</div>
<?php
endblock();
