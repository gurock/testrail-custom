<table class="grid">
	<colgroup>
		<col></col>
		<? foreach ($statuses as $status): ?>
			<col style="width: 100px"></col>
		<? endforeach ?>
		<col style="width: 75px"></col>
	</colgroup>
	<tr class="header">
		<th><?=h( $header )?></th>
		<? foreach ($statuses as $status): ?>
			<th style="text-align: right">
				<span class="statusBox" style="<?= tests::get_status_box_colors($status->color_dark) ?>">&nbsp;&nbsp;</span>
				<?=h( $status->label ) ?>
			</th>
		<? endforeach ?>
		<th style="text-align: right"><?= lang('reports_tpr_table_total') ?></th>
	</tr>
	<? arr::alternator() ?>
	<? foreach ($items as $item): ?>
		<? $alt = arr::alternator('odd', 'even') ?>
		<tr class="<?= $alt ?>">
			<td><?=h( $item->name )?></td>
			<? $total = 0 ?>
			<? foreach ($statuses as $status): ?>
				<? if (isset($results[$item->id][$status->id])): ?>
					<? $total += $results[$item->id][$status->id] ?>
				<? endif ?>
			<? endforeach ?>
			<? foreach ($statuses as $status): ?>
			<td style="text-align: right">				
				<? if (isset($results[$item->id][$status->id])): ?>
					<? $result = $results[$item->id][$status->id] ?>
					<? if ($total > 0): ?>
						<? $percent = (int) (($result / $total) * 100) ?>
					<? else: ?>
						<? $percent = 0 ?>
					<? endif ?>
				<? else: ?>
					<? $result = 0 ?>
					<? $percent = 0 ?>
				<? endif ?>
				<?= $result ?> <span class="secondary">(<?= $percent ?>%)</span>
			</td>
			<? endforeach ?>
			<td style="text-align: right"><?= $total ?></td>
		</tr>
	<? endforeach ?>
</table>
