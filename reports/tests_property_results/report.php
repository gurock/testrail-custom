<?php

/**
 * Copyright Gurock Software GmbH. All rights reserved.
 *
 * This is a sample custom report plugin that computes and displays
 * the result distribution for different test properties.
 *
 * http://docs.gurock.com/testrail-custom/reports-introduction
 * http://www.gurock.com/testrail/
 */

class Tests_property_results_report_plugin extends Report_plugin
{
	private $_model;
	private $_controls;

	// The controls and options for those controls that are used on
	// the form of this report.
	private static $_control_schema = array(
		'runs_select' => array(
			'namespace' => 'custom_runs',
			'multiple_suites' => true
		),
		'runs_limit' => array(
			'type' => 'limits_select',
			'namespace' => 'custom_runs',
			'min' => 0,
			'max' => 100,
			'default' => 10
		)
	);
		
	// The resources (files) to copy to the output directory when
	// generating a report.
	private static $_resources = array(
		'images/app/run10.png',
		'images/icons/help.png',
		'js/highcharts.js',
		'js/jquery.js',
		'styles/print.css',
		'styles/reset.css',
		'styles/view.css'
	);

	public function __construct()
	{
		parent::__construct();
		$this->_model = new Tests_property_results_summary_model();
		$this->_model->init();
		$this->_controls = $this->create_controls(
			self::$_control_schema
		);
	}

	public function prepare_form($context, $validation)
	{
		// Assign the validation rules for the controls used on the
		// form.
		$this->prepare_controls($this->_controls, $context, 
			$validation);

		// Assign the validation rules for the fields on the form.
		$validation->add_rules(
			array(
				'custom_types_include' => array(
					'type' => 'bool',
					'default' => false
				),
				'custom_priorities_include' => array(
					'type' => 'bool',
					'default' => false
				)
			)
		);

		if (request::is_post())
		{
			return;
		}

		// We assign the default values for the form depending on the
		// event. For 'add', we use the default values of this plugin.
		// For 'edit/rerun', we use the previously saved values of
		// the report/report job to initialize the form. Please note
		// that we prefix all fields in the form with 'custom_' and
		// that the storage format omits this prefix (validate_form).

		if ($context['event'] == 'add')
		{
			$defaults = array(
				'types_include' => true,
				'priorities_include' => true
			);
		}
		else
		{
			$defaults = $context['custom_options'];
		}

		foreach ($defaults as $field => $value)
		{
			$validation->set_default('custom_' . $field, $value);
		}
	}

	public function validate_form($context, $input, $validation)
	{
		// At least one detail entity option must be selected (types or
		// priorities).
		if (!$input['custom_types_include'] &&
			!$input['custom_priorities_include'])
		{
			$validation->add_error(
				lang('reports_tpr_form_details_include_required')
			);

			return false;
		}
				
		// We begin with validating the controls used on the form.
		$values = $this->validate_controls(
			$this->_controls,
			$context,
			$input,
			$validation);

		if (!$values)
		{
			return false;
		}
		
		static $fields = array(
			'types_include',
			'priorities_include'
		);

		// And then add our fields from the form input that are not
		// covered by the controls and return the data as it should be
		// stored in the report options.
		foreach ($fields as $field)
		{
			$key = 'custom_' . $field;
			$values[$field] = arr::get($input, $key);
		}

		return $values;
	}

	public function render_form($context)
	{
		$params = array(
			'controls' => $this->_controls,
			'project' => $context['project']
		);

		// Note that we return separate HTML snippets for the form/
		// options and the used dialogs (which must be included after
		// the actual form as they include their own <form> tags).
		return array(
			'form' => $this->render_view(
				'form',
				$params,
				true
			),
			'after_form' => $this->render_view(
				'form_dialogs',
				$params,
				true
			)
		);
	}

