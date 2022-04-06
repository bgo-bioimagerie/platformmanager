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
                    <draggable id="backlog" class="list-group kanban-column" :list="arrBacklog" group="tasks" @change="changeState($event, 'backlog')">
                        <div class="list-group-item" v-for="element in arrBacklog" :key="element.title">
                            {{element.title}}
                        </div>
                    </draggable>
                </div>
            </div>

            <div class="col-md-3">
                <div class="p-2 alert alert-primary">
                    <h3>In progress</h3>
                    <draggable id="inProgress" class="list-group kanban-column" :list="arrInProgress" group="tasks" @change="changeState($event, 'inProgress')">
                        <div class="list-group-item" v-for="element in arrInProgress" :key="element.title">
                            {{element.title}}
                        </div>
                    </draggable>
                </div>
            </div>
        
            <div class="col-md-3">
                <div class="p-2 alert alert-success">
                    <h3>Done</h3>
                    <draggable id="done" class="list-group kanban-column" :list="arrDone" group="tasks" @change="changeState($event, 'done')">
                        <div class="list-group-item" v-for="element in arrDone" :key="element.title">
                            {{element.title}}
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
            arrBacklog: [],
            arrInProgress: [],
            arrDone: [],

            id_space: "<?php echo $id_space ?>",
            id_project: "<?php echo $id_project ?>",
            tasks: <?php echo json_encode($tasks);?>
        }
    },
    created () {
        this.tasks.forEach(task => {
            // console.log("task: ", task);
            switch(task.state) {
                case "0":
                    this.arrBacklog.push(task)
                    break;
                case "1":
                    this.arrInProgress.push(task)
                    break;
                case "2":
                    this.arrDone.push(task)
                    break;
                default:
                    console.error("unknown state for task ", task.name);
                    break;
            }
        });
    },
    methods: {
        changeState(event, tasksList) {
            console.log("tasksList: ", tasksList);
            console.log("event: ", event);
            if (event.added) {
                console.log("event.added.element: ", JSON.stringify(event.added.element));
                let newState = null;

                switch(tasksList) {
                    case "backlog":
                        newState = 0;
                        break;
                    case "inProgress":
                        newState = 1;
                        break;
                    case "done":
                        newState = 2;
                        break;
                    default:
                        newState = 0;
                        console.error("unknown tasksList ", tasksList, " moving task to backlog");
                        break;
                }

                event.added.element.state = newState; 
                this.updateTask(event.added.element);
            }
            
        },
        add() {
            if(this.newTask) {
                this.arrBacklog.push({title: this.newTask});
                this.updateTask({
                    id: 0,
                    id_space: this.id_space,
                    id_project: this.id_project,
                    state: 0,
                    title: this.newTask,
                    content: ""
                });
                this.newTask = "";
            }
        },
        updateTask(task) {
            console.log("updating task: ", task);
            const headers = new Headers();
            headers.append('Content-Type','application/json');
            headers.append('Accept', 'application/json');
            const cfg = {
                headers: headers,
                method: 'POST',
                body: null
            };
            cfg.body = JSON.stringify({
                task: task
            });
            let targetUrl = `/serviceskanban/settask/`;
            console.log("id_space: ", this.id_space)
            let apiRoute = targetUrl + this.id_space + "/" + this.id_project;
            fetch(apiRoute, cfg, true)/* .
            then((response) => response.json()).
            then(data => {
                console.log("data: ", data)
            }).catch( error => {
                console.error("error in setting task " + task.title + " data:", error);
            }); */

        },
        getTasks() {

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