<!doctype html>
<?php include 'Modules/layout.php' ?>

<!-- header -->
<?php startblock('title') ?>
Platform-Manager
<?php endblock() ?>


<?php startblock('stylesheet') ?>
<?php
$headless = Configuration::get("headless");
$pmspaceheadercontent = "";
$pmspaceheadernavbar = "pm-space-navbar-no-header";
if (!$headless) {
    $pmspaceheadercontent = "pm-space-content";
    $pmspaceheadernavbar = "pm-space-navbar";
    ?>
    <link href="data/core/theme/navbar-fixed-top.css" rel="stylesheet">
    <?php
}


?>

<!-- Bootstrap core CSS -->
<link href="externals/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="Modules/core/Theme/core.css">
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/spacemenu.css' />
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/space.css' />


<script src="https://unpkg.com/marked@0.3.6"></script>
<script src="https://unpkg.com/lodash@4.16.0"></script>
<style>
body {
    font-size: 1.1em;
}
h3.panel-title {
    font-size: 1.1em;
}

.btn {
    font-size: 1em;
}

pre {
    all: unset;
}
</style>

<?php endblock() ?>


<?php
startblock('navbar');
if (!$headless) {
    require_once 'Modules/core/Controller/CorenavbarController.php';
    $navController = new CorenavbarController(new Request(array(), false));
    echo $navController->navbar();
}
endblock();
?>


<?php startblock('spacenavbar'); ?>
<?php
if (!$headless) {
    require_once 'Modules/core/Controller/CorespaceController.php';
    $spaceController = new CorespaceController(new Request(array(), false));
    echo $spaceController->navbar($id_space);
}
?>

<div class="col-md-12 col-lg-12 <?php echo $pmspaceheadercontent ?>" >
<?php endblock(); ?>



<!-- body -->
<?php startblock('content') ?>

