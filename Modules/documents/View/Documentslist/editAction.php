<?php include 'Modules/documents/View/layout.php' ?>


<?php startblock('content') ?>
<div class="container"> 
    
    <div class="pm-form" >
        <?php echo $formHtml ?>
    </div>


    <script>
        //id=\"form_blk_$name\"
        let visibility = document.getElementById('visibility');
        let client = document.getElementById('form_blk_id_ref_client');
        let user = document.getElementById('form_blk_id_ref_user');
        if(client && user) {
            if(visibility && visibility.value != <?php echo Document::$VISIBILITY_CLIENT ?>){
                client.style.display = "none";
            }
            if(visibility && visibility.value != <?php echo Document::$VISIBILITY_USER ?>){
                user.style.display = "none";
            }
            if(visibility) {
                visibility.onchange = (e) => {
                    if(e.target.value == <?php echo Document::$VISIBILITY_CLIENT ?>) {
                        client.style.display = "block";
                        user.style.display = "none";

                    } else if(e.target.value == <?php echo Document::$VISIBILITY_USER ?>) {
                        client.style.display = "none";
                        user.style.display = "block";
                    } else {
                        client.style.display = "none";
                        user.style.display = "none";  
                    }
                };
            }
        }

    </script>

</div>
<?php endblock(); ?>