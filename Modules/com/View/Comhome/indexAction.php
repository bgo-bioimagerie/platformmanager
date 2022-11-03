<?php include 'Modules/com/View/layout.php' ?>


<?php startblock('content') ?>
<div class="row">

    <div class="col-12"> 
        <?php echo $message ?>    
    </div>
    
    <div class="col-12"> 
        <?php if (count($news) > 0 && count($tweets) == 0) { ?>   
        <div class="col-12"> 
            <?php include("Modules/com/View/Comhome/news.php"); ?>
         </div> 
        <?php } elseif (count($tweets) > 0 && count($news) == 0) { ?>   
            <div class="col-12"> 
                <?php include("Modules/com/View/Comhome/tweets.php"); ?>  
            </div> 
        <?php } else { ?>
            <div class="col-6" style="margin-top:10px;"> 
                <?php include("Modules/com/View/Comhome/news.php"); ?>  
            </div> 
            <div class="col-6"> 
                <?php include("Modules/com/View/Comhome/tweets.php"); ?>  
            </div> 
        <?php } ?>
    </div>
    
</div>
<?php endblock(); ?>
