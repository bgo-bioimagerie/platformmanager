<?php include 'Modules/services/View/layout.php' ?>

<?php startblock('content') ?>
<div class="pm-form">

    <div class="col-12">
        <h3> <?php echo $projectName ?> </h3>
    </div>
    
    <div class="col-12">
        <?php include 'Modules/services/View/Servicesprojects/projecttabs.php'; ?>
    </div>

    <div id="board" class="container mt-5">
        <div class="row">
            <div class="col form-inline">
                <input type="text" v-model="newTask" aria-placeholder="Enter Task" @keyup.enter="add"/>
                <button class="ml-2 btn btn-primary" @click="add" style="margin:5px;">Add</button>
            </div>
        </div>

        <div class="row mt-3">

            <div class="col-md-3">
                <div class="p-2 alert alert-secondary">
                    <h3>Backlog</h3>
                    <draggable class="list-group kanban-column" :list="arrBacklog" group="tasks">
                        <div class="list-group-item" v-for="element in arrBacklog" :key="element.name">
                            {{element.name}}
                        </div>
                    </draggable>
                </div>
            </div>

            <div class="col-md-3">
                <div class="p-2 alert alert-primary">
                    <h3>In progress</h3>
                    <draggable class="list-group kanban-column" :list="arrInProgress" group="tasks">
                        <div class="list-group-item" v-for="element in arrInProgress" :key="element.name">
                            {{element.name}}
                        </div>
                    </draggable>
                </div>
            </div>
        
            <div class="col-md-3">
                <div class="p-2 alert alert-warning">
                    <h3>Tested</h3>
                    <draggable class="list-group kanban-column" :list="arrTested" group="tasks">
                        <div class="list-group-item" v-for="element in arrTested" :key="element.name">
                            {{element.name}}
                        </div>
                    </draggable>
                </div>
            </div>
        
            <div class="col-md-3">
                <div class="p-2 alert alert-success">
                    <h3>Done</h3>
                    <draggable class="list-group kanban-column" :list="arrDone" group="tasks">
                        <div class="list-group-item" v-for="element in arrDone" :key="element.name">
                            {{element.name}}
                        </div>
                    </draggable>
                </div>
            </div>

        </div>
    </div>
</div>


<script type="module">
import draggable from '/externals/node_modules/vuedraggable/src/vuedraggable.js';


let app = new Vue({
    el: '#board',
    data () {
        return {
            newTask: "",
            arrBacklog: [
                {name: "Code sign Up Page"},
                {name: "Test dashboard"},
                {name: "Style registration"},
                {name: "Help with designs"}
            ],
            arrInProgress: [],
            arrTested: [],
            arrDone: [],

            id_space: "<?php echo $id_space ?>",
            tasks: <?php echo json_encode($tasks);?>
        }
    },
    created () {

    },
    methods: {
        add() {
            if(this.newTask) {
                this.arrBacklog.push({name: this.newTask});
                console.log("backlog: ", this.arrBacklog)
                this.newTask = "";
            }
        }
    }
});
</script>

<style>
    .kanban-column {
        min-height: 300px;
    }
</style>

<?php include 'Modules/services/View/Servicesprojects/editscript.php';  ?>

<?php endblock(); ?>