<!doctype html>
<?php include 'Modules/layout.php' ?>


<?php startblock('stylesheet') ?>
<script src="externals/node_modules/marked/marked.min.js"></script>
<script src="externals/node_modules/lodash/lodash.min.js"></script>
<style>


.btn {
    font-size: 1em;
}

pre {
    all: unset;
}

blockquote {
    all: unset;
}

.selection {
    background-color: #d9edf7;
}
</style>

<?php endblock() ?>


<!-- body -->
<?php startblock('content') ?>

<div id="helpdeskapp" style="background-color: #fff; height:100%">
    <div class="row">
        <!-- Form -->
        <div v-if="message" class="col-12 text-center">
            <div class="alert alert-warning">{{message}}</div>
        </div>
        <div class="col-2 text-center" style="background-color: <?php echo $menuInfo["color"] ?> ; color: <?php echo $menuInfo["txtcolor"] ?>">
            <div @click="newTicket()">Create</div>
            <div @click="setMy()">{{ my ? "Show all tickets": "Show my tickets"}}</div>
            <div v-bind:class="filter==0 ? 'selection':''"  @click="setFilter(0)">New {{unread["s0"]}}</div>
            <div v-bind:class="filter==1 ? 'selection':''" @click="setFilter(1)">Open {{unread["s1"]}}</div>
            <div v-bind:class="filter==2 ? 'selection':''" @click="setFilter(2)">Reminder {{unread["s2"]}}</div>
            <div v-bind:class="filter==3 ? 'selection':''" @click="setFilter(3)">Closed {{unread["s3"]}}</div>
            <div v-bind:class="filter==4 ? 'selection':''" @click="setFilter(4)">Spam</div>
            <?php
            if ($role > CoreSpace::$MANAGER) {
            ?>
            <div @click="getSettings()">Settings</div>
            <?php
            }
            ?>
        </div>
        <div v-if="settings" class="col-10">
            <div class="form">
                <div class="form-check">
                    <input class="form-check-input" v-model="preferences.notifyNew" type="checkbox" value="" id="notifyNew">
                    <label class="form-check-label" for="notifyNew">
                        Notify on new tickets
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" v-model="preferences.notifyAssignedUpdate" type="checkbox" value="" id="notifyAssignedUpdate">
                    <label class="form-check-label" for="notifyAssignedUpdate">
                        Notify on assigned tickets update
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" v-model="preferences.notifyAllUpdate" type="checkbox" value="" id="notifyAllUpdate">
                    <label class="form-check-label" for="notifyAllUpdate">
                        Notify on all tickets update
                    </label>
                </div>
                    <button @click="setSettings" class="btn btn-primary">Save</button>
            </div>
        </div>
        <div v-if="!settings && ticket !== null" class="col-10 text-center">
            <div class="row">
                <div class="col-8">
                    <div v-for="message in ticket.messages" :key="message.id">
                        <div class="card">
                            <div class="card-header">
                            <div class="card-title">{{message.from}} - {{message.created_at}}</div>
                            </div>
                            <div class="card-body text-start" v-html="message.md"></div>
                            <div class="card-footer" v-if="message.type=='0'">
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
                        <div class="card">
                            <div class="card-header">
                                <div v-if="addType==1" class="card-title">Add note</div>
                                <div v-if="addType==0 && ticket.ticket.id > 0" class="card-title">Email reply</div>
                                <div v-if="addType==0 && ticket.ticket.id == 0" class="cart-title">New ticket</div>
                                </div>
                            <div class="card-body" v-html="message.body"></div>
                                <form v-if="!textPreview">
                                    <div v-if="ticket.ticket.id==0" class="form-group">
                                        <label class="form-label">Subject</label>
                                        <input class="form-control" v-model="ticket.ticket.subject"/>
                                    </div>
                                    <div v-if="addType==0" class="form-group">
                                        <label class="form-label">Destination</label>
                                        <input placeholder="comma separated emails" class="form-control" v-model:value="ticket.ticket.created_by"/>
                                    </div>
                                    <div class="form-group">
                                    <textarea v-model="mdText" class="form-control" rows="5">
                                    </textarea>
                                    <label class="form-label">Attachments</label>
                                    <input type="file" id="mailFiles" multiple v-if="addType==0" class="form-control">
                                    </div>
                                </form>
                                <div v-if="textPreview" class="card-body" v-html="text"></div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-primary" @click="preview">Message/Preview</button>
                                <button type="button" class="btn btn-primary" v-if="addType==1" @click="save">Add</button>
                                <button type="button" class="btn btn-primary" v-if="addType==0 && !textPreview" @click="save">Send</button>
                                <button type="button" class="btn btn-primary" v-if="addType==0 && !textPreview" @click="cancelReply">Cancel</button>
                            </div>
                        </div>
                    </div>
                <div class="col-4">
                    <div class="card">
                        <div class="card-header">
                        <div class="cart-title">#{{ticket.ticket.id}}: {{ticket.ticket.subject}}</div>
                        </div>
                        <div class="card-body">
                            <form class="form-horizontal">
                            <div class="form-group">
                            <label for="tstatus" class="">Status</label>
                            <div >
                                <select id="tstatus" class="form-select" v-on:change="updateStatus($event)" v-model:value="ticket.ticket.status">
                                    <option value="0">New</option>
                                    <option value="1">Open</option>
                                    <option value="2">Reminder</option>
                                    <option value="3">Closed</option>
                                    <option value="4">Spam</option>
                                </select>
                            </div>
                            <div v-if="ticket.ticket.status == 2">
                                <input type="date" v-model:value="ticket.ticket.reminder" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-group" v-if="ticket.ticket.assigned">
                                <label for="tassign" class="">Assignee</label>
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
        <div v-if="!settings && ticket === null" class="col-10 text-center">
            <div>
                <button v-if="offset > 0" class="btn btn-primary" @click="prevPage()">prev</button>
                <button class="btn btn-primary" @click="nextPage()">next</button>
            </div>
            <table aria-describedby="list of tickets" class="table  table-sm">
                <thead class="thead-dark">
                    <tr>
                    <th scope="col"></th>
                    <th scope="col">#{{current_filter}}</th>
                    <th scope="col">Date</th>
                    <th scope="col">Subject</th>
                    <th scope="col">Author</th>
                    <th scope="col">Status</th>
                    <th scope="col">Queue</th>
                    <th scope="col">Assigned</th>
                    </tr>
                </thead>
                <tbody>
                <tr><td><input type="checkbox" v-bind:checked="selectAll" @click="selectTicket(null)"/></td><td></td><td><button @click="spamSelected()" class="btn btn-warning">Spam selected</button></td></tr>
                <tr v-for="ticket in tickets" :key="ticket.id" v-bind:class="ticket.unread=='1' ? 'alert alert-warning':''">
                <td><input @click="selectTicket(ticket.id)" v-bind:checked="ticket && ticket.selected" type="checkbox"/></td>
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
            <div>
                <button v-if="offset > 0" class="btn btn-primary" @click="prevPage()">prev</button>
                <button class="btn btn-primary" @click="nextPage()">next</button>
            </div>
        </div>
    </div>