<div id="helpdeskapp" class="col-md-12" style="background-color: #fff; height:100%">
    <div class="row">
        <!-- Message -->
        <div class="col-sm-10 col-sm-offset-1 text-center">
             <?php
        if (isset($_SESSION["message"])) {
            if (substr($_SESSION["message"], 0, 3) === "Err") {
                ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION["message"] ?>
                </div>
                <?php
            } else {
                ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION["message"] ?>
                </div>
                <?php
            }
            unset($_SESSION["message"]);
        }
        ?>
        </div>

         <!-- Form -->
         <div v-if="message" class="col-sm-10 col-sm-offset-1 text-center">
            <div class="alert alert-warning">{{message}}</div>
        </div>
        <div v-if="ticket !== null" class="col-sm-10 col-sm-offset-1 text-center">
        <div class="row">
        <button class="pull-left btn btn-primary" type="button" @click="back">Back to tickets</button>
        </div>
        <div class="row">
            <div class="col-sm-8">
            <div v-for="message in ticket.messages" :key="message.id">
                <div class="panel panel-default">
                    <div class="panel-heading">
                    <h3 class="panel-title">{{message.from}} - {{message.created_at}}</h3>
                    </div>
                    <div class="panel-body" v-html="message.md"></div>
                    <div class="panel-footer" v-if="message.type=='0'">
                        <div>
                            <div v-for="attach in message.attachements" :key="attach.id">
                            <a v-bind:href="'/corefiles/<?php echo $id_space ?>/' + attach.id_file" target="_blank" rel="noopener noreferrer" >{{attach.name_file}}</a>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" @click="reply(ticket.ticket.id, message.id)"><small>reply</small></button>
                    </div>
                </div>
            </div>
            <div ref="addToMessage">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 v-if="addType==1" class="panel-title">Add note</h3>
                    <h3 v-if="addType==0" class="panel-title">Email reply</h3>
                    </div>
                <div class="panel-body" v-html="message.body"></div>
                    <form v-if="!textPreview">
                        <label>Destination</label>
                        <input v-if="addType==0" placeholder="comma separated emails" class="form-control" v-model:value="ticket.ticket.created_by"/>
                        <div class="form-group">
                        <textarea v-model="mdText" class="form-control" rows="5">
                        </textarea>
                        <input type="file" id="mailFiles" multiple v-if="addType==0" class="form-control">Attachments</h3>
                        </div>
                    </form>
                    <div v-if="textPreview" class="panel-body" v-html="text"></div>
                </div>
                <div class="panel-footer">
                    <button type="button" class="btn btn-primary" @click="preview">Message/Preview</button>
                    <button type="button" class="btn btn-primary" v-if="addType==1" @click="save">Add [TODO]</button>
                    <button type="button" class="btn btn-primary" v-if="addType==0" @click="save">Send [TODO]</button>
                </div>
            </div>
            </div>
            <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                <h3 class="panel-title">#{{ticket.ticket.id}}: {{ticket.ticket.subject}}</h3>
                </div>
                <div class="panel-body">
                    <form class="form-horizontal">
                    <div class="form-group">
                    <label for="tstatus" class="col-sm-2">Status</label>
                    <div >
                        <select id="tstatus" class="form-control" v-on:change="updateStatus($event)" v-model:value="ticket.ticket.status">
                            <option value="0">New</option>
                            <option value="1">Open</option>
                            <option value="2">Reminder</option>
                            <option value="3">Closed</option>
                        </select>
                    </div>
                    <div v-if="ticket.ticket.status == 2">
                        <input type="date" v-model:value="ticket.ticket.reminder" class="form-control"/>
                        </div>
                    </div>
                    <div class="form-group" v-if="ticket.ticket.assigned">
                        <label for="tassign" class="col-sm-2">Assignee</label>
                        <div>
                            <input id="tassign" class="form-control" readonly v-bind:value="ticket.ticket.assigned_name"/>
                        </div>
                    </div>
                    </form>
                    <div v-if="!ticket.ticket.assigned"><button type="button" class="btn btn-primary" @click="assign"><small>Assign to myself</small></button></div>
                    <div><small>Created: {{ticket.ticket.created_at}}</small></div>
                </div>
            </div>
            </div>
        </div>
        </div>
        <div v-if="ticket === null" class="col-sm-10 col-sm-offset-1 text-center">
        <div>
            <span class="badge" @click="setMy()">My tickets</span>
            <span v-if="filter!=0" class="badge" @click="setFilter(0)">New</span>
            <span v-if="filter!=1" class="badge" @click="setFilter(1)">Open</span>
            <span v-if="filter!=2" class="badge" @click="setFilter(2)">Reminder</span>
            <span v-if="filter!=3" class="badge" @click="setFilter(3)">Closed</span>
        </div>
        <table aria-describedby="list of tickets" class="table table-striped table-sm">
            <thead class="thead-dark">
                <tr>
                <th scope="col">#</th>
                <th scope="col">Date</th>
                <th scope="col">Subject</th>
                <th scope="col">Author</th>
                <th scope="col">Status</th>
                <th scope="col">Queue</th>
                <th scope="col">Assigned</th>
                </tr>
            </thead>
            <tbody>
            <tr v-for="ticket in tickets" :key="ticket.id">
               <td  @click="fetchTicket(ticket.id)"><button type="button" class="btn btn-primary">{{ticket.id}}</button></td>
               <td>{{ticket.created_at}}</td>
               <td>{{ticket.subject}}</td>
               <td>{{ticket.created_by}}</td>
               <td>{{status(ticket.status)}}</td>
               <td>{{ticket.queue}}</td>
               <td>{{ticket.assigned_name}}</td>
            </tr>
            </tbody>
        </table>
        </div>

    </div>
</div>
<script>

