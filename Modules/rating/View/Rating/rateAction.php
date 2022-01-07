<?php include 'Modules/core/View/spacelayout.php' ?>

<?php startblock('stylesheet') ?>
<script src="externals/pfm/star-rating/vue-star-rating/dist/VueStarRating.umd.js"></script>
<?php endblock(); ?>

<?php startblock('content') ?>

<div id="ratingEval" class="container">
<div v-if="evaluated" class="well">Thank you!</div>
<form v-if="!evaluated" class="form">
    <div v-if="msg" class="label label-danger">{{msg}}</div>
    <input type="hidden" name="module" value="<?php echo $data['rate']['module']; ?>"/>
    <input type="hidden" name="resource" value="<?php echo $data['rate']['resource']; ?>"/>
    <div class="form-group">
        <rating style="min-height: 30px" v-model="rate" :star-size="20" :read-only="false"></rating>
    </div>
    <div class="form-group">
        <label for="comment">Comment</label>
        <textarea v-model="comment" class="form-control" id="comment" name="comment" maxlength="255"></textarea>
    </div>
    <div class="form-group">
        <label for="anon">Anonymous ?</label>
        <select v-model="anon" class="form-control">
            <option value="1" selected>Yes</option>
            <option value="0">No</option>
        </select>
    </div>
    <div class="form-group">
        <button type="button" v-on:click="evaluate" class="btn btn-primary">Evaluate</button>
    </div>
</form>
</div>
<script>
var app = new Vue({
    el: '#ratingEval',
    name: 'rating',
    data () {
        return {
            evaluated: false,
            id_space: <?php echo $context['currentSpace']['id']; ?>,
            module: "<?php echo $data['rate']['module']; ?>",
            resource: <?php echo $data['rate']['resource']; ?>,
            rate: <?php echo json_encode($data['rate']['rate']); ?>,
            comment: '',
            anon: 1,
            msg: ''
        }
    },
    methods: {
        rateChanged(e) {
            this.rate = e;
        },
        evaluate() {
            //this.evaluated = true;
            let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let cfg = {
                headers: headers,
                method: 'POST',
                body: JSON.stringify({
                    'rate': this.rate,
                    'anon': this.anon,
                    'comment': this.comment
                })
            }
            fetch(`/rating/${this.id_space}/${this.module}/${this.resource}`, cfg).
            then(() => { this.evaluated = true}).
            catch(() => {this.msg = 'Sorry, something went wrong'})
        }
    },
    components: {
        rating: VueStarRating.default,
    }
})
</script>
<?php endblock(); ?>