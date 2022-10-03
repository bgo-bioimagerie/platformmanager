<?php include 'Modules/core/View/spacelayout.php' ?>

<?php startblock('stylesheet') ?>
<script src="externals/pfm/star-rating/VueStarRating.umd.min.js"></script>
<?php endblock(); ?>

<?php startblock('content') ?>
<div class="container mt-3">
<?php if (!$data['campaign'] || !$data['campaign']['id']) { ?><div class="alert alert-warning"><?php echo RatingTranslator::WarningCampaign($lang) ?></div><?php } ?>
<?php echo $form ?>
</div>
<?php if ($data['campaign'] && $data['campaign']['id']) { ?>
<!-- TODO show ratings -->
<div id="ratings" class="mt-3 row">
    <div class="col-12 mb-3"><div class="row"><h4 class="col-4"><?php echo BookingTranslator::booking($lang); ?> [{{total.booking ? total.booking.count : 0}}]</h4><div class="col" v-if="total.booking"><rating v-model:rating="total.booking.rate" :star-size="20" :read-only="false"></rating></div>   </div></div>
    <div class="col-4 mb-3">
        <div v-for="(resource, index) in global_bookings" :key="index" class="mb-3 row">
            <div class="col-4"><strong>{{resource.resourcename}}</strong> ({{resource.count}})</div>
            <div class="col-8">
                <rating v-model:rating="resource.rate" :star-size="20" :read-only="false"></rating>
            </div>
            <div class="col-2"><button @click="show(resource.module, resource.resourcename)" type="button" class="btn btn-sm btn-outline-dark">Details</button></div>
        </div>
    </div>
    <div class="col-8">
        <div v-for="(resource, index) in bookings" :key="resource.id" class="mb-3">
            <div v-if="resource.module == module_selected && resource.resourcename == resource_selected" class="row">
            <div class="col-2"><strong>{{resource.resourcename}}</strong></div>
            <div class="col-3 form-group">
                <rating v-model:rating="resource.rate" :star-size="20" :read-only="false"></rating>
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

    <div class="col-12 mb-3"><div class="row"><h4 class="col-4"><?php echo ServicesTranslator::Projects($lang); ?> [{{total.projects ? total.projects.count : 0}}]</h4><div class="col" v-if="total.projects"><rating v-model:rating="total.projects.rate" :star-size="20" :read-only="false"></rating></div>   </div></div>
    <div class="col-4 mb-3">
        <div v-for="(resource, index) in global_projects" :key="index" class="mb-3 row">
            <div class="col-4"><strong>{{resource.resourcename}}</strong> ({{resource.count}})</div>
            <div class="col-8">
                <rating v-model:rating="resource.rate" :star-size="20" :read-only="false"></rating>
            </div>
            <div class="col-2"><button @click="show(resource.module, resource.resourcename)" type="button" class="btn btn-sm btn-outline-dark">Details</button></div>
        </div>
    </div>
    <div class="col-8">
        <div v-for="(resource, index) in projects" :key="resource.id" class="mb-3">
            <div v-if="resource.module == module_selected && resource.resourcename == resource_selected" class="row">
            <div class="col-2"><strong>{{resource.resourcename}}</strong></div>
            <div class="col-3 form-group">
                <rating v-model:rating="resource.rate" :star-size="20" :read-only="false"></rating>
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
Vue.createApp({
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
}).mount('#ratings')
</script>

<?php } ?>

<?php endblock() ?>