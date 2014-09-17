<? $tab = 1 ?>

<p><?= lang('reports_tmpl_form_intro') ?></p>

<div class="tabs">
	<div class="tab-header">
		<a href="javascript:void(0)" class="tab1 <?= $tab == 1 ? 'current' : '' ?>" rel="1"
			onclick="App.Tabs.activate(this)"><?= lang('reports_tmpl_form_tab1') ?></a>
		<a href="javascript:void(0)" class="tab2 <?= $tab == 2 ? 'current' : '' ?>" rel="2"
			onclick="App.Tabs.activate(this)"><?= lang('reports_tmpl_form_tab2') ?></a>
	</div>
	<div class="tab-body tab-frame">
		<div class="tab tab1 <?= $tab != 1 ? 'hidden' : '' ?>">
			<?= lang('reports_tmpl_form_tab1') ?>
		</div>
		<div class="tab tab2 <?= $tab != 2 ? 'hidden' : '' ?>">
			<?= lang('reports_tmpl_form_tab2') ?>
		</div>
	</div>
</div>
