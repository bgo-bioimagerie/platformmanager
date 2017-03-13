<?php include 'Modules/core/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<head>

    <link rel="stylesheet" type="text/css" href="externals/dataTables/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="externals/dataTables/dataTables.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="externals/dataTables/fixedColumns.bootstrap.min.css">

    <script src="externals/dataTables/jquery-1.12.3.js"></script>
    <script src="externals/dataTables/jquery.dataTables.min.js"></script>
    <script src="externals/dataTables/dataTables.bootstrap.min.js"></script>
    <script src="externals/dataTables/dataTables.fixedColumns.min.js"></script>

    <script>
        $(document).ready(function () {
            var table = $('#example').DataTable({
                scrollY: "500px",
                scrollX: true,
                scrollCollapse: true,
                paging: false,
                fixedColumns: {
                    leftColumns: 3
                }
            });
        });
    </script>
</head>

<div class="col-md-12 pm-table">
    <div class="col-md-12">
            
            <div class="col-md-10">
                <div class="dropdown">
                    <button id="dLabel" type="button" class="btn  btn-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo 'Menu ' . AntibodiesTranslator::antibodies($lang) ?>
                    </button>
                    <div class="dropdown-menu col-md-12" aria-labelledby="dLabel" style="background-color: transparent; border: none; box-shadow:none;">
                        <?php
                        include 'Modules/antibodies/View/navbar.php';
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2">
                <button type="button" onclick="location.href = 'anticorpscsv/<?php echo $id_space ?>'" class="btn btn-primary"><?php echo AntibodiesTranslator::Export_as_csv($lang) ?></button>
            </div>
        </div>
        <div class="text-center">
        <div class="btn-group btn-group-sm">
            <button class="btn btn-default <?php if ($letter == "All") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/All';"><?php echo AntibodiesTranslator::All($lang) ?></button>
            <button class="btn btn-default <?php if ($letter == "A") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/A';">A</button>
            <button class="btn btn-default <?php if ($letter == "B") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/B';">B</button>
            <button class="btn btn-default <?php if ($letter == "C") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/C';">C</button>
            <button class="btn btn-default <?php if ($letter == "D") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/D';">D</button>
            <button class="btn btn-default <?php if ($letter == "E") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/E';">E</button>
            <button class="btn btn-default <?php if ($letter == "F") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/F';">F</button>
            <button class="btn btn-default <?php if ($letter == "G") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/G';">G</button>
            <button class="btn btn-default <?php if ($letter == "H") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/H';">H</button>
            <button class="btn btn-default <?php if ($letter == "I") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/I';">I</button>
            <button class="btn btn-default <?php if ($letter == "J") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/J';">J</button>
            <button class="btn btn-default <?php if ($letter == "K") {
    echo "active";
} ?>" onclick="location.href = 'ecusers/<?php echo $id_space ?>/K';">K</button>
            <button class="btn btn-default <?php if ($letter == "L") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/L';">L</button>
            <button class="btn btn-default <?php if ($letter == "M") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/M';">M</button>
            <button class="btn btn-default <?php if ($letter == "N") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/N';">N</button>
            <button class="btn btn-default <?php if ($letter == "O") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/O';">O</button>
            <button class="btn btn-default <?php if ($letter == "P") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/P';">P</button>
            <button class="btn btn-default <?php if ($letter == "Q") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/Q';">Q</button>
            <button class="btn btn-default <?php if ($letter == "R") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/R';">R</button>
            <button class="btn btn-default <?php if ($letter == "S") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/S';">S</button>
            <button class="btn btn-default <?php if ($letter == "T") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/T';">T</button>
            <button class="btn btn-default <?php if ($letter == "U") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/U';">U</button>
            <button class="btn btn-default <?php if ($letter == "V") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/V';">V</button>
            <button class="btn btn-default <?php if ($letter == "W") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/W';">W</button>
            <button class="btn btn-default <?php if ($letter == "X") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/X';">X</button>
            <button class="btn btn-default <?php if ($letter == "Y") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/Y';">Y</button>
            <button class="btn btn-default <?php if ($letter == "Z") {
    echo "active";
} ?>" onclick="location.href = 'antibodies/<?php echo $id_space ?>/Z';">Z</button>
        </div>

    </div>
    <div class="col-md-12" style="height: 7px;">
    </div>
    
    <br/>
    <div class="col-md-12">
        <div class="page-header" style="margin-top: -20px;">
            <h1>
                Anticorps<br> <small></small>
            </h1>
        </div>
        

        <div class="col-xs-12">
            <table id="example" class="table table-striped table-bordered" style="font-size: 10px;" cellspacing="0" width="100%">
                <thead>	 
                    <tr>
                        <th class="text-center" colspan="10" style="color:#337AB7;">Anticorps</th>
                        <th class="text-center" colspan="2" style="background-color: #ffeeee; color:#337AB7;">Protocole</th>
                        <th class="text-center" colspan="6" style="background-color: #eeffee; color:#337AB7;">Tissus</th>
                        <th class="text-center" colspan="4" style="background-color: #eeeeff; color:#337AB7;">Propriétaire</th>
                    </tr>

                    <tr>			
                        <th></th>
                        <th class="text-center" style="color:#337AB7;">No</th> 
                        <th class="text-center" style="color:#337AB7;">Nom</th>
                        <th class="text-center" style="color:#337AB7;">St</th>
                        <th class="text-center" style="color:#337AB7;">Fournisseur</th>
                        <th class="text-center" style="color:#337AB7;">Source</th>
                        <th class="text-center" style="color:#337AB7;">Référence</th>
                        <th class="text-center" style="color:#337AB7;">Clone</th>
                        <th class="text-center" style="color:#337AB7;">lot</th>
                        <th class="text-center" style="color:#337AB7;">Isotype</th>

                        <th class="text-center" style="background-color: #ffeeee; color:#337AB7;">proto</th>
                        <th class="text-center" style="background-color: #ffeeee; color:#337AB7;">AcI dil</th>

                        <th class="text-center" style="background-color: #eeffee; color:#337AB7;">commentaire</th>
                        <th class="text-center" style="background-color: #eeffee; color:#337AB7;">espèce</th>
                        <th class="text-center" style="background-color: #eeffee; color:#337AB7;">organe</th>
                        <th class="text-center" style="background-color: #eeffee; color:#337AB7;">statut</th>
                        <th class="text-center" style="background-color: #eeffee; color:#337AB7;">ref. bloc</th>
                        <th class="text-center" style="background-color: #eeffee; color:#337AB7;">prélèvement</th>	

                        <th class="text-center" style="background-color: #eeeeff; color:#337AB7;">Nom</th>
                        <th class="text-center" style="background-color: #eeeeff; color:#337AB7;">disponibilité</th>
                        <th class="text-center" style="background-color: #eeeeff; color:#337AB7;">Date réception</th>
                        <th class="text-center" style="background-color: #eeeeff; color:#337AB7;">No Dossier</th>

                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($anticorpsArray as $anticorps) : ?> 

                        <tr>
                            <td width="10%" class="text-left">

                                <?php
                                foreach ($anticorps['tissus'] as $tissus) {
                                    $imageFile = "data/antibodies/" . $tissus["image_url"];
                                    $printImage = true;
                                    if (!file_exists($imageFile) || is_dir($imageFile)) {
                                        $printImage = false;
                                        //$imageFile = "Modules/antibodies/Theme/images_icon.png";
                                    } else {
                                        list($width, $height, $type, $attr) = getimagesize($imageFile);
                                    }
                                    if ($printImage) {
                                        ?>
                                        <a id="imgview_<?php echo $tissus["id"] ?>" >
                                            <img src="<?php echo $imageFile ?>" itemprop="thumbnail" alt="photo" width="25" height="25"/>
                                        </a>
                                        <script>
                                            $(document).ready(function () {
                                                $('#imgview_<?php echo $tissus["id"] ?>').on('click', function () {

                                                    var img = "<p><?php echo $tissus["image_url"] ?></p><img src='<?php echo $imageFile ?>' width='100%'  />";

                                                    $('#imagedivcontent').html(img);
                                                    $('#imagepopup_box').show();
                                                    $('#hider').show();
                                                });
                                            });
                                        </script>
                                        <?php
                                    }
                                }
                                ?>
                            </td>
                            <?php
                            $anticorpsId = $this->clean($anticorps['id']);
                            $isCatalogue = "";
                            if ($anticorps['export_catalog'] == 1) {
                                $isCatalogue = " (c)";
                            }
                            ?>

                            <td style="width:1em;" class="text-left"><a href="anticorpsedit/<?php echo $id_space ?>/<?php echo $anticorpsId ?>"><?php echo $this->clean($anticorps ['no_h2p2']); ?></a></td>
                            <td width="5%" class="text-left"><a href="anticorpsedit/<?php echo $id_space ?>/<?php echo $anticorpsId ?>"><?php echo $this->clean($anticorps ['nom']); ?></a></td>
                            <td width="5%" class="text-left"><?php echo $this->clean($anticorps ['stockage']); ?></td>
                            <td width="5%" class="text-left"><?php echo $this->clean($anticorps ['fournisseur']); ?></td>
                            <td width="5%" class="text-left"><?php echo $this->clean($anticorps ['source']); ?></td>
                            <td width="5%" class="text-left"><?php echo $this->clean($anticorps ['reference']); ?></td>
                            <td width="5%" class="text-left"><?php echo $this->clean($anticorps ['clone']); ?></td>
                            <td width="5%" class="text-left"><?php echo $this->clean($anticorps ['lot']); ?></td>
                            <td width="5%" class="text-left"><?php echo $this->clean($anticorps ['isotype']); ?></td>



                            <!--  PROTOCOLE -->
                            <td width="5%" class="text-left" style="background-color: #ffeeee;"><?php
                                $tissus = $anticorps ['tissus'];
                                $val = "";
                                for ($i = 0; $i < count($tissus); ++$i) {

                                    if ($tissus[$i]['ref_protocol'] == "0") {
                                        $val .= "<p>Manuel</p>";
                                    } else {
                                        $val .= "<p><a href=\"protocols/protoref/" . $anticorps ['id'] . "\">"
                                                . $tissus[$i]['ref_protocol'] . "</a></p>";
                                    }
                                }
                                echo $val;
                                ?></td>


                            <td width="5%" class="text-left" style="background-color: #ffeeee;"><?php
                                $tissus = $anticorps ['tissus'];
                                $val = "";
                                for ($i = 0; $i < count($tissus); ++$i) {
                                    $val = $val . "<p>"
                                            . $tissus[$i]['dilution']
                                            . "</p>";
                                }
                                echo $val;
                                ?></td>


                            <!-- TISSUS -->
                            <td width="5%" class="text-left" style="background-color: #eeffee;"><?php
                                $tissus = $anticorps ['tissus'];
                                $val = "";
                                for ($i = 0; $i < count($tissus); ++$i) {
                                    $val = $val . "<p>"
                                            . $tissus[$i]['comment']
                                            . "</p>";
                                }
                                echo $val;
                                ?></td>

                            <td width="5%" class="text-left" style="background-color: #eeffee;"><?php
                                $tissus = $anticorps ['tissus'];
                                $val = "";
                                for ($i = 0; $i < count($tissus); ++$i) {
                                    $val = $val . "<p>" . $tissus[$i]['espece']
                                            . "</p>";
                                }
                                echo $val;
                                ?></td>

                            <td width="5%" class="text-left" style="background-color: #eeffee;"><?php
                                $tissus = $anticorps ['tissus'];
                                $val = "";
                                for ($i = 0; $i < count($tissus); ++$i) {
                                    $val = $val . "<p>"
                                            . $tissus[$i]['organe']
                                            . "</p>";
                                }
                                echo $val;
                                ?></td>

                            <td width="5%" class="text-left" style="background-color: #eeffee;"><?php
                                $tissus = $anticorps ['tissus'];
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
                                ?></td>


                            <td width="5%" class="text-left" style="background-color: #eeffee;"><?php
                                $tissus = $anticorps ['tissus'];
                                $val = "";
                                for ($i = 0; $i < count($tissus); ++$i) {
                                    $val = $val . "<p>"
                                            . $tissus[$i]['ref_bloc']
                                            . "</p>";
                                }
                                echo $val;
                                ?></td>

                            <td width="5%" class="text-left" style="background-color: #eeffee;"><?php
                                $tissus = $anticorps ['tissus'];
                                $val = "";
                                for ($i = 0; $i < count($tissus); ++$i) {
                                    $val = $val . "<p>"
                                            . $tissus[$i]['prelevement']
                                            . "</p>";
                                }
                                echo $val;
                                ?></td>




                            <td width="5%" class="text-left" style="width:5em; background-color: #eeeeff;"><?php
                                $owner = $anticorps ['proprietaire'];
                                foreach ($owner as $ow) {
                                    $name = $ow['name'] . " " . $ow['firstname'];
                                    $dispo = $ow['disponible'];
                                    if ($dispo == 1) {
                                        $dispo = "disponible";
                                    } else if ($dispo == 2) {
                                        $dispo = "épuisé";
                                    } else if ($dispo == 3) {
                                        $dispo = "récupéré par équipe";
                                    }
                                    $date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
                                    $txt = $this->clean($name);

                                    if ($this->clean($dispo) == "épuisé") {
                                        echo '<p style="background-color:#ffaaaa; color:#fff">' . $txt . '</p>';
                                    } else {
                                        echo '<p>' . $txt . '</p>';
                                    }
                                }
                                ?>
                            </td>

                            <td width="5%" class="text-left" style="background-color: #eeeeff;"><?php
                                $owner = $anticorps ['proprietaire'];
                                foreach ($owner as $ow) {
                                    $dispo = $ow['disponible'];
                                    if ($dispo == 1) {
                                        $dispo = "disponible";
                                    } else if ($dispo == 2) {
                                        $dispo = "épuisé";
                                    } else if ($dispo == 3) {
                                        $dispo = "récupéré par équipe";
                                    }
                                    $date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
                                    $txt = $this->clean($dispo);

                                    if ($this->clean($dispo) == "épuisé") {
                                        echo '<p style="background-color:#ffaaaa; color:#fff">' . $txt . '</p>';
                                    } else {
                                        echo '<p>' . $txt . '</p>';
                                    }
                                }
                                ?>
                            </td>

                            <td width="5%" class="text-left" style="background-color: #eeeeff;"><?php
                                $owner = $anticorps ['proprietaire'];
                                foreach ($owner as $ow) {
                                    $name = $ow['name'] . " " . $ow['firstname'];
                                    $dispo = $ow['disponible'];
                                    if ($dispo == 1) {
                                        $dispo = "disponible";
                                    } else if ($dispo == 2) {
                                        $dispo = "épuisé";
                                    } else if ($dispo == 3) {
                                        $dispo = "récupéré par équipe";
                                    }
                                    $date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
                                    $txt = $this->clean($date_recept);

                                    if ($this->clean($dispo) == "épuisé") {
                                        echo '<p style="background-color:#ffaaaa; color:#fff">' . $txt . '</p>';
                                    } else {
                                        echo '<p>' . $txt . '</p>';
                                    }
                                }
                                ?>
                            </td>

                            <td width="5%" class="text-left" style="background-color: #eeeeff;"><?php
                                $owner = $anticorps ['proprietaire'];
                                foreach ($owner as $ow) {
                                    $name = $ow['name'] . " " . $ow['firstname'];
                                    $dispo = $ow['disponible'];
                                    if ($dispo == 1) {
                                        $dispo = "disponible";
                                    } else if ($dispo == 2) {
                                        $dispo = "épuisé";
                                    } else if ($dispo == 3) {
                                        $dispo = "récupéré par équipe";
                                    }
                                    $date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
                                    $txt = $this->clean($ow['no_dossier']);

                                    if ($this->clean($dispo) == "épuisé") {
                                        echo '<p style="background-color:#ffaaaa; color:#fff">' . $txt . '</p>';
                                    } else {
                                        echo '<p>' . $txt . '</p>';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>

            </table>
        </div>
    </div>
</div>


<!--  *************  -->
<!--  Popup windows  -->
<!--  *************  -->
<link rel="stylesheet" type="text/css" href="Framework/pm_popup.css">
<div id="hider" class="col-xs-12"></div> 
<div id="imagepopup_box" class="pm_popup_box_full" style="display: none;">
    <div class="col-md-1 col-md-offset-11" style="text-align: right;"><a id="tissusbuttonclose" class="glyphicon glyphicon-remove" style="cursor:pointer;"></a></div>
    <div id="imagedivcontent">

    </div>    
</div> 

<script>
    $(document).ready(function () {

        $("#hider").hide();
        $("#imagepopup_box").hide();

        $("#tissusbuttonclose").click(function () {
            $("#hider").hide();
            $('#imagepopup_box').hide();
        });

    });
</script>

<?php
endblock();