</div>
<script>

var app = new Vue({
    el: '#helpdeskapp',
    data () {
        return {
            selectAll: false,
            current_filter: 'New' ,
            filter: 0,  // ticket status filter
            addType: 1,  // note
            my: false,
            message: '',
            tickets: [],
            ticket: null,
            textPreview: false,
            text: '',
            mdText: '',
            settings: false,
            preferences: {
                notifyNew: false,
                notifyAssignedUpdate: false,
                notifyAllUpdate: false
            },
            offset: 0,
            limit: 50,
            unread: {}
        }
    },
    created () { this.fetchTickets(); <?php if($ticket) {
        echo "this.fetchTicket(".$ticket['id'].")";
    } ?> },
    methods: {
        spam(id) {
            return new Promise((resolve, reject) => {
                let headers = new Headers()
                headers.append('Content-Type','application/json')
                headers.append('Accept', 'application/json')
                let cfg = {
                    headers: headers,
                    method: 'POST',
                }
                fetch(`/helpdesk/<?php echo $id_space ?>/${id}/status/4`, cfg).
                then(() => {
                    resolve()
                }).catch(err => {
                    console.error('failed to spam', id)
                    reject(err)
                })
            })
        },
        spamSelected() {
            this.tickets.forEach(async (ticket) => {
                if (ticket && ticket.selected) {
                    try {
                        await this.spam(ticket.id)
                    } catch(err) {
                    }
                }
            })
            this.fetchTickets();
        },
        selectTicket(id) {
            if (id === null) {
                let tickets = [...this.tickets]
                if (this.select) {
                    tickets.forEach((ticket) => {
                        ticket.selected = false;
                    })
                } else {
                    tickets.forEach((ticket) => {
                        ticket.selected = true;
                    })
                }
                this.select = !this.select
                this.tickets = tickets
                return
            }
            let tickets = [...this.tickets]
            tickets.forEach((ticket) => {
                if(ticket.id === id) {
                    ticket.selected = !ticket.selected;
                }
            })
            this.tickets = tickets
        },
        nextPage() {
            if(this.tickets.length == 0) {
                return;
            }
            this.offset += this.limit;
            this.fetchTickets();
        },
        prevPage() {
            if(this.offset - this.limit < 0) {
                this.offset = 0;
            } else {
                this.offset -= this.limit;
            }
            this.fetchTickets();
        },
        getSettings () {
            this.settings = true;
            let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let cfg = {
                headers: headers,
                method: 'GET',
            }
            fetch(`/helpdesk/<?php echo $id_space ?>/settings`, cfg).
            then(response => response.json()).
            then((data) => {
                this.preferences = {
                    notifyNew: data.settings.notifyNew,
                    notifyAssignedUpdate: data.settings.notifyAssignedUpdate,
                    notifyAllUpdate: data.settings.notifyAllUpdate
                }
            })
        },
        setSettings () {
            this.settings = true;
            let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let cfg = {
                headers: headers,
                method: 'POST',
                body: JSON.stringify({
                    'settings': this.preferences
                })
            }
            fetch(`/helpdesk/<?php echo $id_space ?>/settings`, cfg).
            then(() => { this.message = "Settings updated"})
        },
        updateStatus(event) {
            let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let cfg = {
                headers: headers,
                method: 'POST',
            }
            fetch(`/helpdesk/<?php echo $id_space ?>/${this.ticket.ticket.id}/status/${event.target.value}`, cfg).
            then(() => {
                if(event.target.value == 4) {
                    this.filter = 0;
                    this.ticket = null;
                    this.fetchTickets();
                }
            })
        },
        setMy() {
            this.settings = false;
            this.my = !this.my;
            this.fetchTickets();
        },
        setFilter(f) {
            if(f==0) {
                this.current_filter = 'New'
            } else if(f==1) {
                this.current_filter = 'Open'
            } else if(f==2) {
                this.current_filter = 'Reminder'
            } else if(f==3) {
                this.current_filter = 'Closed'
            } else if (f==4) {
                this.current_filter = 'Spam'
            }
            this.settings = false;
            this.filter = f;
            this.ticket = null;
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
        cancelReply() {
                this.addType = 1;
                this.mdText = '';
                this.text = '';
        },
        save() {
            let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let data = {
                    'type': this.addType,
                    'body': this.mdText,
                    'to': this.ticket.ticket.created_by
                }
            let cfg = {
                headers: headers,
                method: 'POST',
                body: JSON.stringify(data)
            }
            if(this.addType == 0) {
                const inputFiles = document.getElementById('mailFiles');
                let f = new FormData();
                f.append('type', this.addType);
                f.append('body', this.mdText);
                f.append('to', this.ticket.ticket.created_by);
                if (this.ticket.ticket.id == 0) {
                    f.append('subject', this.ticket.ticket.subject);
                }
                let fileIndex = 0;
                for (const inputfile of inputFiles.files) {
                    f.append('file' + fileIndex,inputfile,inputfile.name)
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
            then((data) => {
                this.fetchTicket(data.ticket.id)
                this.addType = 1;
                this.mdText = '';
                this.text = '';
            })

        },
        newTicket() {
            this.addType = 0;
            this.mdText = '';
            this.text = '';
            this.ticket = {
                'id_space': <?php echo $id_space ?>,
                'ticket': {
                'id': 0,
                'subject': '',
                'created_by': ''
                },
                'messages': [],

            };
        },
        reply(ticket, message) {
            this.addType = 0;
            let body = '';
            for(let i=0;i<this.ticket.messages.length;i++) {
                if(this.ticket.messages[i].id == message) {
                    let lines = this.ticket.messages[i].body.split("\n");
                    let resp = '';
                    lines.forEach(line => {
                        resp += '>'+line + "\n";
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
                case 4:
                    return 'Spam';
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
            })    
        },
        fetchTickets () {
            let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let cfg = {
                headers: headers
            }
            let params = new URLSearchParams({
                offset: this.offset,
                limit: this.limit
            });
            if(this.my) {
                params = new URLSearchParams({
                    mine: 1,
                    offset: this.offset,
                    limit: this.limit
                })
            }
            fetch('/helpdesk/<?php echo $id_space ?>/list/' + this.filter + '?' + params , cfg).
            then((response) => response.json()).
            then(data => {
                this.tickets = data.tickets;
            })
            this.fetchUnread();
        },
        fetchUnread() {
            let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let cfg = {
                headers: headers
            }
            let params = new URLSearchParams({
                offset: this.offset,
                limit: this.limit
            });
            if(this.my) {
                params = new URLSearchParams({
                    mine: 1,
                    offset: this.offset,
                    limit: this.limit
                })
            }
            fetch('/helpdesk/<?php echo $id_space ?>/unread' , cfg).
            then((response) => response.json()).
            then(data => {
                let unreads = {}
                data.unread.forEach(unread => {
                    unreads["s"+unread.status] = `(${unread.total})`
                });
                this.unread = unreads;
            })            
        }
    }
})
</script>
<?php endblock(); ?>

