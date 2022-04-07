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
    
<?php startblock('content') ?>

<div class="" id="welcome">
    <div class="row">

        <div class="col-12 col-md-2">
            <div><h3><?php echo CoreTranslator::Menus($lang); ?></h3></div>
            <div class="btn-group-vertical btn-group-justified" role=group">
                    <a class="m-1 btn btn-primary" href="coretiles?mine=1"><?php echo CoreTranslator::MySpaces($lang); ?></a>
                <?php 
            foreach ($mainMenus as $menu) {
                echo sprintf('<a class="m-1 btn btn-primary" href="coretiles/1/%s">%s</a>', $menu['id'], $menu['name']);
            }
            ?>
            </div>
        </div>


        <div class="col-12 col-md-6">
            <div class="row">
                <div class="col-4">
                    <input id="search" type="text" class="form-control" v-model="search" placeholder="search"/>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-4 m-2" v-for="space in matches" :key="space.id">
                <div class="card text-dark bg-light">
                    <div class="card-header">
                        <a :href="`corespace/${space.id}`">{{space.name}} [{{menus[space.id] || ""}}] <span v-if="space.status == 0" aria-hidden="true" aria-label="private" class="bi-lock-fill"></span></a>
                    </div>
                   <div class="card-body">
                        <img class="card-img-top" v-if="space.image" :src="space.image" onerror="this.style.display='none'" alt="logo" style="width:100%; max-width:200px">
                        <p><small>{{space.description.substr(0, 50)}}</small></p>
                        <div>
                            <small v-if="space.support">
                            support: <a :href="`mailto:${space.support}`">{{space.support}}</a>
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div v-if="space.join" style="bottom: 20px">
                            <a :href="`coretilesselfjoinspace/${space.id}`">
                                <button type="button" class="btn btn-sm btn-success">{{space.join}}</button>
                            </a>
                        </div>
                        <div v-if="space.join_requested" style="bottom: 20px">
                            <button type="button" class="btn btn-sm btn-info" disabled>{{space.join_requested}}</button>
                        </div>
                    </div>
                </div>
                </div>
            </div>
            <div class="row" id="user_stars">
                <?php foreach($spaces as $item) { ?>
                <div class="col-12 col-md-6 m-2">
                <div class="card text-dark bg-light">
                    <div class="card-header">
                        <a href="<?php echo "corespace/" . $item["id"] ?>"> <?php echo $item["name"] ?> <?php $menu = array_key_exists($item['id'], $itemsMenus) ? $itemsMenus[$item['id']] : ''; echo "[$menu]" ?></a>
                        <?php if(isset($_SESSION["id_user"]) && $_SESSION["id_user"] > 0) { ?>
                                <a aria-label="remove from favorites" href="<?php echo "coretiles/1/0/unstar/".$item["id"] ?>"><span aria-hidden="true" class="bi-star-fill"></span></a>
                        <?php } ?>
                        <?php if($item["status"] == 0) { echo '<span class="bi-lock-fill" aria-hidden="true" aria-label="private"></span>'; } ?>
                    </div>
                    <div class="card-body">
                        <?php if(isset($item['image'])) {?><img class="card-img-top" aria-label="space logo" onerror="this.style.display='none'" src="<?php echo $item["image"] ?>" alt="logo" style="width:100%; max-width:200px"><?php } ?>
                        <?php if(strlen($item['description']) > 50) { echo '<p>'.substr($item["description"], 0, 50).'...</p>'; } else { echo '<p>'.$item['description'].'</p>'; } ?>
                        </div>
                    <div class="card-footer">
                        <small>
                        <?php if($item["support"]) {  echo 'support: <a href="mailto:'.$item["support"].'">'.$item["support"].'</a>'; } ?>
                        </small>
                    </div>
                </div>
                </div>
                <?php } ?>
            </div>


            <div class="row" id="user_spaces">
                <?php foreach($userSpaces as $item) { ?>
                <div class="col-6 col-md-4 m-2">    
                <div class="card text-dark bg-light">
                    <?php if(isset($icon)) {?><img class="card-img-top" aria-label="space logo" onerror="this.style.display='none'" src="<?php echo $item["image"] ?>" alt="logo" style="margin-left: -15px;width:218px;height:150px"><?php } ?>
                    <div class="card-header">
                        <a href="<?php echo "corespace/" . $item["id"] ?>"> <?php echo $item["name"] ?> <?php $menu = array_key_exists($item['id'], $itemsMenus) ? $itemsMenus[$item['id']] : ''; echo "[$menu]" ?></a>
                        <?php if($item["status"] == 0) { echo '<span class="bi-lock-fill" aria-hidden="true" aria-label="private"></span>'; } ?>
                    </div>
                    <div class="card-body">
                        <?php echo $item["description"] ?>
                    </div>
                    <div class="card-footer">
                        <small>
                        <?php if($item["support"]) {  echo 'support: <a href="mailto:'.$item["support"].'">'.$item["support"].'</a>'; } ?>
                        </small>
                    </div>
                </div>
                </div> 
                <?php } ?>
            </div>


            <?php if(!isset($_SESSION['id_user']) || $_SESSION['id_user'] <= 0) { ?>
            <div class="row">
                <div class="col-12 text-dark bg-light text-center m-3" id ="welcome" style="min-height: 400px">
                    <?php if($content) { echo $content; } else {?>
                        <h3 style="margin: 20px"><?php echo CoreTranslator::welcome($lang) ?></h3>
                        <a href="coreconnection"><button class="btn btn-primary"><?php echo CoreTranslator::login($lang) ?></button></a>
                        <?php if(Configuration::get('allow_registration', 0)) { ?>
                            OR
                            <a href="corecreateaccount"><button class="btn btn-primary"><?php echo CoreTranslator::CreateAccount($lang) ?></button></a>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="col-12 col-md-4" id="user_home">
            <?php if(isset($_SESSION['id_user']) && $_SESSION['id_user'] > 0) { ?>
                <div v-if="bookings && bookings.length > 0" id="future_bookings">
                    <div class="card text-dark bg-light">
                        <div class="card-header">Bookings</div>
                        <div class="card-body">
                            <div class="row" v-for="b in bookings">
                                <div class="col-12">
                                    {{b.date}}: {{b.resource}} [{{b.space}}] <a :href="`/bookingeditreservation/${b.id_space}/r_${b.id}`"><span class="bi-zoom-in"></span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="projects && projects.length > 0" id="projects">
                    <div class="card text-dark bg-light">
                        <div class="card-header">Service projects</div>
                        <div class="card-body">
                            <div class="row" v-for="b in projects">
                                <div class="col-12">
                                    {{b.date}}: {{b.name}} [{{b.space}}] <a :href="`/servicesprojectsheet/${b.id_space}/${b.id}`"><span class="bi-zoom-in"></span></a>
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
            menus: <?php echo json_encode($itemsMenus); ?>
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
                if((space.name && space.name.toLowerCase().includes(event)) || (space.description && space.description.toLowerCase().includes(event))){
                    spaces.push(space)
                    slist[space.id] = true
                }
            })
            this.catalog.forEach(c => {
                if (slist[c.id_space]) {
                    return
                }
                if ((c.title && c.title.toLowerCase().includes(event)) || (c.short_desc && c.short_desc.toLowerCase().includes(event)) || (c.full_desc && c.full_desc.toLowerCase().includes(event))) {
                    spaces.push(this.spaces[c.id_space])
                    slist[c.id_space] = true
                }
            })
            this.resources.forEach(c => {
                if (slist[c.id_space]) {
                    return
                }
                if ((c.name && c.name.toLowerCase().includes(event)) || (c.description && c.description.toLowerCase().includes(event)) || (c.long_description && c.long_description.toLowerCase().includes(event))) {
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
<?php endblock(); ?>
