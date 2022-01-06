<?php include 'Modules/core/View/spacelayout.php' ?>

<?php startblock('stylesheet') ?>
<script src="externals/pfm/star-rating/awesome-vue-star-rating/dist/AwesomeVueStarRating.umd.js"></script>
<?php endblock(); ?>

<?php startblock('content') ?>

<div class="container" id="ratingapp">
    <div v-if="comments" id="ratingcomments">
    <button v-on:click="showList()" type="button" class="btn btn-primary">back</button>
    </div>
    <table v-if="!comments" id="ratinglist" class="table" aria-label="list of notes per module/resource">
        <thead><tr><th scope="col">Votes</th><th scope="col">Module</th><th scope="col">Resource</th><th scope="col">Note</th><th scope="col"></th></tr></thead>
        <tbody>
            <tr v-for="rate in ratings">
            <td>{{rate.count}}</td>
            <td>{{rate.module}}</td>
            <td>{{rate.resourcename}}[{{rate.resource}}]</td>
            <td>
                <rating :star="rate.rate" :starsize="'1x'" :disabled="true" :maxstars="5" :starsize="'md'" :hasresults="true"
                    :hasdescription="true" :ratingdescription="this.ratingdescription"></rating>
            </td>
            <td><button v-on:click="showComments(rate.module, rate.resource)" type="button" class="btn btn-primary">comments</button></td>
            </tr>
        <tbody>
    </table>
</div>
<script>

var app = new Vue({
    el: '#ratingapp',
    name: 'rating',
    data () {
        return {
            comments: false,
            ratingComments: [],
            ratings: <?php echo json_encode($data['stats']); ?>,
            ratingdescription: [
                {
                    text: "Poor",
                    class: "star-poor"
                },
                {
                    text: "Below Average",
                    class: "star-belowAverage"
                },
                {
                    text: "Average",
                    class: "star-average"
                },
                {
                    text: "Good",
                    class: "star-good"
                },
                {
                    text: "Excellent",
                    class: "star-excellent"
                }
            ],

        }
    },
    methods: {
        fake() {
            console.log('to be done')
        },
        showList() {
            this.comments = false
        },
        showComments(id_module, resource) {
            let id_space = <?php echo $context['currentSpace']['id']."\n"; ?>
            console.log('show comments for ',id_space, id_module, resource)
            this.comments = true
        }
    },
    components: {
        rating: AwesomeVueStarRating,
    }
})
</script>

<?php endblock();

