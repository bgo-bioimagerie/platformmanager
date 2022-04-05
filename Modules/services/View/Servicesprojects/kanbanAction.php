<?php include 'Modules/services/View/layout.php' ?>
<?php Configuration::getLogger()->debug("[TEST]", ["in kanbanAction view"]); ?>

<?php startblock('content') ?>
<div class="pm-form">

    <div class="col-12">
        <h3> <?php echo $projectName ?> </h3>
    </div>
    
    <div class="col-12">
        <?php include 'Modules/services/View/Servicesprojects/projecttabs.php'; ?>
    </div>
<!-- BEGINS -->

<div id="board" class="container">

</div>

<script>
let app = new Vue({
    el: '#board',
    data () {
        return {
            docs: [],
            id_space: "<?php echo $id_space ?>",
            search: '',
            tasks: <?php echo json_encode($tasks);?>
        }
    },
    created () {
            this.levels(this.level, this.path);
    },
    methods: {
        copyLink(doc) {
            let link = `<?php echo Configuration::get('public_url') ?>/documentsopen/<?php echo $id_space?>/${doc.id}`;
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
            window.location.href = 'documentsedit/<?php echo $id_space ?>/0/?dir='+this.path.join('/')
        },
        confirmDelete(doc) {
            if (confirm(`Delete ${doc.display} ?`)) {
                window.location.href = 'documentsdelete/<?php echo $id_space ?>/' + doc.id;
            }
        },
        download(id) {
            window.open(`documentsopen/<?php echo $id_space?>/${id}`)
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
});
</script>
<!-- ENDS -->

<?php include 'Modules/services/View/Servicesprojects/editscript.php';  ?>

<?php endblock(); ?>