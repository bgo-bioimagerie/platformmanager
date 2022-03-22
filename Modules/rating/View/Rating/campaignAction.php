<?php include 'Modules/core/View/spacelayout.php' ?>

<?php startblock('stylesheet') ?>
<script src="externals/pfm/star-rating/vue-star-rating/dist/VueStarRating.umd.js"></script>
<?php endblock(); ?>

<?php startblock('content') ?>
<div class="container mt-3">
<?php echo $form ?>
</div>
<!-- TODO show ratings -->
<div id="ratings" class="mt-3 row">

    <div class="col-12 mb-3"><div class="row"><h4 class="col-4"><?php echo BookingTranslator::booking($lang); ?> [{{total.booking.count}}]</h4><div class="col"><rating style="min-height: 30px" v-model="total.booking.rate" :star-size="20" :read-only="false"></rating></div>   </div></div>
    <div class="col-4 mb-3">
        <div v-for="(resource, index) in global_bookings" :key="index" class="mb-3 row">
            <div class="col-4"><strong>{{resource.resourcename}}</strong> ({{resource.count}})</div>
            <div class="col-8">
                <rating style="min-height: 30px" v-model="resource.rate" :star-size="20" :read-only="false"></rating>
            </div>
            <div class="col-2"><button @click="show(resource.module, resource.resourcename)" type="button" class="btn btn-sm btn-outline-dark">Details</button></div>
        </div>
    </div>
    <div class="col-8">
        <div v-for="(resource, index) in bookings" :key="resource.id" class="mb-3">
            <div v-if="resource.module == module_selected && resource.resourcename == resource_selected" class="row">
            <div class="col-2"><strong>{{resource.resourcename}}</strong></div>
            <div class="col-3 form-group">
                <rating style="min-height: 30px" v-model="resource.rate" :star-size="20" :read-only="false"></rating>
            </div>
            <div class="col-4 form-group">
                <div>{{resource.comment}}</div>
            </div>
            <div class="col-2 form-group">
                <div>{{resource.login}}</div>
            </div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-3"><div class="row"><h4 class="col-4"><?php echo ServicesTranslator::Projects($lang); ?> [{{total.projects.count}}]</h4><div class="col"><rating style="min-height: 30px" v-model="total.projects.rate" :star-size="20" :read-only="false"></rating></div>   </div></div>
    <div class="col-4 mb-3">
        <div v-for="(resource, index) in global_projects" :key="index" class="mb-3 row">
            <div class="col-4"><strong>{{resource.resourcename}}</strong> ({{resource.count}})</div>
            <div class="col-8">
                <rating style="min-height: 30px" v-model="resource.rate" :star-size="20" :read-only="false"></rating>
            </div>
            <div class="col-2"><button @click="show(resource.module, resource.resourcename)" type="button" class="btn btn-sm btn-outline-dark">Details</button></div>
        </div>
    </div>
    <div class="col-8">
        <div v-for="(resource, index) in projects" :key="resource.id" class="mb-3">
            <div v-if="resource.module == module_selected && resource.resourcename == resource_selected" class="row">
            <div class="col-2"><strong>{{resource.resourcename}}</strong></div>
            <div class="col-3 form-group">
                <rating style="min-height: 30px" v-model="resource.rate" :star-size="20" :read-only="false"></rating>
            </div>
            <div class="col-4 form-group">
                <div>{{resource.comment}}</div>
            </div>
            <div class="col-2 form-group">
                <div>{{resource.login}}</div>
            </div>
            </div>
        </div>
    </div>

</div>



<script>
var app = new Vue({
    el: '#ratings',
    name: 'rating',
    data () {
        return {
            module_selected: null,
            resource_selected: null,
            id_space: <?php echo $context['currentSpace']['id']; ?>,
            id_campaign: <?php echo $data['campaign']['id'] ?? 0; ?>,
            bookings: <?php echo json_encode($data['bookings']) ?>,
            global_bookings: <?php echo json_encode($data['global_bookings']) ?>,
            projects: <?php echo json_encode($data['projects']) ?>,
            global_projects: <?php echo json_encode($data['global_projects']) ?>,
            total: <?php echo json_encode($data['total']) ?>,
            msg: ''
        }
    },
    methods: {
        show(selected_module, selected_resource) {
            this.module_selected = selected_module
            this.resource_selected = selected_resource
        }
    },
    components: {
        rating: VueStarRating.default,
    }
})
</script>
<?php endblock() ?>