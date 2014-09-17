<div class="tabs">
	<div class="tab-header">
		<a href="javascript:void(0)" class="tab1 current" rel="1"
			onclick="App.Tabs.activate(this)">
				<?= lang('reports_tpr_form_details') ?></a>
		<a href="javascript:void(0)" class="tab2" rel="2"
			onclick="App.Tabs.activate(this)">
				<?= lang('reports_tpr_form_runs') ?></a>
	</div>
	<div class="tab-body tab-frame">
		<div class="tab tab1">
			<!-- The content of tab 1 goes here -->
			<p class="top"><?= lang('reports_tpr_form_details_include') ?></p>
			<div class="checkbox form-checkbox" style="margin-left: 15px">
				<label>
					<?= lang('reports_tpr_form_details_include_types') ?>
					<input type="checkbox" id="custom_types_include"
						name="custom_types_include" value="1"
						<?= validation::get_checked('custom_types_include',1) ?> />
				</label>
			</div>
			<div class="checkbox" style="margin-left: 15px">
				<label>
					<?= lang('reports_tpr_form_details_include_priorities') ?>
					<input type="checkbox" id="custom_priorities_include"
						name="custom_priorities_include" value="1"
						<?= validation::get_checked('custom_priorities_include',1) ?> />
				</label>
			</div>
		</div>
		<div class="tab tab2 hidden">
			<!-- The content of tab 2 goes here -->
			<? $report_obj->render_control(
				$controls,
				'runs_select',
				array(
					'top' => true,
					'project' => $project
				)
			) ?>
			<? $report_obj->render_control(
				$controls,
				'runs_limit',
				array(
					'intro' => lang('report_plugins_runs_limit'),
					'limits' => array(5, 10, 25, 50, 100, 0)
				)
			) ?>
		</div>
	</div>
</div>
