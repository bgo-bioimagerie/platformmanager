<?php

    
    $data = ' [{
    name: "Sprint 0",
    desc: "Analysis",
    values: [{
    from: "/Date(1320192000000)/",
    to: "/Date(1322401600000)/",
    label: "Requirement Gathering",
    customClass: "ganttRed"
    }]
    },{
    desc: "Scoping",
    values: [{
    from: "/Date(1322611200000)/",
    to: "/Date(1323302400000)/",
    label: "Scoping",
    customClass: "ganttRed"
    }]
    },{
    name: "Sprint 1",
    desc: "Development",
    values: [{
    from: "/Date(1323802400000)/",
    to: "/Date(1325685200000)/",
    label: "Development",
    customClass: "ganttGreen"
    }]
    },{
    name: " ",
    desc: "Showcasing",
    values: [{
    from: "/Date(1325685200000)/",
    to: "/Date(1325695200000)/",
    label: "Showcasing",
    customClass: "ganttBlue"
    }]
    },{
    name: "Sprint 2",
    desc: "Development",
    values: [{
    from: "/Date(1326785200000)/",
    to: "/Date(1325785200000)/",
    label: "Development",
    customClass: "ganttGreen"
    }]
    },{
    desc: "Showcasing",
    values: [{
    from: "/Date(1328785200000)/",
    to: "/Date(1328905200000)/",
    label: "Showcasing",
    customClass: "ganttBlue"
    }]
    },{
    name: "Release Stage",
    desc: "Training",
    values: [{
    from: "/Date(1330011200000)/",
    to: "/Date(1336611200000)/",
    label: "Training",
    customClass: "ganttOrange"
    }]
    },{
    desc: "Deployment",
    values: [{
    from: "/Date(1336611200000)/",
    to: "/Date(1338711200000)/",
    label: "Deployment",
    customClass: "ganttOrange"
    }]
    },{
    desc: "Warranty Period",
    values: [{
    from: "/Date(1336611200000)/",
    to: "/Date(1349711200000)/",
    label: "Warranty Period",
    customClass: "ganttOrange"
    }]
    }]';
    
?>

<!doctype html>

<head>
<title>jQuery.Gantt</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge;chrome=IE8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="http://maxcdn.bootstrapcdn.com/bootstrap/latest/css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="css/style.css" type="text/css" rel="stylesheet">
<link href="http://cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.min.css" rel="stylesheet" type="text/css">
<style type="text/css">
body {
    font-family: Helvetica, Arial, sans-serif;
    font-size: 13px;
padding: 0 0 50px 0;
}
h1 {
margin: 40px 0 20px 0;
}
h2 {
    font-size: 1.5em;
    padding-bottom: 3px;
    border-bottom: 1px solid #DDD;
    margin-top: 50px;
    margin-bottom: 25px;
}
table th:first-child {
width: 150px;
}
</style>
</head>

<body>

<div class="gantt"></div>

</body>

<script src="js/jquery.min.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="js/jquery.fn.gantt.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/latest/js/bootstrap.min.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.min.js"></script>
<script>
$(".gantt").gantt({
    source: <?php echo $data ?>,
    scale: "weeks",
    minScale: "weeks",
    maxScale: "months",
    onItemClick: function(data) {
        alert("Item clicked - show some details");
    },
    onAddClick: function(dt, rowId) {
        alert("Empty space clicked - add an item!");
    },
    onRender: function() {
    console.log("chart rendered");
   }
});
</script>
</html>
<?php
    
    

