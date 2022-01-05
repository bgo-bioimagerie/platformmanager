<?php include 'Modules/core/View/spacelayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="row" id="ratingapp">
</div>
<script>

var app = new Vue({
    el: '#ratingapp',
    data () {
        return {
            ratings: <?php echo json_encode($data['stats']); ?>,
        }
    },
    methods: {
        fake() {
            console.log('to be done')
        },
    }
})
</script>

<?php endblock();

