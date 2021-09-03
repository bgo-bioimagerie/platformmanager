<script>
    <?php include 'Modules/com/Api/ComnewsApi.php'; ?>

    $(document).ready(function() {
        $("#hider").fadeIn("slow");
        $('#entriespopup_box').fadeIn("slow");
        $("#entriesbuttonclose").click(function() {
            $("#hider").hide();
            $('#entriespopup_box').hide();
        });
        showEditEntryForm(<?php echo $id_space ?>);

        /**
         * Gets news from ComnewsApi then calls displayPopup()
         * 
         * @param string id_space
         * @param string id_resource 
         * 
         */
        function showEditEntryForm(id_space) {
            // get com infos
            $.post('comgetnews/' + id_space)
                .done((response) => {
                    let data = JSON.parse(response);
                    let newsList = new Array();
                    data.forEach((elem) => {
                        newsList.push({
                            "title": elem.title,
                            "content": elem.content,
                            "media": elem.media
                        })
                    });
                    fillPopup(newsList);
                });
        }

        /**
         * Displays a popup window #entriespopup_box
         * to display news
         * 
         * @param array newsList
         * 
         */
        function fillPopup(newsList) {         
            newsList.forEach((news) => {
                if (news.media && news.media != null) {
                    // set image
                    let img = document.createElement('img');
                    img.setAttribute("src", news.media);
                    img.setAttribute("style", "max-width:320px");
                    $("#content_section").append(img);
                    $("#content_section").append("<br/>");
                }            
                $("#content_section").append("<b>" + news.title + "</b>");
                $("#content_section").append(news.content);
                $("#content_section").append("<br/>");
            });
        }
    });
</script>