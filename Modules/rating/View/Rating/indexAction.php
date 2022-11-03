<?php include_once 'Modules/core/View/spacelayout.php' ?>

<?php startblock('stylesheet') ?>
<script src="externals/pfm/star-rating/VueStarRating.umd.min.js"></script>
<?php endblock(); ?>

<?php startblock('content') ?>

<div class="container" id="ratingapp">
    <div v-if="comments && ratingComments" id="ratingcomments">
    <h4>{{ratingComments[0].module}} / {{ratingComments[0].resourcename}}</h4>
    <button v-on:click="showList()" type="button" class="btn btn-primary">back</button>
    <table class="table" aria-label="list of evaluations">
        <thead><tr>
            <th scope="col">Date</th><th scope="col">User</th><th scope="col">Note</th><th scope="col">Comment</th>
        </tr></thead>
        <tbody>
            <tr v-for="rate in ratingComments">
                <td>{{rate.created_at}}</td>
                <td>{{rate.login}}</td>
                <td>{{rate.rate}}</td>
                <td>{{rate.comment}}</td>
            </tr>
        </tbody>
    </table>
    </div>
    <table v-if="!comments" id="ratinglist" class="table" aria-label="list of notes per module/resource">
        <thead><tr><th scope="col">Votes</th><th scope="col">Module</th><th scope="col">Resource</th><th scope="col">Note</th><th scope="col"></th></tr></thead>
        <tbody>
            <tr v-for="rate in ratings">
            <td>{{rate.count}}</td>
            <td>{{rate.module}}</td>
            <td>{{rate.resourcename}}[{{rate.resource}}]</td>
            <td>
            <rating v-model:rating="rate.rate" :star-size="20" :read-only="true"></rating>
            </td>
            <td><button v-on:click="showComments(rate.module, rate.resource)" type="button" class="btn btn-primary">comments</button></td>
            </tr>
        <tbody>
    </table>
</div>
<script>
Vue.createApp({
    data () {
        return {
            comments: false,
            ratingComments: [],
            ratings: <?php echo json_encode($data['stats']); ?>,
            id_space: <?php echo $context['currentSpace']['id']; ?>
        }
    },
    methods: {
        showList() {
            this.comments = false
        },
        showComments(id_module, resource) {
            let id_space = <?php echo $context['currentSpace']['id']."\n"; ?>
            console.log('show comments for ',id_space, id_module, resource)
            let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let cfg = {
                headers: headers,
                method: 'GET',
            }
            fetch(`/rating/${this.id_space}/evaluations/${id_module}/${resource}`, cfg).
            then(response => response.json()).
            then((data) => { this.ratingComments = data.ratings; this.comments = true})

        }
    },
    components: {
        rating: VueStarRating.default,
    }
}).mount('#ratingApp')
</script>

<?php endblock();
