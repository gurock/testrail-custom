<?php
$min_width = 250;
foreach ($statuses as $status)
{
	$min_width += 100;
}

$min_width = max(960, $min_width);

$header = array(
	'project' => $project,
	'report' => $report,
	'meta' => $report_obj->get_meta(),
	'min_width' => $min_width,
	'css' => array(
		'styles/reset.css' => 'all',
		'styles/view.css' => 'all',
		'styles/print.css' => 'print'
	),
	'js' => array(
		'js/jquery.js',
		'js/highcharts.js'
	)
);

$GI->load->view('report_plugins/layout/header', $header);
?>

<?php
$stats = obj::create();
$stats->passed_count = 0;
$stats->retest_count = 0;
$stats->failed_count = 0;
$stats->untested_count = 0;
$stats->blocked_count = 0;
$stats->custom_status1_count = 0;
$stats->custom_status2_count = 0;
$stats->custom_status3_count = 0;
$stats->custom_status4_count = 0;
$stats->custom_status5_count = 0;
$stats->custom_status6_count = 0;
$stats->custom_status7_count = 0;

foreach ($runs as $r)
{
	$stats->passed_count += $r->passed_count;
	$stats->retest_count += $r->retest_count;
	$stats->failed_count += $r->failed_count;
	$stats->untested_count += $r->untested_count;
	$stats->blocked_count += $r->blocked_count;
	$stats->custom_status1_count += $r->custom_status1_count;
	$stats->custom_status2_count += $r->custom_status2_count;
	$stats->custom_status3_count += $r->custom_status3_count;
	$stats->custom_status4_count += $r->custom_status4_count;
	$stats->custom_status5_count += $r->custom_status5_count;
	$stats->custom_status6_count += $r->custom_status6_count;
	$stats->custom_status7_count += $r->custom_status7_count;
	tests::set_status_percents($r);
}

tests::set_status_percents($stats);
?>

<? $GI->load->view('report_plugins/charts/defaults') ?>

<?php
$temp = array();
$temp['stats'] = $stats;
$report_obj->render_view('index/charts/status', $temp);
?>

<h1 class="top"><img class="right noPrint" src="%RESOURCE%:images/icons/help.png" width="16" height="16" alt="" title="<?= lang('reports_tpr_runs_header_info') ?>" /><?= lang('reports_tpr_runs_header') ?></h1>
<? if ($runs): ?>
	<?php
	$temp = array();
	$temp['runs'] = $runs;
	$temp['run_rels'] = $run_rels;
	$temp['show_links'] = true;
	$GI->load->view('report_plugins/runs/groups', $temp);
	?>
	<? $run_count_partial = count($runs) ?>
	<? if ($run_count > $run_count_partial): ?>
		<p class="partial">
			<?= langf('reports_tpr_runs_more',
			$run_count - 
			$run_count_partial) ?>
		</p>
	<? endif ?>
<? else: ?>
	<p><?= lang('reports_tpr_runs_empty') ?></p>
<? endif ?>

<? if ($types_include): ?>
	<h1><img class="right noPrint" src="%RESOURCE%:images/icons/help.png" width="16" height="16" alt="" title="<?= lang('reports_tpr_types_header_info') ?>" /><?= lang('reports_tpr_types_header') ?></h1>
	<? if ($types): ?>
		<?php
		$temp = array();
		$temp['header'] = lang('reports_tpr_types_item');
		$temp['items'] = $types;
		$temp['results'] = $types_results;
		$temp['statuses'] = $statuses;
		$report_obj->render_view('index/table', $temp);
		?>
	<? else: ?>
		<p><?= lang('reports_tpr_types_empty') ?></p>
	<? endif ?>
<? endif ?>

<? if ($priorities_include): ?>
<h1><img class="right noPrint" src="%RESOURCE%:images/icons/help.png" width="16" height="16" alt="" title="<?= lang('reports_tpr_priorities_header_info') ?>" /><?= lang('reports_tpr_priorities_header') ?></h1>
	<? if ($priorities): ?>
		<?php
		$temp = array();
		$temp['header'] = lang('reports_tpr_priorities_item');
		$temp['items'] = $priorities;
		$temp['results'] = $priorities_results;
		$temp['statuses'] = $statuses;
		$report_obj->render_view('index/table', $temp);
		?>
	<? else: ?>
		<p><?= lang('reports_tpr_priorities_empty') ?></p>
	<? endif ?>
<? endif ?>

<?php
$temp = array();
$temp['report'] = $report;
$temp['meta'] = $report_obj->get_meta();
$temp['show_options'] = true;
$temp['show_report'] = true;
$GI->load->view('report_plugins/layout/footer', $temp);
?>
