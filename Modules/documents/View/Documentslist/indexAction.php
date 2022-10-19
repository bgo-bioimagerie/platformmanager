<?php include_once 'Modules/documents/View/layout.php' ?>


<?php startblock('content') ?>
<div class="container pm-table">
    <div class="row">
        <div class="col-12" id="doctree">


        <?php if ($userSpaceStatus >= CoreSpace::$MANAGER) { ?> 
        <div class="col-2" style="padding-top:7px;">
            <button type="button" class="btn btn-default" v-on:click="create()">
                <span class="bi-plus" aria-hidden="true"></span> <?php echo DocumentsTranslator::Add_Doc($lang) ?>
            </button>
            <p></p>
        </div>
        <?php } ?>

            <table style="background-color: white" aria-label="doc list" class="table table-striped">
                <thead><tr>
                    <th scope="col" aria-label="folder or file"></th>
                    <th scope="col"><?php echo DocumentsTranslator::Title($lang)?></th>
                    <th scope="col"><?php echo DocumentsTranslator::LastModified($lang)?></th>
                    <th scope="col"><?php echo DocumentsTranslator::Owner($lang)?></th>
                    <th scope="col" aria-label="actions"></th>
                </tr></thead>
                <tbody>
                    <tr v-if="level > 0">
                        <td v-on:click="up()"><span class="bi-folder2-open"></span></td>
                        <td>..</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr v-for="doc in docs">
                        <td><div v-if="doc.folder" v-on:click="goto(doc)" aria-label="go to folder"><span class="bi-folder2-open"></span></div><div aria-label="download" v-if="!doc.folder" v-on:click="download(doc.id)"><span class="bi-download"></span></div></td>
                        <td>{{doc.display}} <span style="margin-left: 10px" v-if="!doc.folder && doc.visibility!='Public'" class="bi-file-lock"></span></td>
                        <td><span v-if="!doc.folder">{{doc.date_modified}}</span></td>
                        <td><span v-if="!doc.folder">{{doc.user}}</span></td>
                        <td><?php if ($context['role'] > CoreSpace::$USER) { ?>
                            <div v-if="!doc.folder">
                                <a v-bind:href="'documentsedit/<?php echo $idSpace ?>/' + doc.id" ><button type="button" class="btn btn-sm btn-primary">Edit</button></a>
                                <button v-on:click="confirmDelete(doc)" type="button" class="btn btn-sm btn-danger">Delete</button></a>
                                <button v-on:click="copyLink(doc)" type="button" class="btn btn-sm">Copy link</button>
                            </div>
                            <?php } ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div id="search">
                <input aria-label="search doc input" class="form-control" v-model="search" placeholder="search" @input="findDocs"/>
                <table v-if="matches && matches.length>0" style="background-color: white" aria-label="search results" class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col" aria-label="download"></th>
                            <th scope="col"><?php echo DocumentsTranslator::Title($lang)?></th>
                            <th scope="col" aria-label="actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="match in matches">
                            <td><div aria-label="download" v-on:click="download(match.id)"><span class="bi-download"></span></div></td>
                            <td>{{match.title}}</td>
                            <td><?php if ($context['role'] > CoreSpace::$USER) { ?>
                                <div>
                                    <a v-bind:href="'documentsedit/<?php echo $idSpace ?>/' + match.id" ><button type="button" class="btn btn-sm btn-primary">Edit</button></a>
                                    <button v-on:click="confirmDelete(match)" type="button" class="btn btn-sm btn-danger">Delete</button></a>
                                </div>
                                <?php } ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
let doclist = <?php echo json_encode($data['documents']) ?>;
doclist.forEach((doc) => {
    let elts = doc.title.split('/');
    doc.path = elts;
    doc.size = elts.length;
});

<?php if ($dir) {
    $elts = explode('/', $dir);
    $l = count($elts);
} else {
    $l = 0;
}
?>


Vue.createApp({
    data () {
        return {
            docs: [],
            level: <?php if ($dir) {
                echo count(explode('/', $dir));
            } else {
                echo 0;
            } ?>,
            path: <?php echo "'$dir'" ?? 'null' ?>,
            search: '',
            matches: []
        }
    },
    created () {
        this.levels(this.level, this.path);
    },
    methods: {
        copyLink(doc) {
            let link = `<?php echo Configuration::get('public_url') ?>/documentsopen/<?php echo $idSpace?>/${doc.id}`;
            navigator.clipboard.writeText(link);
        },
        findDocs() {
            if(this.search.length === 0) {
                this.matches = [];
                return;
            }
            if(this.search.length<3) {
                return;
            }
            let found = [];
            doclist.forEach(d => {
               if(d.title.includes(this.search)) {
                   found.push(d);
               }
            });
            this.matches = found;
        },
        create() {
            window.location.href = 'documentsedit/<?php echo $idSpace ?>/0/?dir='+this.path.join('/')
        },
        confirmDelete(doc) {
            if (confirm(`Delete ${doc.display} ?`)) {
                window.location.href = 'documentsdelete/<?php echo $idSpace ?>/' + doc.id;
            }
        },
        download(id) {
            window.open(`documentsopen/<?php echo $idSpace?>/${id}`)
        },
        up() {
            if(this.level<1) {
                return;
            }
            this.level--;
            let subpath = this.path.slice(0, this.level).join('/')
            this.levels(this.level, subpath);
        },
        goto(doc) {
            this.level++;
            dpath = doc.display;
            if(doc.subpath) {
                dpath = doc.subpath+'/'+doc.display;
            }
            this.levels(this.level, dpath)
        },
        levels(depth, name) {
            let data = [];
            let data1 = [];
            let data2 = [];
            let dirs = {};
            doclist.forEach(d => {
                if(name && !d.title.startsWith(name)) {
                    return;
                }
                if(d.size >= (depth+1)) {
                    d.display = d.path[depth]
                    d.folder = false;
                    if(d.size > (depth+1)) {
                        if(dirs[d.display] !== undefined) {
                            return;
                        }
                        dirs[d.display] = true;
                        d.folder = true;
                        d.subpath = '';
                        if(depth > 0) {
                            d.subpath = d.path.slice(0, depth)
                        }
                    }
                    if(d.folder) {data1.push(d)}
                    else {data2.push(d)}
                }
            })
            this.path = [];
            if(name) {
                this.path = name.split('/');
            }
            data1.forEach(d => {
                data.push(d)
            })
            data2.forEach(d => {
                data.push(d)
            })
            this.docs = data
        }
    }
}).mount('#doctree');

</script>


<?php endblock(); ?>