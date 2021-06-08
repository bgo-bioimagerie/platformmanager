<?php include 'Modules/core/View/layout.php' ?>

<?php include('Modules/catalog/View/Catalogview/toolbar.php') ?>


<head>

    <link rel="stylesheet" type="text/css" href="externals/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="externals/dataTables/dataTables.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="externals/dataTables/fixedColumns.bootstrap.min.css">

    <script src="externals/dataTables/jquery-1.12.3.js"></script>
    <script src="externals/dataTables/jquery.dataTables.min.js"></script>
    <script src="externals/dataTables/dataTables.bootstrap.min.js"></script>
    <script src="externals/dataTables/dataTables.fixedColumns.min.js"></script>

    <script>
        $(document).ready(function () {
            var table = $('#example').DataTable({
                scrollY: "700px",
                scrollX: true,
                scrollCollapse: true,
                paging: false,
                fixedColumns: {
                    leftColumns: 0
                }
            });
        });
    </script>

</head>


<div class="col-md-12" style="height:15px; background-color: #ffffff;">

</div>

<div class="col-md-12" style="background-color: #ffffff;">

    <table id="example" class="table table-bordered table-striped" cellspacing="0" width="100%">
        <thead>

            <tr>
                <th class="text-center" colspan="2" style="width:50%; color:#337AB7;">Anticorps</th>
                <th class="text-center" colspan="3" style="width:50%; background-color: #eeffee; color:#337AB7;">Tissus</th>
            </tr>

            <tr>
                <th class="text-center" style="width:5%; color:#337AB7;">No</th>
                <th class="text-center" style="width:5%; color:#337AB7;"><?php echo CatalogTranslator::Name($lang) ?></th>              
                <th class="text-center" style="width:5%; background-color: #eeffee; color:#337AB7;"><?php echo CatalogTranslator::Spices($lang) ?></th>
                <th class="text-center" style="width:5%; background-color: #eeffee; color:#337AB7;"><?php echo CatalogTranslator::Sample($lang) ?></th>
                <th class="text-center" style="width:5%; background-color: #eeffee; color:#337AB7;"><?php echo CatalogTranslator::Image($lang) ?></th>
            
            </tr>    

        </thead>

        <tbody>
            <?php foreach ($entries as $entry) : ?> 
                <tr>
                    <td  class="text-left"><?php echo $this->clean($entry ['no_h2p2']); ?></td>
                    <td  class="text-left"><?php echo $this->clean($entry ['nom']); ?></td> 

                    <!-- Tissus -->

                    <td  class="text-left" style="background-color: #eeffee;">
                        <?php
                        $tissus = $entry ['tissus'];
                        $val = "";
                        for ($i = 0; $i < count($tissus); ++$i) {
                            $val = $val . "<p>" . $tissus[$i]['espece']
                                    . "</p>";
                        }
                        echo $val;
                        ?>
                    </td>

                    <td  class="text-left" style="background-color: #eeffee;"><?php
                        $tissus = $entry ['tissus'];
                        $val = "";
                        for ($i = 0; $i < count($tissus); ++$i) {
                            $val = $val . "<p>"
                                    . $tissus[$i]['prelevement']
                                    . "</p>";
                        }
                        echo $val;
                        ?>
                    </td >
                    <td  class="text-left" style="background-color: #eeffee;">
                        <?php
                        $tissus = $entry ['tissus'];
                        $val = "";
                        for ($i = 0; $i < count($tissus); ++$i) {
                            $imageFile = "data/antibodies/" . $tissus[$i]["image_url"];
                            if (!file_exists($imageFile) || is_dir($imageFile)) {
                                ?>
                                <div style="height:25px;"></div>
                                <?php
                            } else {
                                list($width, $height, $type, $attr) = getimagesize($imageFile);
                                ?>
                                <a href="<?php echo $imageFile ?>" itemprop="contentUrl" data-size="<?php echo $width ?>x<?php echo $height ?>">
                                    <img src="<?php echo $imageFile ?>" itemprop="thumbnail" alt="photo" width="25" height="25"/>
                                </a>
                                <?php
                            }
                        }
                        ?>
                    </td>


                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
</div>
