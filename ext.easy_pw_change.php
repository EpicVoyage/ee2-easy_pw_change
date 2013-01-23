<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }
/**
 * =====================================================
 * Show "Change Password" in the CP quick links.
 * -----------------------------------------------------
 * Copyright 2012 EpicVoyage. All Rights Reserved
 * https://www.epicvoyage.org/ee/easy_pw_change/
 * -----------------------------------------------------
 * v0.1: Initial release.
 * v0.2: Added last_call.
 * =====================================================
 */

class Easy_pw_change_ext {
	# Basic information about this extension
	var $name = 'Easy PW Change';
	var $version = '0.3';
	var $description = 'Show a "Change Password" link in the CP quick links';
	var $settings_exist = 'y';
	var $docs_url = 'https://www.epicvoyage.org/ee/easy_pw_change/';

	# Our script's settings
	var $settings = array(
		'link' => 'Change Password',
		'disable_sidebar' => false,
		'disable_username' => false,
		'groups' => array()
	);

	function __construct($settings = '') {
		$this->EE =& get_instance();

		if (!empty($settings)) {
			$this->settings = $settings;
		}
	}

	/**
	 * Yay! The cp_menu_array hook is finally documented. It was created around EE 2.1.5.
	 */
	public function add_menus($menu) {
		if ($this->EE->extensions->last_call !== FALSE) {
			$menu = $this->EE->extensions->last_call;
		}

		if (in_array($this->EE->session->userdata('group_id'), $this->settings['groups'])) {
			$this->EE->lang->language['nav_easy_pw_change'] = $this->settings['link'];
			$menu['easy_pw_change'] = BASE.AMP.'C=myaccount'.AMP.'M=username_password';

			if (isset($_GET['C']) && ($_GET['C'] == 'myaccount') && !isset($_GET['id'])) {
				$this->EE->session->set_flashdata('easy_pw_change_js', true);
			}
		}

		return $menu;
	}

	/**
	 * Hide the requested elements of the membership page for the named groups.
	 */
	function mod_pw_page() {
		$ret = '';
		if ($this->EE->extensions->last_call !== FALSE) {
			$ret = $this->EE->extensions->last_call;
		}

		# Just to double-check that this is not cached...
		$this->EE->output->set_header('Cache-Control: no-cache, must-revalidate');
		$this->EE->output->set_header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

		if ($this->EE->session->flashdata('easy_pw_change_js')) {
			if ($this->settings['disable_sidebar']) {
				$ret .= '$(".pageContents table").find("td").first().remove();';
			}
			if ($this->settings['disable_username']) {
				$ret .= "var easy_pw_change_user = $('#username');easy_pw_change_user.after(easy_pw_change_user.val()+'<input type=\"hidden\" name=\"username\" id=\"username\" value=\"'+easy_pw_change_user.val()+'\" />');easy_pw_change_user.remove();";
			}

			unset($this->EE->session->flashdata['easy_pw_change_js']);
		}

		return $ret;
	}

	function settings_form($current) {
		$this->EE->load->helper('form');
		$this->EE->load->library('table');
		$this->EE->lang->loadfile('easy_pw_change');

		$settings = array(
			'menu_name' => form_input(array(
				'name' => 'link',
				'value' => $current['link'],
			)),
			'disable_sidebar' => form_checkbox('disable_sidebar', 1, $current['disable_sidebar']),
			'disable_username' => form_checkbox('disable_username', 1, $current['disable_username'])
		);

		$this->EE->db->select('group_id, group_title');
		//$this->EE->db->where_not('group_id', 1);
		$this->EE->db->order_by('group_title', 'ASC');
		$query = $this->EE->db->get('member_groups');
		$groups = array();
		if ($query->num_rows()) {
			foreach ($query->result_array() as $row) {
				$groups[$row['group_id']] = array(
					$row['group_title'],
					form_checkbox('groups[]', $row['group_id'], in_array($row['group_id'], $current['groups']))
				);
			}
		}

		return $this->EE->load->view('index', array('settings' => $settings, 'groups' => $groups), true);
	}

	function save_settings() {
		if (empty($_POST)) {
			show_error($this->EE->lang->line('unauthorized_access'));
		}
		unset($_POST['submit']);

		$_POST['disable_sidebar'] = isset($_POST['disable_sidebar']) ? $_POST['disable_sidebar'] : false;
		$_POST['disable_username'] = isset($_POST['disable_username']) ? $_POST['disable_username'] : false;
		$_POST['groups'] = isset($_POST['groups']) ? $_POST['groups'] : array();

		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update('extensions', array('settings' => serialize($_POST)));

		# Notify the user that the settings were updated.
		$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('preferences_updated'));

		return;
	}
	
	# Install ourselves into the database.
	function activate_extension() {
		$this->disable_extension();

		# Install hooks...
		$hooks = array(
			'cp_menu_array' => 'add_menus',
			'cp_js_end' => 'mod_pw_page'
		);
		$ext_template = array(
			'class'	=> __CLASS__,
			'settings' => serialize($this->settings),
			'priority' => 10,
			'version'  => $this->version,
			'enabled'  => 'y'
		);

		foreach ($hooks as $hook => $method) {
			$ext_template['hook'] = $hook;
			$ext_template['method'] = $method;
			$this->EE->db->insert('extensions', $ext_template);
		}

		return;
	}


	# No updates yet, but the manual says this function is required.
	function update_extension($current = '') {
		//$this->activate_extension();
		return;
	}

	# Uninstalls extension
	function disable_extension() {
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');

		return;
	}
}

/* End of file ext.easy_pw_change.php */
/* Location: ./system/expressionengine/third_party/easy_pw_change/ext.easy_pw_change.php */