	public function run($context, $options)
	{
		$project = $context['project'];

		// Read the test suites first.
		$suites = $this->_helper->get_suites_by_include(
			$project->id,
			$options['runs_suites_ids'],
			$options['runs_suites_include']
		);

		$suite_ids = obj::get_ids($suites);

		// We then get the actual list of test runs used, depending on
		// the report options.
		if ($suite_ids)
		{
			$runs = $this->_helper->get_runs_by_include(
				$project->id,
				$suite_ids,
				$options['runs_include'],
				$options['runs_ids'],
				$options['runs_filters'],
				null, // Active and completed
				$options['runs_limit'],
				$run_rels,
				$run_count
			);
		}
		else
		{
			$runs = array();
			$run_rels = array();
			$run_count = 0;
		}

		$run_ids = obj::get_ids($runs);

		// Get all active statuses from the database.
		$statuses = $this->_model->get_statuses();
		$status_ids = obj::get_ids($statuses);

		// Read the types and priorities from the database including
		// results.
		$types_include = $options['types_include'];
		$types = array();
		$types_results = array();

		if ($types_include && $run_ids)
		{
			$types = $this->_model->get_types();
			foreach ($types as $type)
			{
				$types_results[$type->id] = 
					$this->_model->get_type_results(
						$run_ids,
						$type->id
					);
			}
		}

		$priorities_include = $options['priorities_include'];
		$priorities = array();
		$priorities_results = array();

		if ($priorities_include && $run_ids)
		{
			$priorities = $this->_model->get_priorities();
			foreach ($priorities as $priority)
			{
				$priorities_results[$priority->id] = 
					$this->_model->get_priority_results(
						$run_ids,
						$priority->id
					);
			}
		}

		// Render the report to a temporary file and return the path
		// to TestRail (including additional resources that need to be
		// copied).
		return array(
			'resources' => self::$_resources,
			'html_file' => $this->render_page(
				'index',
				array(
					'report' => $context['report'],
					'project' => $project,
					'runs' => $runs,
					'run_rels' => $run_rels,
					'run_count' => $run_count,
					'statuses' => $statuses,
					'types_include' => $types_include,
					'types' => $types,
					'types_results' => $types_results,
					'priorities_include' => $priorities_include,
					'priorities' => $priorities,
					'priorities_results' => $priorities_results
				)
			)
		);
	}
}

class Tests_property_results_summary_model extends BaseModel
{
	public function get_statuses()
	{
		$this->db->select('*');
		$this->db->from('statuses');
		$this->db->where('is_active', true);
		$this->db->order_by('display_order');
		return $this->db->get_result();
	}

	public function get_types()
	{
		$this->db->select('*');
		$this->db->from('case_types');
		$this->db->where('is_deleted', false);
		$this->db->order_by('name', 'asc');
		return $this->db->get_result();
	}

	public function get_type_results($run_ids, $type_id)
	{
		$query = $this->db->query(
			'SELECT
				tests.status_id,
				COUNT(*) as status_count
			FROM
				tests
			JOIN
				cases
					on
				cases.id = tests.content_id
			WHERE
				tests.run_id in ({0}) and
				cases.type_id = {1}
			GROUP BY
				tests.status_id',
			$run_ids,
			$type_id
		);

		$results = $query->result();
		return obj::get_lookup_scalar(
			$results,
			'status_id',
			'status_count'
		);
	}

	public function get_priorities()
	{
		$this->db->select('*');
		$this->db->from('priorities');
		$this->db->where('is_deleted', false);
		$this->db->order_by('priority', 'asc');
		return $this->db->get_result();
	}

	public function get_priority_results($run_ids, $priority_id)
	{
		$query = $this->db->query(
			'SELECT
				tests.status_id,
				COUNT(*) as status_count
			FROM
				tests
			JOIN
				cases
					on
				cases.id = tests.content_id
			WHERE
				tests.run_id in ({0}) and
				cases.priority_id = {1}
			GROUP BY
				tests.status_id',
			$run_ids,
			$priority_id
		);

		$results = $query->result();
		return obj::get_lookup_scalar(
			$results,
			'status_id',
			'status_count'
		);
	}
}
