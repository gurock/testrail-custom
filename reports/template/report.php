<?php

/**
 * Copyright Gurock Software GmbH. See license.md for details.
 *
 * This is the official template for developing report plugins for
 * TestRail.
 *
 * http://docs.gurock.com/testrail-custom/reports-introduction
 * http://www.gurock.com/testrail/
 */

class Template_report_plugin extends Report_plugin
{
	// The resources (files) to copy to the output directory when
	// generating a report.
	private static $_resources = array(
		'js/jquery.js',
		'styles/print.css',
		'styles/reset.css',
		'styles/view.css'
	);

	public function __construct()
	{
		parent::__construct();
	}

	public function prepare_form($context, $validation)
	{
	}

	public function validate_form($context, $input, $validation)
	{
	}

	public function render_form($context)
	{
		$params = array(
			'project' => $context['project']
		);

		return array(
			'form' => $this->render_view(
				'form',
				$params,
				true
			)
		);
	}

	public function run($context, $options)
	{
		$project = $context['project'];

		// Render the report to a temporary file and return the path
		// to TestRail (including additional resources that need to be
		// copied).
		return array(
			'resources' => self::$_resources,
			'html_file' => $this->render_page(
				'index',
				array(
					'report' => $context['report'],
					'project' => $project
				)
			)
		);
	}
}
