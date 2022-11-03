<?php include_once 'Modules/catalog/View/publiclayout.php' ?>

<?php startblock('stylesheet') ?>
<link rel="stylesheet" type="text/css" href="externals/node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">

<script src="externals/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="externals/node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<?php endblock() ?>

<?php startblock('content') ?>
<div class="container">
<?php include('Modules/catalog/View/Catalogpublic/toolbar.php') ?>

    <script>
        $(document).ready(function () {
            var table = $('#antibodies').DataTable({
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


<div class="row" style="background-color: #ffffff;">

    <table aria-label="list of antibodies" id="antibodies" class="table table-bordered table-striped" cellspacing="0" width="100%">
        <thead>

            <tr>
                <th id="antibodies" class="text-center" colspan="3" style="width:50%; color:#337AB7;">Anticorps</th>
                <th id="tissues" class="text-center" colspan="3" style="width:50%; background-color: #eeffee; color:#337AB7;">Tissus</th>
            </tr>

            <tr>
                <th id="image" class="text-center" style="width:5%; color:#337AB7;"></th>
                <th id="number" class="text-center" style="width:5%; color:#337AB7;">No</th>
                <th id="name" class="text-center" style="width:5%; color:#337AB7;"><?php echo CatalogTranslator::Name($lang) ?></th>              
                <th id="species" class="text-center" style="width:5%; background-color: #eeffee; color:#337AB7;"><?php echo CatalogTranslator::Species($lang) ?></th>
                <th id="sample" class="text-center" style="width:5%; background-color: #eeffee; color:#337AB7;"><?php echo CatalogTranslator::Sample($lang) ?></th>
                <th id="status" class="text-center" style="width:5%; background-color: #eeffee; color:#337AB7;"><?php echo CatalogTranslator::Status($lang) ?></th>
            </tr>    

        </thead>

        <tbody>
            <?php foreach ($entries as $entry) : ?> 
                <tr>
                    <td headers="antibodies image" class="text-left">
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
                    <td headers="antibodies number" class="text-left"><?php echo $this->clean($entry ['no_h2p2']); ?></td>
                    <td headers="antibodies name" class="text-left"><?php echo $this->clean($entry ['nom']); ?></td> 
                    
                    <!-- Tissus -->

                    <td headers="tissues species" class="text-left" style="background-color: #eeffee;">
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

                    <td headers="tissues sample" class="text-left" style="background-color: #eeffee;"><?php
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

                    <td headers="tissues status" class="text-left" style="background-color: #eeffee;">
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
</div>

<?php endblock(); ?>