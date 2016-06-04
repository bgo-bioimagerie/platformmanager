
<script type="text/javascript" src="externals/datepicker/js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
<script type="text/javascript" src="externals/datepicker/js/locales/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script>

<script type="text/javascript">

$('.form_date_fr').datetimepicker({
	language:  'fr',
	weekStart: 1,
	todayBtn:  1,
	autoclose: 1,
	todayHighlight: 1,
	startView: 2,
	minView: 2,
	forceParse: 0,
	format: 'dd/mm/yyyy'
});
$('.form_date_en').datetimepicker({
	language:  'us',
	weekStart: 1,
	todayBtn:  1,
	autoclose: 1,
	todayHighlight: 1,
	startView: 2,
	minView: 2,
	forceParse: 0,
	format: 'yyyy-mm-dd'
});
</script>
