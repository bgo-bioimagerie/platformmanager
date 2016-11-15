<script>
$(document).ready(function(){   
    $('#formidsubmit').click(function (e) {
        e.preventDefault(); // on empêche le bouton d'envoyer le formulaire
        $.post(
                'validationurl', // Un script PHP que l'on va créer juste après
                $('#formid').serialize(),
                function (data) {
                    if ('error' in data){
                        alert("Error: " + data.error.msg);
                    }
                    else{
                        //alert(data.message);
                    }
                },
                'json'
                );

    });
});
</script>