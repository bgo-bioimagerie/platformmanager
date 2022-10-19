<script>
    $(document).ready(function () {

<?php
foreach ($notes as $note) {
    ?>
            $("#migratetask_<?php echo $note["id"] ?>").click(function () {
                alert("<?php echo BulletjournalTranslator::migrate_task($lang) . " " . $note["name"] . " ?" ?>");
                
                $.post(
                    'bjmigratetask/<?php echo $idSpace . "/" .$note["id"] ?>',
                    {},
                    function (data) {
                        $('#tableline_<?php echo $note["id"] ?>').remove();
                    },
                    'json'
                    );
            });
    <?php
}
?>

});
</script>

