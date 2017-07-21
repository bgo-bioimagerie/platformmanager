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
        });
        
        $("#deletebookingperiodbutton").click(function () {

            $("#hider").fadeIn("slow");
            $('#entriesperiodpopup_box').fadeIn("slow");
        });
        $("#entriesperiodbuttonclose").click(function () {
            $("#hider").hide();
            $('#entriesperiodpopup_box').hide();
        });

    });
</script>            