<?php include 'Modules/core/View/spacelayout.php' ?>
<?php
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
?>
<?php startblock('stylesheet') ?>
<script src="externals/pfm/star-rating/vue-star-rating/dist/VueStarRating.umd.js"></script>
<?php endblock(); ?>

<?php startblock('content') ?>

<div id="ratingEval" class="container">

<form class="form">
    <div v-if="msg" class="label label-danger">{{msg}}</div>
    <?php if($data['resources']) { echo '<h4>'.BookingTranslator::booking($lang).'</h4>'; } ?>
    <div class="row">
        <div v-for="(resource, index) in resources" :key="resource.vid" class="mb-3 col-6">
            <input type="hidden" name="module" value="booking"/>
            <input type="hidden" name="resource" value="resource.id"/>
            <h4>{{resource.name}}</h4>
            <div class="form-group">
                <rating style="min-height: 30px" v-model="resource.rate" :star-size="20" :read-only="false"></rating>
            </div>
            <div class="form-group">
                <label for="comment">Comment</label>
                <textarea v-model="resource.comment" class="form-control" id="comment" name="comment" maxlength="255"></textarea>
            </div>
            <div class="form-group">
                <label for="anon">Anonymous ?</label>
                <select v-model="resource.anon" class="form-control">
                    <option value="1" selected>Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="form-group">
                <button type="button" v-on:click="evaluate(index, 'booking')" class="btn btn-primary">Evaluate</button>
            </div>
        </div>
    </div>
    <?php if($data['projects']) { echo '<h4>'.ServicesTranslator::Projects($lang).'</h4>'; } ?>
    <div class="row">
        <div v-for="(resource, index) in projects" :key="resource.vid" class="mb-3 col-6">
            <input type="hidden" name="module" value="projects"/>
            <input type="hidden" name="resource" value="resource.id"/>
            <h4>{{resource.name}}</h4>
            <div class="form-group">
                <rating style="min-height: 30px" v-model="resource.rate" :star-size="20" :read-only="false"></rating>
            </div>
            <div class="form-group">
                <label for="comment">Comment</label>
                <textarea v-model="resource.comment" class="form-control" id="comment" name="comment" maxlength="255"></textarea>
            </div>
            <div class="form-group">
                <label for="anon">Anonymous ?</label>
                <select v-model="resource.anon" class="form-control">
                    <option value="1" selected>Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="form-group">
                <button type="button" v-on:click="evaluate(index, 'projects')" class="btn btn-primary">Evaluate</button>
            </div>
        </div>
    </div>
</form>
</div>
<script>
var app = new Vue({
    el: '#ratingEval',
    name: 'rating',
    data () {
        return {
            id_space: <?php echo $context['currentSpace']['id']; ?>,
            id_campaign: <?php echo $campaign; ?>,
            resources: <?php echo json_encode($data['resources']) ?>,
            projects: <?php echo json_encode($data['projects']) ?>,
            msg: ''
        }
    },
    methods: {
        evaluate(index, kind) {
            let rate = null;
            switch (kind) {
                case 'booking':
                    rate = this.resources[index];
                    break;
                case 'projects':
                    rate = this.projects[index];
                default:
                    break;
            }

            console.log(rate);
            if(!rate) {
                console.error('rate not found')
                return
            }
            let headers = new Headers()
            headers.append('Content-Type','application/json')
            headers.append('Accept', 'application/json')
            let cfg = {
                headers: headers,
                method: 'POST',
                body: JSON.stringify(rate)
            }
            fetch(`/rating/${this.id_space}/campaign/${this.id_campaign}/rate`, cfg).
            then(resp => {
                return resp.json()
            }).
            then(data => {
                console.log(data)
                switch (kind) {
                    case 'booking':
                        this.resources[index].id = data.rate.id;
                        break;
                    case 'projects':
                        this.projects[index].id = data.rate.id;
                    default:
                        break;
                }
            }).
            catch((err) => { console.error(err); this.msg = 'Sorry, something went wrong'})
        }
    },
    components: {
        rating: VueStarRating.default,
    }
})
</script>
<?php endblock(); ?>