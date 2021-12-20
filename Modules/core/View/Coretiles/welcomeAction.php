<?php include 'Modules/core/View/layout.php' ?>

<?php startblock('stylesheet') ?>

<style>
    .modulebox{
        border: solid 1px #e1e1e1; 
        border-bottom: solid 3px #e1e1e1; 
        min-height:325px; 
        width:220px; 
        margin-left: 25px;
        margin-top: 25px;
        background-color: white;
    }

</style>

<?php endblock(); ?>
<!-- body -->     
<?php startblock('content') ?>

<div class="container" id="welcome">
    <div class="row">

        <div class="col-md-2">
                <div class="col-xs-12"><h3><?php echo CoreTranslator::Menus($lang); ?></h3></div>
                <?php 
            foreach ($mainMenus as $menu) {
                echo '<div style="margin: 10px" >';
                echo sprintf('<a href="coretiles/1/%s"><button class="btn btn-primary btn-block">%s</button></a></li>', $menu['id'], $menu['name']);
                echo '</div>';
            }
            ?>
        </div>


        <div class="col-md-6">
            <div class="row" style="margin: 10px;">
                <div class="col-xs-6">
                    <input id="search" type="form-control" v-model="search" placeholder="search"/>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-md-4 col-lg-2 modulebox" v-for="space in matches" :key="space.id">
                    <a :href="`corespace/${space.id}`">
                    <img v-if="space.image" :src="space.image" onerror="this.style.display='none'" alt="logo" style="margin-left: -15px;width:218px;height:150px">
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
                    <div v-if="space.join" style="bottom: 20px; right: 10px">
                        <a :href="`coretilesselfjoinspace/${space.id}`">
                            <button type="button" class="btn btn-md btn-success">{{space.join}}</button>
                        </a>
                    </div>
                    <div v-if="space.join_requested" style="bottom: 20px; right: 10px">
                        <button type="button" class="btn btn-md btn-info" disabled>{{space.join_requested}}</button>
                    </div>
                </div>
            </div>
            <div class="row" id="user_stars">
                <?php foreach($spaces as $item) { ?>
                <div class="col-xs-12 col-md-4 col-lg-2 modulebox">
                    <a href="<?php echo "corespace/" . $item["id"] ?>">
                    <?php if(isset($icon)) {?><img aria-label="space logo" onerror="this.style.display='none'" src="<?php echo $item["image"] ?>" alt="logo" style="margin-left: -15px;width:218px;height:150px"><?php } ?>
                    </a>
                    <p></p>
                    <p style="color:#018181; ">
                        <a href="<?php echo "corespace/" . $item["id"] ?>"> <?php echo $item["name"] ?></a>
                        <?php if(isset($_SESSION["login"]) && $_SESSION["id_user"] > 0) { ?>
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
            <?php if(!isset($_SESSION['id_user']) || $_SESSION['id_user'] <= 0) { ?>
            <div class="row">
                <div class="col-xs-12">
                    <?php echo $content; ?>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="col-md-4" id="user_home">
            <?php if(isset($_SESSION['id_user']) && $_SESSION['id_user'] > 0) { ?>
                <div v-if="bookings && bookings.length > 0" id="future_bookings">
                    <div class="panel panel-default">
                        <div class="panel-heading">Bookings</div>
                        <div class="panel-body">
                            <div class="row" v-for="b in bookings">
                                <div class="col-xs-12">
                                    {{b.date}}: {{b.resource}} [{{b.space}}] <a :href="`/bookingeditreservation/${b.id_space}/r_${b.id}`"><span class="glyphicon glyphicon-zoom-in"></span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="projects && projects.length > 0" id="projects">
                    <div class="panel panel-default">
                        <div class="panel-heading">Service projects</div>
                        <div class="panel-body">
                            <div class="row" v-for="b in projects">
                                <div class="col-xs-12">
                                    {{b.date}}: {{b.name}} [{{b.space}}] <a :href="`/servicesprojectsheet/${b.id_space}/${b.id}`"><span class="glyphicon glyphicon-zoom-in"></span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div> <!-- /container -->
<script>
let spaces = <?php echo json_encode($spaceMap); ?>;
let resources = <?php echo json_encode($resources); ?>;
let catalog = <?php echo json_encode($catalog); ?>;

var app = new Vue({
    el: '#welcome',
    data () {
        return {
            logged: <?php if(isset($_SESSION['id_user']) && $_SESSION['id_user'] > 0) { echo "true"; } else { echo "false";} ?>,
            spaces: <?php echo json_encode($spaceMap); ?> ,
            catalog: <?php echo json_encode($resources); ?>,
            resources: <?php echo json_encode($catalog); ?>,
            search: '',
            matches: [],
            bookings: [],
            projects: [],
        }
    },
    mounted: function() {
        if(!this.logged) {
            return
        }
        let headers = new Headers();
                headers.append('Content-Type','application/json');
                headers.append('Accept', 'application/json');
        let cfg = {
            headers: headers,
            method: 'GET',
        };
        try {
            fetch(`/user/booking/future/0/0`, cfg).
                then((response) => response.json()).
                then(data => {
                    let bookings = []
                    data.bookings.forEach((elem) => {
                        let bdate = new Date(elem.start_time*1000);
                        bookings.push({
                            "id": elem.id,
                            "id_space": elem.id_space,
                            "resource": elem.resource,
                            "id_resource": elem.resource_id,
                            "space": elem.space,
                            "date": `${bdate.toLocaleDateString()} ${bdate.toLocaleTimeString()}`
                        });
                    });

                    this.bookings = bookings
                })
        } catch(error) {
            console.debug('failed to get user bookings', error);
        }
        try {
            fetch(`/user/services/projects/0`, cfg).
                then((response) => response.json()).
                then(data => {
                    let projects = []
                    data.projects.forEach((elem) => {
                        projects.push({
                            "id": elem.id,
                            "id_space": elem.id_space,
                            "name": elem.name,
                            "space": elem.space,
                            "date": elem.date_open
                        });
                    });

                    this.projects = projects
                })
        } catch(error) {
            console.debug('failed to get user bookings', error);
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
