<?php include 'Modules/catalog/View/publiclayout.php' ?>

<?php include('Modules/catalog/View/Catalogpublic/toolbar.php') ?>


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
                <th class="text-center" colspan="3" style="width:50%; color:#337AB7;">Anticorps</th>
                <th class="text-center" colspan="3" style="width:50%; background-color: #eeffee; color:#337AB7;">Tissus</th>
            </tr>

            <tr>
                <th class="text-center" style="width:5%; color:#337AB7;"></th>
                <th class="text-center" style="width:5%; color:#337AB7;">No</th>
                <th class="text-center" style="width:5%; color:#337AB7;"><?php echo CatalogTranslator::Name($lang) ?></th>              
                <th class="text-center" style="width:5%; background-color: #eeffee; color:#337AB7;"><?php echo CatalogTranslator::Species($lang) ?></th>
                <th class="text-center" style="width:5%; background-color: #eeffee; color:#337AB7;"><?php echo CatalogTranslator::Sample($lang) ?></th>
                <th class="text-center" style="width:5%; background-color: #eeffee; color:#337AB7;"><?php echo CatalogTranslator::Status($lang) ?></th>
            </tr>    

        </thead>

        <tbody>
            <?php foreach ($entries as $entry) : ?> 
                <tr>
                    <td width="10%" class="text-left">
                        <?php
                        $imageFile = "data/antibodies/" . $entry["image_url"];
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
                        ?>
                    </td>
                    <td width="10%" class="text-left"><?php echo $this->clean($entry ['no_h2p2']); ?></td>
                    <td width="10%" class="text-left"><?php echo $this->clean($entry ['nom']); ?></td> 
                    
                    <!-- Tissus -->

                    <td width="10%" class="text-left" style="background-color: #eeffee;">
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

                    <td width="10%" class="text-left" style="background-color: #eeffee;"><?php
                        $tissus = $entry ['tissus'];
                        $val = "";
                        for ($i = 0; $i < count($tissus); ++$i) {
                            $val = $val . "<p>"
                                    . $tissus[$i]['prelevement']
                                    . "</p>";
                        }
                        echo $val;
                        ?>
                    </td>

                    <td width="10%;" class="text-left" style="background-color: #eeffee;">
                        <?php
                        $tissus = $entry ['tissus'];
                        $val = "";
                        for ($i = 0; $i < count($tissus); ++$i) {

                            $statusTxt = "";
                            $background = "#ffffff";
                            foreach ($status as $stat) {
                                if ($tissus[$i]['status'] == $stat["id"]) {
                                    $statusTxt = $stat['nom'];
                                    $background = $stat["color"];
                                }
                            }
                            $val = $val . "<p style=\"background-color: #" . $background . "\">"
                                    . $statusTxt
                                    . "</p>";
                        }
                        echo $val;
                        ?>
                    </td>

                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
</div>
