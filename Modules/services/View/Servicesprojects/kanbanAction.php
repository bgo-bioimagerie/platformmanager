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
                <button class="ml-2 btn btn-primary" @click="addTask" style="margin:5px;">Add</button>
            </div>
        </div>

        <div class="row mt-3">

            <div class="col-md-3" v-for="(column, cindex) in columns">
                <div class="p-2 alert" v-bind:class="column.color">
                    <h3>{{column.title}}</h3>
                    <draggable :id="column.name" class="list-group kanban-column" :list="column.tasks" group="tasks" @change="changeState($event, cindex)">
                        <div class="list-group-item" style="cursor:grab;" v-for="element in column.tasks" :key="element.title">
                            {{element.title}}
                            <button class="bi bi-arrow-right round" @click="showContent($event, element)"></button>
                            <div class="bi bi-trash" @click="deleteTask(element)"></div>
                            <div class="hidable mt-3" v-show="element.contentVisible">
                                <textarea class="contentArea" @blur="updateTaskContent($event, element)">{{element.content}}</textarea>
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
            if (!event.target.classList.contains("contentArea")) {
                task.contentVisible = !task.contentVisible;
                this.updateHidables(event.target.parentElement, task);
            }
        },
        changeState(event, columnIndex) {
            if (event.added) {
                let newState = columnIndex;
                event.added.element.state = newState;
                event.added.element.contentVisible = false;

                let draggableElement = document.getElementById(this.columns[columnIndex].name);
                this.updateHidables(draggableElement, event.added.element);
                this.updateTask(event.added.element);
            }
        },
        updateHidables(parentElement, task) {
            let arrowClasses = task.contentVisible
                ? ['bi-arrow-down', 'bi-arrow-right']
                : ['bi-arrow-right', 'bi-arrow-down'];
            if (event.target && event.target.nodeName == "BUTTON") {
                event.target.classList.add(arrowClasses[0]);
                event.target.classList.remove(arrowClasses[1]);
            }
            let hidables = parentElement.getElementsByClassName("hidable");
            [...hidables].forEach(hidable => {
                if (task.contentVisible) {
                    hidable.style.display = "";
                    hidable.focus();
                } else {
                    hidable.style.display = "none";
                }
            });
        },
        updateTaskContent(event, task) {
            task.content = event.target.value;
            this.updateTask(task);
        },
        async addTask(task=null) {
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
            if (confirm("You are about to delete " + task.title + "?")) {
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
    }
});
</script>

<style>
    .kanban-column {
        min-height: 300px;
    }
    .list-group-item {
        align-content: right;
    }
    .contentArea {
        min-height: 150px;
        max-width: 100%;
    }
    .bi-trash {
        display:inline;
        position: relative;
        float: right;
        background-color: transparent;
        color: orangered;
        cursor:pointer;
    }
    .round {
        background-color: transparent;        
        border-color: transparent;
        padding: 5px;
        text-decoration: none;
        display: inline-block;
        font-size: 12px;
        border-radius: 100%
    }
</style>

<?php include 'Modules/services/View/Servicesprojects/editscript.php';  ?>

<?php endblock(); ?>