var app = new Vue({
    el: '#helpdeskapp',
    data () {
        return {
            filter: 0,  // ticket status filter
            addType: 1,  // note
            my: false,
            message: '',
            tickets: [],
            ticket: null,
            textPreview: false,
            text: '',
            mdText: ''
        }
    },
    created () { this.fetchTickets() },
    methods: {
        updateStatus(event) {
            console.log('status', event.target.value);
            let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let cfg = {
                headers: headers,
                method: 'POST',
            }
            fetch(`/helpdesk/<?php echo $id_space ?>/${this.ticket.ticket.id}/status/${event.target.value}`, cfg).
            then(() => {
                console.debug('ticket updated')
            })
        },
        setMy() {
            this.my = !this.my;
            this.fetchTickets();
        },
        setFilter(f) {
            this.filter = f;
            this.fetchTickets();
        },
        assign() {
            let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let cfg = {
                headers: headers,
                method: 'POST'
            }
            fetch(`/helpdesk/<?php echo $id_space ?>/${this.ticket.ticket.id}/assign`, cfg).
            then(() => this.fetchTicket(this.ticket.ticket.id))
        },
        back() {
            this.ticket = null;
            this.fetchTickets();
        },
        save() {
            console.debug('save ticket', this.ticket.ticket);
            let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let cfg = {
                headers: headers,
                method: 'POST',
                body: JSON.stringify({
                    'type': this.addType,
                    'body': this.mdText,
                    'to': this.ticket.ticket.created_by
                })
            }
            if(this.addType == 0) {
                const inputFiles = document.getElementById('mailFiles');
                let f = new FormData();
                f.append('type', this.addType);
                f.append('body', this.mdText);
                f.append('to', this.ticket.ticket.created_by);
                let fileIndex = 0;
                for (const file of inputFiles.files) {
                    f.append('file' + fileIndex,file,file.name)
                    fileIndex++;
                }
                headers = new Headers()
                headers.append('Accept', 'application/json')
                cfg = {
                    headers: headers,
                    method: 'POST',
                    body: f
                }
            }
            fetch(`/helpdesk/<?php echo $id_space ?>/${this.ticket.ticket.id}`, cfg).
            then(response => response.json()).
            then(() => {
                this.fetchTicket(this.ticket.ticket.id)
                this.addType = 1;
                this.mdText = '';
                this.text = '';
            })

        },
        reply(ticket, message) {
            this.addType = 0;
            let body = '';
            for(let i=0;i<this.ticket.messages.length;i++) {
                if(this.ticket.messages[i].id == message) {
                    let lines = this.ticket.messages[i].body.split("\n");
                    let resp = '';
                    lines.forEach(line => {
                        resp += '>'+line;
                    })
                    body = resp;
                }
                this.mdText = "\n\n"+body;
            }
            this.goto('addToMessage')
        },
        goto(refName) {
            let element = this.$refs[refName];
            let top = element.offsetTop;
            window.scrollTo(0, top);
        },
        togglePreview () {
            this.textPreview = !this.textPreview;
        },
        preview () {
            this.text = marked( this.mdText, { sanitize: true });
            this.togglePreview();
        },
        status (id) {
            switch (parseInt(id)) {
                case 0:
                    return 'New';
                case 1:
                    return 'Open';
                case 2:
                    return 'Reminder';
                case 3:
                    return 'Closed';
                default:
                    return 'Unknown';
            }
        },
        fetchTicket (id) {
            let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let cfg = {
                headers: headers
            }
            fetch(`/helpdesk/<?php echo $id_space ?>/${id}`, cfg).
            then((response) => response.json()).
            then(data => {
                for(let i=0;i<data.messages.length;i++) {
                    data.messages[i].md = marked(data.messages[i].body, { sanitize: true }) || "";
                }
                this.ticket = data;
                console.debug('get ticket', data);
            })    
        },
        fetchTickets () {
            let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let cfg = {
                headers: headers
            }
            let params = '';
            if(this.my) {
                params = new URLSearchParams({
                    mine: 1
                })
            }
            fetch('/helpdesk/<?php echo $id_space ?>/list/' + this.filter + '?' + params, cfg).
            then((response) => response.json()).
            then(data => {
                this.tickets = data.tickets;
                console.debug('get tickets', data);
            })
        }
    }
})
</script>
<?php
endblock();

