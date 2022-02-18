<?php include 'Modules/core/View/spacelayout.php' ?>

    
<?php startblock('content') ?>
<div id="logs" class="row container">
    <div class="col-sm-12">
       <input :value="dateToYYYYMMDD(fromFilter)"
                   @input="fromFilter = $event.target.valueAsDate" type="date"/>
       <input :value="dateToYYYYMMDD(toFilter)"
                   @input="toFilter = $event.target.valueAsDate" type="date"/>
        <button v-on:click="refresh" type="button" class="btn btn-primary">Refresh</button>
    </div>
    <div class="col-sm-12">
      <table class="table" aria-label="list of logs">
      <thead><tr><th scope="date">Date</th><th scope="author">Author</th><th scope="message">Message</th></tr></thead>
      <tbody>
          <tr v-for="item in logs" :key="item.id">
              <td>{{ item.created_at }}</td>
              <td>{{ item.user }}</td>
              <td>{{ item.message }}</td>
          </tr>
      </tbody>
      </table>
  </div>
</div>



<script>
let today = new Date();
let yesterday = new Date();
yesterday.setDate( today.getDate() - 1);
var app = new Vue({
  el: '#logs',
  data: {
    logs: <?php echo json_encode($logs);?>,
    fromFilter: yesterday,
    toFilter: today
  },
  mounted() {
    this.refresh()
  },
  methods: {
    dateToYYYYMMDD(d) {
      // alternative implementations in https://stackoverflow.com/q/23593052/1850609
    	return d && new Date(d.getTime()-(d.getTimezoneOffset()*60*1000)).toISOString().split('T')[0]
    },
    refresh() {
        let start = Math.round(this.fromFilter / 1000);
        let end = Math.round(this.toFilter / 1000);
        //http://localhost:4000/corespacehistory/6
        let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let cfg = {
                headers: headers,
                method: 'GET',
            }
            fetch(`/corespacehistory/<?php echo $id_space ?>?start=${start}&end=${end}`, cfg).
            then((response) => response.json()).
            then(data => {
                this.logs = data.logs
            })
    }
  }
})
</script>
<?php endblock(); ?>