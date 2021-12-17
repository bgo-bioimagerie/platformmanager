<?php include 'Modules/core/View/spacelayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<?php
    if ($showCom) {
?>
<!--  *************  -->
<!--  Popup window  -->
<!--  *************  -->

<?php include 'Modules/com/View/Comhome/comhomeScript.php';  ?>

<?php
    }
?>


<?php
if ($space['color'] == "") {
    $space['color'] = "#428bca";
}
?>

    <!-- display com popup -->

<div class="row">
<div  style="background-color: #fff; ">

    <div id="tiles" class="container" style="background-color: #fff;">

        <?php
        require_once 'Modules/com/Controller/ComtileController.php';
        $navController = new ComtileController(new Request(array(), false));
        echo $navController->indexAction($id_space);
        ?>

        <div class="page-header">
            <h2>
                <?php echo CoreTranslator::Tools($lang) ?>
                <br>
            </h2>
        </div>
        <div class="pm-tiles" >
            <div class="pm-tiles bs-glyphicons">
                <ul class="pm-tiles bs-glyphicons-list">
                    <?php
                    $configModel = new CoreConfig();
                    foreach ($spaceMenuItems as $item) {
                        ?>
                        <li style="background-color:<?php echo $item["color"]; ?>; <?php echo "; color: ".$item["txtcolor"]; ?>">
                            <a href="<?php echo $item["url"] . "/" . $id_space ?>">
                                <span class="pm-tiles glyphicon <?php echo $item["icon"] ?>" aria-hidden="true"></span>
                                <span style="<?php echo "color: ".$item["txtcolor"]; ?>" class="pm-tiles glyphicon-class"><?php echo $item["name"] ?></span>
                                <span v-if="notifs?.<?php echo strtolower(str_replace(' ', '', $item['name'])); ?>" class="label label-info">{{notifs?.<?php echo strtolower(str_replace(' ', '', $item['name'])); ?>}}</span>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                    <?php if($role > 1) { ?>
                        <li style="background-color:<?php echo $space['color'] ?>;">
                            <a href="<?php echo "coremail/" . $space["id"] ?>">
                                <span class="pm-tiles glyphicon glyphicon-bell" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class">Notifications</span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <?php
        if ($showAdmMenu) {
            ?>
            <div class="page-header">
                <h2>
                    <?php echo CoreTranslator::Admin($lang) ?>
                    <br>
                </h2>
            </div>
            <div class="pm-tiles" >
                <div class="pm-tiles bs-glyphicons">
                    <ul class="pm-tiles bs-glyphicons-list">
                        <li style="background-color:<?php echo $space['color'] ?>;">
                            <a href="<?php echo "spaceadminedit/" . $space["id"] ?>">
                                <span class="pm-tiles glyphicon glyphicon-cog" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class"><?php echo CoreTranslator::Space($lang) ?></span>
                            </a>
                        </li>
                        <li style="background-color:<?php echo $space['color'] ?>;">
                            <a href="<?php echo "spaceconfig/" . $space["id"] ?>">
                                <span class="pm-tiles glyphicon glyphicon-cog" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class"><?php echo CoreTranslator::Configuration($lang) ?></span>
                            </a>
                        </li>
                        <li style="background-color:<?php echo $space['color'] ?>;">
                            <a href="<?php echo "corespaceaccess/" . $space["id"] ?>">
                                <span class="pm-tiles glyphicon glyphicon-user" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class"><?php echo CoreTranslator::Users($lang) ?></span>
                                <span v-if="notifs?.spaceaccess" class="label label-info">{{notifs.spaceaccess}}</span>
                            </a>
                        </li> 

                        <li style="background-color:<?php echo $space['color'] ?>;">
                            <a href="<?php echo "corespacehistory/" . $space["id"] ?>">
                                <span class="pm-tiles glyphicon glyphicon-th-list" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class"><?php echo CoreTranslator::History($lang) ?></span>
                            </a>
                        </li>
                        <li style="background-color:<?php echo $space['color'] ?>;">
                            <a href="grafana">
                                <span class="pm-tiles glyphicon glyphicon-stats" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class"><?php echo CoreTranslator::GrafanaStats($lang) ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php
        }
        ?>
        <?php if($_SESSION['id_user'] > 0 && $role<CoreSpace::$MANAGER && $role > 0) { ?>
        <div class="page-header">
                <h2>
                    <?php echo CoreTranslator::RequestJoin(true, $lang)."?" ?>
                    <br>
                </h2>
        </div>
        <div>
            <a href="<?php echo "coretilesselfjoinspace/". $space["id"] ?>">
                <button type="button" class="btn btn-md btn-danger">
                    <?php echo CoreTranslator::RequestJoin(true, $lang) ?>
                </button>
            </a>
        </div>
        <?php } ?>


    </div> <!-- /container -->
</div>
</div>

<?php
$spaceModules = ['spaceaccess'];
foreach($spaceMenuItems as $item) {
    $spaceModules[] = $item['name'];
}

?>

<script>


var app = new Vue({
    el: '#tiles',
    data () {
        return {
            id_space: <?php echo $id_space ?>,
            logged: <?php if(isset($_SESSION['id_user']) && $_SESSION['id_user']) { echo "true"; } else { echo "false";} ?>,
            modules: <?php echo json_encode($spaceModules ); ?> ,
            notifs: {}
        }
    },
    mounted: function() {
        if(!this.logged) {
            return
        }
        const headers = new Headers();
                headers.append('Content-Type','application/json');
                headers.append('Accept', 'application/json');
        const cfg = {
            headers: headers,
            method: 'GET',
        };
        this.modules.forEach(mod => {
            let modName = mod.replace(' ', '').toLowerCase();
                fetch(`/core/tiles/${this.id_space}/module/${modName}/notifs`, cfg).
                    then((response) => response.json(), (error) => {}).
                    then(data => {
                        let n = {...this.notifs}
                        n[modName] = data.notifs
                        this.notifs = n
                    }).catch((error) => {
                        console.debug('failed to get notifications', modName, error);
                    })

        });
        
    },
    methods: {
    }
})


</script>


<?php
endblock();
?>
