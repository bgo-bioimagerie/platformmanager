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
                <label for="newTask">New task</label>
                <input id="newTask" type="text" v-model="newTask" aria-placeholder="Enter Task" @keyup.enter="addTask"/>
                <button class="ml-2 btn btn-primary" @click="addTask" style="margin:5px;">Add</button>
            </div>

            <div class="col form-inline">
                <label for="newCat">New Category</label>
                <input id="newCat" type="text" v-model="newCategory" aria-placeholder="Enter Category" @keyup.enter="addCategory"/>
                <input id="newCatColor" type="color" v-model="newCategoryColor" aria-placeholder="Choose Color" style="vertical-align:middle; margin-bottom:5px" value="#000000"/>
                <button class="ml-2 btn btn-primary" @click="addCategory" style="margin:5px;">Add</button>
            </div>
        </div>

        <div class="row mt-3">
            <draggable id="categories" class="kanban-categories" :list="categories" group="categories" @change="changeCategoryPosition">
                <div class="category col-md-3" v-for="(category, cindex) in categories" style="display:inline-flex">
                    <div :id=category.id class="p-2 alert">
                        <div class="bi bi-x-square-fill deleteBtn" @click="deleteCategory(category)"></div>
                        <h3>
                            {{category.title}}
                            <button class="bi bi-pencil-square round" @click="editCategory($event, category)"></button>
                        </h3>
                        
                        <input hidden class="categoryColorInput" type="color" v-model="category.color" aria-placeholder="Choose Color" value="category.color" @change="editCategoryColor($event, category)"/>
                        

                        <draggable :id="category.name" class="list-group kanban-category" :list="category.tasks" group="tasks" @change="changeTaskState($event, cindex)">
                            <div class="list-group-item" style="cursor:grab;" v-for="element in category.tasks" :key="element.id">
                                {{element.title}}
                                <button class="bi bi-arrow-right round" @click="showTaskContent($event, element)"></button>
                                <div class="bi bi-trash deleteBtn" @click="deleteTask(element)"></div>
                                <div class="hidable mt-3" v-show="element.contentVisible">
                                    <textarea class="contentArea" @blur="updateTaskContent($event, element)">{{element.content}}</textarea>
                                </div>
                            </div>
                        </draggable>

                    </div>
                </div>
            </draggable>
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
            newCategory: "",
            newCategoryColor: "#000000",
            categories: <?php echo json_encode($categories);?>,
            id_space: "<?php echo $id_space ?>",
            id_project: "<?php echo $id_project ?>",
            tasks: <?php echo json_encode($tasks);?>
        }
    },
    created () {
        this.categories.forEach(category => {
            category.name = category.title.replace(/\s/g, '').toLowerCase();
        });
        this.tasks.forEach(task => {
            task.contentVisible = false;
            this.categories[task.state].tasks.push(task)
        });
    },
    mounted () {
        this.categories.forEach(category => {
            this.getRGBAColor(category);
        });
    },
    updated() {
        this.categories.forEach(category => {
            this.getRGBAColor(category);
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
        editCategory(event, category) {
            console.log("category.color: ", category.color);
            let categoryName = category.title// event.target.innerText;
            let newName = prompt("Rename category", categoryName);
            if (newName != null) {
                category.title = newName;
                category.name = category.title.replace(/\s/g, '').toLowerCase();
                // let colorInput = event.target.parentElement.parentElement.getElementsByClassName("categoryColorInput")[0];
                let colorInput = document.getElementById(category.id).getElementsByClassName("categoryColorInput")[0];
                colorInput.click();
            }
            this.updateCategories();
        },

        editCategoryColor(event, category) {
            category.color = event.target.value;
            this.updateCategories();
        },

        getRGBAColor(category, opacity=0.5) {
            let h = category.color.substring(1,7);
            let r = parseInt(h.substring(0,2),16);
            let g = parseInt(h.substring(2,4), 16);
            let b = parseInt(h.substring(4,6),16);
            let a  = opacity;
            let rgbaColor = "rgba(" + r +"," + g + "," + b + "," + a + ")";
            let categoryGroup = document.getElementById(category.id);
            categoryGroup.style.backgroundColor = rgbaColor;
        },
        showTaskContent(event, task) {
            if (!event.target.classList.contains("contentArea")) {
                task.contentVisible = !task.contentVisible;
                this.updateHidables(event.target.parentElement, task);
            }
        },
        changeTaskState(event, categoryIndex) {
            if (event.added) {
                let newState = categoryIndex;
                event.added.element.state = newState;
                event.added.element.contentVisible = false;

                let draggableElement = document.getElementById(this.categories[categoryIndex].id);
                this.updateHidables(draggableElement, event.added.element);
                this.updateTask(event.added.element);
            }
        },
        changeCategoryPosition(event) {
            if (event.moved) {
                event.moved.element.position = event.moved.newIndex;
                this.categories.forEach((category, index) => {
                    category.position = index;
                });
                this.updateCategories();
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
                this.categories[0].tasks.push(this.newTask);
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
                then((response) => response.json()).
                then(data => {
                    task.id = data.id;
                });
        },
        async addCategory(category=null) {
            if(this.newCategory) {
                this.newCategory = {
                    id: 0,
                    id_space: this.id_space,
                    id_project: this.id_project,
                    position: this.categories.length,
                    title: this.newCategory,
                    color: this.newCategoryColor,
                    tasks: []
                };
                this.categories.push(this.newCategory);
                await this.updateCategories();
                this.newCategory = "";
            }
        },
        async updateCategories() {
            const headers = new Headers();
            headers.append('Content-Type','application/json');
            headers.append('Accept', 'application/json');
            const cfg = {
                headers: headers,
                method: 'POST',
                body: null
            };
            let targetUrl = `/servicesprojects/settaskcategory/`;
            let apiRoute = targetUrl + this.id_space + "/" + this.id_project;
            this.categories.forEach(async category => {
                cfg.body = JSON.stringify({
                    category: category
                });
                await fetch(apiRoute, cfg, true).
                    then((response) => response.json()).
                    then(data => {
                        category.id = data.id;
                });
            });
        },
        deleteTask(task, displayAlert=true) {
            if (!displayAlert || confirm("You are about to delete " + task.title + "?")) {
                let tasks = this.categories[task.state].tasks;
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

        },
        deleteCategory(category) {
            if (confirm("You are about to delete " + category.title + " category. Deleting it will also delete all related tasks" )) {
                // remove tasks
                category.tasks.forEach(task => {
                    this.deleteTask(task, false);
                });
                // remove category
                this.categories.splice(this.categories.indexOf(this.categories.find(element => element.id == category.id)), 1);
                const headers = new Headers();
                headers.append('Content-Type','application/json');
                headers.append('Accept', 'application/json');
                const cfg = {
                    headers: headers,
                    method: 'POST',
                    body: null
                };
                cfg.body = JSON.stringify({
                    category: category
                });
                let targetUrl = `/servicesprojects/deletetaskcategory/`
                let apiRoute = targetUrl + this.id_space + "/" + category.id;
                fetch(apiRoute, cfg, true)
            }

        }
    }
});
</script>

<style>
    .kanban-category {
        min-height: 300px;
        min-width: 250px;
    } 
    .list-group-item {
        align-content: right;
    }
    .contentArea {
        min-height: 150px;
        max-width: 200px;
    }
    .deleteBtn {
        display:inline;
        position: relative;
        float: right;
        background-color: transparent;
        color: red;
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
    label {
        display: block;
    }
</style>

<?php include 'Modules/services/View/Servicesprojects/editscript.php';  ?>

<?php endblock(); ?> 