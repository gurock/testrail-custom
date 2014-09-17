<? $statuses = $GI->cache->get_objects('status') ?>

<?php
$temp = array();
$temp['stats'] = $stats;
$temp['statuses'] = $statuses;
$GI->load->view('report_plugins/charts/status', $temp);
?>
