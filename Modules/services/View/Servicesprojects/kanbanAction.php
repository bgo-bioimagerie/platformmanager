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
                <input type="text" v-model="newTask" aria-placeholder="Enter Task" @keyup.enter="addTask"/>
                <button class="ml-2 btn btn-primary" @click="add" style="margin:5px;">Add</button>
            </div>
        </div>

        <div class="row mt-3">

            <div class="col-md-3" v-for="(column, cindex) in columns">
                <div class="p-2 alert" v-bind:class="column.color">
                    <h3>{{column.title}}</h3>
                    <draggable :id="column.name" class="list-group kanban-column" :list="column.tasks" group="tasks" @change="changeState($event, cindex)">
                        <div class="list-group-item" v-for="element in column.tasks" :key="element.title" @click="showContent($event, element)">
                            {{element.title}}
                            <div class="bi bi-trash" style="display:inline" @click="deleteTask(element)"></div>
                            <div class="hidable" v-show="element.contentVisible">
                                <textarea class="contentArea">{{element.content}}</textarea>
                            </div>
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
            columns: [
                {name: "backlog", title: "Backlog", color: "alert-secondary", tasks: []},
                {name: "inProgress", title: "In progress", color: "alert-primary", tasks: []},
                {name: "done", title: "Done", color: "alert-success", tasks: []}
            ],
            id_space: "<?php echo $id_space ?>",
            id_project: "<?php echo $id_project ?>",
            tasks: <?php echo json_encode($tasks);?>
        }
    },
    created () {
        this.tasks.forEach(task => {
            task.contentVisible = false;
            this.columns[task.state].tasks.push(task)
        });
    },
    methods: {
        getTaskById(id) {
            this.tasks.forEach(task => {
                if (id == task.id) {
                    return task;
                }
            });
        },
        showContent(event, task) {
            task.contentVisible = !task.contentVisible;
            let hidables = event.target.getElementsByClassName("hidable");
            [...hidables].forEach(hidable => {
                hidable.style.display = task.contentVisible ? "" : "none";
            });
        },
        changeState(event, columnIndex) {
            if (event.added) {
                let newState = columnIndex;
                event.added.element.state = newState; 
                this.updateTask(event.added.element);
            }
        },
        async addTask() {
            if(this.newTask) {
                this.newTask = {
                    id: 0,
                    id_space: this.id_space,
                    id_project: this.id_project,
                    state: 0,
                    title: this.newTask,
                    content: "",
                    contentVisible: false
                };
                await this.updateTask(this.newTask)
                this.tasks.push(this.newTask);
                this.columns[0].tasks.push(this.newTask);
                this.newTask = "";
            }
        },
        async updateTask(task) {
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
            let targetUrl = `/servicesprojects/settask/`;
            let apiRoute = targetUrl + this.id_space + "/" + this.id_project;
            await fetch(apiRoute, cfg, true).
                then((response) => response.json()) .
                then(data => {
                    task.id = data.id;
                });
        },
        deleteTask(task) {
            let tasks = this.columns[task.state].tasks;
            tasks.splice(tasks.indexOf(tasks.find(element => element.id == task.id)), 1);
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
            let targetUrl = `/servicesprojects/deletetask/`
            let apiRoute = targetUrl + this.id_space + "/" + task.id;
            fetch(apiRoute, cfg, true)
        }
    }
});
</script>

<style>
    .kanban-column {
        min-height: 300px;
    }
    .contentArea {
        min-height: 150px;
    }
    .bi-trash {
        position: absolute;
        right: 5px;
        background-color: transparent;
        color: orangered;
    }
</style>

<?php include 'Modules/services/View/Servicesprojects/editscript.php';  ?>

<?php endblock(); ?>