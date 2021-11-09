<?php include 'Modules/core/View/layout.php' ?>

<?php startblock('stylesheet') ?>

<link rel="stylesheet" type="text/css" href="externals/bootstrap/css/bootstrap.min.css">
<?php
$headless = Configuration::get("headless");
if (!$headless) {
    ?>
    <link href="data/core/theme/navbar-fixed-top.css" rel="stylesheet">
    <?php
}
?>
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/core.css' />
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/space.css' />

<style>
    .modulebox{
        border: solid 1px #e1e1e1; 
        border-bottom: solid 3px #e1e1e1; 
        height:325px; 
        width:220px; 
        margin-left: 25px;
        margin-top: 25px;
    }    
</style>

<?php endblock(); ?>
<!-- body -->     
<?php startblock('content') ?>


<div class="col-xs-12 pm-tile-container"  >
    <div class="container" style="margin-top: 50px;">
        <div id="spacesearch" class="col-xs-12" style="margin: 50px;">
            <div class="row">
                <div class="col-xs-6">
                    <input id="search" type="form-control" v-model="search" placeholder="search"/>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-md-4 col-lg-2 modulebox" v-for="space in matches" :key="space.id">
                    <a :href="`corespace/${space.id}`">
                    <img v-if="space.image" :src="space.image" alt="logo" style="margin-left: -15px;width:218px;height:150px">
                    </a>
                    <p></p>
                    <p style="color:#018181; ">
                        <a :href="`corespace/${space.id}`">{{space.name}}  <span v-if="space.status == 0" aria-hidden="true" aria-label="private" class="glyphicon glyphicon-lock"></span></a>
                    </p>
                    <p style="color:#a1a1a1; font-size:12px;">{{space.description}}</p>
                    <div>
                        <small v-if="space.support">
                        support: <a :href="`mailto:${space.support}`">{{space.support}}</a>
                        </small>
                    </div>
                    <div v-if="space.join" style="position: absolute; bottom: 20px; right: 10px">
                        <a :href="`coretilesselfjoinspace/${space.id}`">
                            <button type="button" class="btn btn-md btn-success">{{space.join}}</button>
                        </a>
                    </div>
                    <div v-if="space.join_requested" style="position: absolute; bottom: 20px; right: 10px">
                        <button type="button" class="btn btn-md btn-info" disabled>{{space.join_requested}}</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12">
            <?php echo $content; ?>
        </div>
        <?php foreach($spaces as $item) { ?>

            <div class="col-xs-12 col-md-4 col-lg-2 modulebox">
                <a href="<?php echo "corespace/" . $item["id"] ?>">
                <?php if(isset($icon)) {?><img aria-label="space logo" src="<?php echo $item["image"] ?>" alt="logo" style="margin-left: -15px;width:218px;height:150px"><?php } ?>
                </a>
                <p></p>
                <p style="color:#018181; ">
                    <a href="<?php echo "corespace/" . $item["id"] ?>"> <?php echo $item["name"] ?></a>
                    <?php if(isset($_SESSION["login"])) { ?>
                            <a aria-label="remove from favorites" href="<?php echo "coretiles/1/0/unstar/".$item["id"] ?>"><span aria-hidden="true" class="glyphicon glyphicon-star"></span></a>
                    <?php } ?>
                    <?php if($item["status"] == 0) { echo '<span class="glyphicon glyphicon-lock" aria-hidden="true" aria-label="private"></span>'; } ?>
                </p>
                <p style="color:#a1a1a1; font-size:12px;">
                    <?php echo $item["description"] ?>
                </p>
                <div style="position: absolute; bottom: 0px">
                    <small>
                    <?php if($item["support"]) {  echo 'support: <a href="mailto:'.$item["support"].'">'.$item["support"].'</a>'; } ?>
                    </small>
                </div>
            </div>  



        <?php } ?>
    </div>
</div> <!-- /container -->
<script>
let spaces = <?php echo json_encode($spaceMap); ?>;
let resources = <?php echo json_encode($resources); ?>;
let catalog = <?php echo json_encode($catalog); ?>;

var app = new Vue({
    el: '#spacesearch',
    data () {
        return {
            spaces: <?php echo json_encode($spaceMap); ?> ,
            catalog: <?php echo json_encode($resources); ?>,
            resources: <?php echo json_encode($catalog); ?>,
            search: '',
            matches: []
        }
    },
    watch: {
        search(search_event) {
            //let event = evt.target.value;
            if(search_event.length < 3) {
                this.matches = []
                return
            }
            let event = search_event.toLowerCase();
            let spaces = []
            let slist = {}
            Object.keys(this.spaces).forEach(s => {
                let space = this.spaces[s]
                if(space.name?.toLowerCase().includes(event) || space.description?.toLowerCase().includes(event)){
                    spaces.push(space)
                    slist[space.id] = true
                }
            })
            this.catalog.forEach(c => {
                if (slist[c.id_space]) {
                    return
                }
                if (c.title?.toLowerCase().includes(event) || c.short_desc?.toLowerCase().includes(event) || c.full_desc?.toLowerCase().includes(event)) {
                    spaces.push(this.spaces[c.id_space])
                    slist[c.id_space] = true
                }
            })
            this.resources.forEach(c => {
                if (slist[c.id_space]) {
                    return
                }
                if (c.name?.toLowerCase().includes(event) || c.description?.toLowerCase().includes(event) || c.long_description?.toLowerCase().includes(event)) {
                    spaces.push(this.spaces[c.id_space])
                    slist[c.id_space] = true
                }
            })
            this.matches = spaces
        }
    },
    methods: {
    }
})


</script>
<?php
endblock();
