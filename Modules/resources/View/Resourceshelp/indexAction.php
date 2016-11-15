<?php include 'Modules/resources/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>


<script src="externals/jcanvas.js"></script>
<script src="Framework/pm_graph.js"></script>
<link rel="stylesheet" type="text/css" href="Framework/pm_popup.css">

<script>

    $(document).ready(function () {
    var $myCanvas = $('#myCanvas');
    graph = new PMGraph($myCanvas);
    graph.addNode(1, "rectangle", new PMPoint(350, 250), new PMSize(200, 100), new PMNodeStyle("#337ab7", 0, "", 10), new PMText("Ressources", "times", 12, "#fff"), new PMNodeStyle("#337ab7", 0, "", 10));
    graph.addNode(2, "rectangle", new PMPoint(200, 52), new PMSize(200, 100), new PMNodeStyle("#fff", 2, "#337ab7", 10), new PMText("Domaine", "times", 12, "#337ab7"), new PMNodeStyle("#e1e1e1", 2, "#337ab7", 10));
    graph.addNode(3, "rectangle", new PMPoint(500, 52), new PMSize(200, 100), new PMNodeStyle("#fff", 2, "#337ab7", 10), new PMText("Catégorie", "times", 12, "#337ab7"), new PMNodeStyle("#e1e1e1", 2, "#337ab7", 10));
    graph.addArc(1, 1, "center", 2, "bottom", new PMArcStyle(2, "#337ab7", true));
    graph.addArc(1, 1, "center", 3, "bottom", new PMArcStyle(2, "#337ab7", true));
    // responsable
    graph.addNode(4, "rectangle", new PMPoint(600, 200), new PMSize(200, 100), new PMNodeStyle("#fff", 2, "#337ab7", 10), new PMText("Responsable", "times", 12, "#337ab7"), new PMNodeStyle("#e1e1e1", 2, "#337ab7", 10));
    graph.addArc(1, 1, "center", 4, "left", new PMArcStyle(2, "#337ab7", true));
    // visa
    graph.addNode(5, "rectangle", new PMPoint(600, 350), new PMSize(200, 100), new PMNodeStyle("#fff", 2, "#337ab7", 10), new PMText("Visa", "times", 12, "#337ab7"), new PMNodeStyle("#e1e1e1", 2, "#337ab7", 10));
    graph.addArc(1, 1, "center", 5, "left", new PMArcStyle(2, "#337ab7", true));
    // evenement
    graph.addNode(6, "rectangle", new PMPoint(102, 200), new PMSize(200, 100), new PMNodeStyle("#fff", 2, "#337ab7", 10), new PMText("Evenements", "times", 12, "#337ab7"), new PMNodeStyle("#e1e1e1", 2, "#337ab7", 10));
    graph.addArc(1, 1, "center", 6, "right", new PMArcStyle(2, "#337ab7", true));
    // etat
    graph.addNode(7, "rectangle", new PMPoint(102, 350), new PMSize(200, 100), new PMNodeStyle("#fff", 2, "#337ab7", 10), new PMText("Etats", "times", 12, "#337ab7"), new PMNodeStyle("#e1e1e1", 2, "#337ab7", 10));
    graph.addArc(1, 1, "center", 7, "right", new PMArcStyle(2, "#337ab7", true));
    graph.render();
    });</script>
<div class="col-md-10 " id="pm-table">  

    <h3>Resources help</h3>
    <div class="text-center">  
        <canvas id="myCanvas" width="800" height="600" style="border: 0px solid #e1e1e1;">
            <p>This is fallback content for users of assistive technologies or of browsers that don't have full support for the Canvas API.</p>
        </canvas>
    </div>
</div>

<?php
require_once "Framework/Popup.php";
$PopupObject = new Popup();
$PopupObject->addWindow("button_2", file_get_contents("Modules/resources/View/Resourceshelp/areaHelp.php") );
$PopupObject->addWindow("button_3", file_get_contents("Modules/resources/View/Resourceshelp/categoryHelp.php") );
$PopupObject->addWindow("button_4", file_get_contents("Modules/resources/View/Resourceshelp/responsibleHelp.php") );
$PopupObject->addWindow("button_5", file_get_contents("Modules/resources/View/Resourceshelp/visaHelp.php") );
$PopupObject->addWindow("button_6", file_get_contents("Modules/resources/View/Resourceshelp/eventsHelp.php") );
$PopupObject->addWindow("button_7", file_get_contents("Modules/resources/View/Resourceshelp/statesHelp.php") );
$PopupObject->render(true);
?>    


<?php
endblock();
