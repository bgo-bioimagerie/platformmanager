<?php ?>

<script>
    $(document).ready(function () {

        $("#hider").hide();
        $("#entriesbuttonclose").click(function () {
            $("#hider").hide();
            $('#entriespopup_box').hide();
        });

        $("#deletebookingbutton").click(function () {

            $("#hider").fadeIn("slow");
            $('#entriespopup_box').fadeIn("slow");
        })
        ;

    });
</script>            