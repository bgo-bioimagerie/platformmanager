<?php include 'Modules/core/View/spacelayout.php' ?>

<?php startblock('stylesheet') ?>
<script src="externals/pfm/star-rating/awesome-vue-star-rating/dist/AwesomeVueStarRating.common.js"></script>
<?php endblock(); ?>

<?php startblock('content') ?>

<div class="row" id="ratingapp">
<rating :star="2" size="2x" :disabled="false" :maxstars="5" :starsize="lg" :hasresults="true"
    :hasdescription="true" :ratingdescription="this.ratingdescription"></rating>
</div>
<script>

var app = new Vue({
    el: '#ratingapp',
    name: 'rating',
    data () {
        return {
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
    },
    components: {
        rating: AwesomeVueStarRating,
    }
})
</script>

<?php endblock();

