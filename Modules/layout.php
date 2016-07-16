<?php require_once 'Framework/ti.php' ?>
<html>
<head>
    <meta charset="UTF-8" />
    <base href="<?php echo $rootWeb ?>" >
    <title>
    <?php startblock('title') ?>
    <?php endblock() ?>
    </title>
        
    <?php startblock('stylesheet') ?>
    <link rel="stylesheet" href="externals/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="Modules/core/Theme/core.css">
    <?php endblock() ?>
            
</head>
<body>
    <?php startblock('menu') ?>
    <?php endblock() ?>
    <?php startblock('navbar') ?>
    <?php endblock() ?>
    
    <!-- <div class="col-xs-12" id="pm-content"/> -->
    <?php startblock('content') ?>
    <?php endblock() ?>
    <!-- </div> -->
</body>
</html>
