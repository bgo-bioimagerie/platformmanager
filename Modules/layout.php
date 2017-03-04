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
<body style="background-color: #f1f1f1;">

    <?php startblock('navbar') ?>
    <?php endblock() ?>
    
    <?php startblock('spacenavbar') ?>
    <?php endblock() ?>
    
    <?php startblock('content') ?>
    <?php endblock() ?>
    
    <?php startblock('footer') ?>
    <?php endblock() ?>
</body>
</html>
