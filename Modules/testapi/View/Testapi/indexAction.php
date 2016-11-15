<?php include 'Modules/testapi/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="container">
    <?php echo $formHtml ?>

</div> 

<script>
    /*
$(document).ready(function(){    
    $("#messages").hide();
    $('#testapiformsubmit').click(function (e) {
        e.preventDefault(); // on empêche le bouton d'envoyer le formulaire
        $.post(
                'apitestquery', // Un script PHP que l'on va créer juste après
                $('#testapiform').serialize(),
                function (data) {
                    if ('error' in data){
                        alert("Error: " + data.error.msg);
                    }
                    else{
                        alert(data.message);
                    }

                },
                'json'
                );

    });
});
*/
</script>


<?php
endblock();
