<?php

/**
 * Editor
 *
 * This extension adds little Edit buttons next to your weblog entries on your website.
 *
 * @package   Editor
 * @author    Brandon Kelly <me@brandon-kelly.com>
 * @link      http://brandon-kelly.com/apps/editor/
 * @copyright Copyright (c) 2008 Brandon Kelly
 * @license   http://creativecommons.org/licenses/by-sa/3.0/   Attribution-Share Alike 3.0 Unported
 */
class Editor
{
	/**
	 * Extension Settings
	 *
	 * @var array
	 */
	var $settings = array();

	/**
	 * Extension Name
	 *
	 * @var string
	 */
	var $name = 'Editor';

	/**
	 * Extension Class Name
	 *
	 * @var string
	 */
	var $class_name = 'Editor';

	/**
	 * Extension Version
	 *
	 * @var string
	 */
	var $version = '1.0.0';

	/**
	 * Extension Description
	 *
	 * @var string
	 */
	var $description = 'Access the Edit form right from your site';

	/**
	 * Extension Settings Exist
	 *
	 * If set to 'y', a settings page will be shown in the Extensions Manager
	 *
	 * @var string
	 */
	var $settings_exist = 'y';

	/**
	 * Documentation URL
	 *
	 * @var string
	 */
	var $docs_url = 'http://brandon-kelly.com/apps/editor/?utm_campaign=editor_em';



	/**
	 * Extension Constructor
	 *
	 * @param array   $settings
	 * @since version 1.0.0
	 */
	function Editor($settings=array())
	{
		$this->settings = $this->get_site_settings($settings);
	}
	
	
	
	/**
	 * Get All Settings
	 *
	 * @return array   All extension settings
	 * @since  version 1.0.0
	 */
	function get_all_settings()
	{
		global $DB;

		$query = $DB->query("SELECT settings
		                     FROM exp_extensions
		                     WHERE class = '{$this->class_name}'
		                       AND settings != ''
		                     LIMIT 1");

		return $query->num_rows
			? unserialize($query->row['settings'])
			: array();
	}



	/**
	 * Get Default Settings
	 * 
	 * @return array   Default settings for site
	 * @since 1.0.0
	 */
	function get_default_settings()
	{
		$settings = array(
			'cp_url'                      => '',
			'check_for_extension_updates' => 'y'
		);

		return $settings;
	}



	/**
	 * Get Site Settings
	 *
	 * @param  array   $settings   Current extension settings (not site-specific)
	 * @return array               Site-specific extension settings
	 * @since  version 1.0.0
	 */
	function get_site_settings($settings=array())
	{
		global $PREFS;
		
		$site_settings = $this->get_default_settings();
		
		$site_id = $PREFS->ini('site_id');
		if (isset($settings[$site_id]))
		{
			$site_settings = array_merge($site_settings, $settings[$site_id]);
		}

		return $site_settings;
	}
	
	
	
	



	/**
	 * Settings Form
	 *
	 * Construct the custom settings form.
	 *
	 * Look and feel based on LG Addon Updater's settings form.
	 *
	 * @param  array   $current   Current extension settings (not site-specific)
	 * @see    http://expressionengine.com/docs/development/extensions.html#settings
	 * @since  version 1.0.0
	 */
	function settings_form($current)
	{
		$current = $this->get_site_settings($current);
		
		global $DB, $DSP, $LANG, $IN;

		// Breadcrumbs

		$DSP->crumbline = TRUE;

		$DSP->title = $LANG->line('extension_settings');
		$DSP->crumb = $DSP->anchor(BASE.AMP.'C=admin'.AMP.'area=utilities', $LANG->line('utilities'))
		            . $DSP->crumb_item($DSP->anchor(BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=extensions_manager', $LANG->line('extensions_manager')))
		            . $DSP->crumb_item($this->name);

	    $DSP->right_crumb($LANG->line('disable_extension'), BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=toggle_extension_confirm'.AMP.'which=disable'.AMP.'name='.$IN->GBL('name'));

		// Donations button
	    $DSP->body .= '<div style="float:right;">'
	                . '<a style="display:block; margin:-2px 10px 0 0; padding:5px 0 5px 70px; width:190px; height:15px; font-size:12px; line-height:15px;'
	                . ' background:url(http://brandon-kelly.com/images/shared/donations.png) no-repeat 0 0; color:#000; font-weight:bold;"'
	                . ' href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2181794" target="_blank">'
	                . $LANG->line('donate')
	                . '</a>'
	                . '</div>'

		// Form header

		           . "<h1>{$this->name} <small>{$this->version}</small></h1>"

		           . $DSP->form_open(
		                                 array(
		                                     'action' => 'C=admin'.AMP.'M=utilities'.AMP.'P=save_extension_settings',
		                                     'name'   => 'settings_example',
		                                     'id'     => 'settings_example'
		                                 ),
		                                 array(
		                                     'name' => strtolower($this->class_name)
		                                 )
		                             )
		// CP URL Setting

		            . $DSP->table_open(
		                                   array(
		                                       'class'  => 'tableBorder',
		                                       'border' => '0',
		                                       'style' => 'margin-top:18px; width:100%'
		                                   )
		                               )

		            . $DSP->tr()
		            . $DSP->td('tableHeading', '', '2')
		            . $LANG->line("cp_url_title")
		            . $DSP->td_c()
		            . $DSP->tr_c()

		            . $DSP->tr()
		            . $DSP->td('', '', '2')
		            . '<div class="box" style="border-width:0 0 1px 0; margin:0; padding:10px 5px"><p>'.$LANG->line('cp_url_info').'</p></div>'
		            . $DSP->td_c()
		            . $DSP->tr_c()

		            . $DSP->tr()
		            . $DSP->td('tableCellOne', '60%')
		            . $DSP->qdiv('defaultBold', $LANG->line("cp_url_label"))
		            . $DSP->td_c()

		            . $DSP->td('tableCellOne')
		            . $DSP->input_text('cp_url', isset($current['cp_url']) ? $current['cp_url'] : '')
		            . $DSP->td_c()
		            . $DSP->tr_c()

		            . $DSP->table_c();


		// Updates Setting

		$lgau_query = $DB->query("SELECT class
		                          FROM exp_extensions
		                          WHERE class = 'Lg_addon_updater_ext'
		                            AND enabled = 'y'
		                          LIMIT 1");
		$lgau_enabled = $lgau_query->num_rows ? TRUE : FALSE;
		$check_for_extension_updates = ($lgau_enabled AND $current['check_for_extension_updates'] == 'y') ? TRUE : FALSE;

		$DSP->body .= $DSP->table_open(
		                                   array(
		                                       'class'  => 'tableBorder',
		                                       'border' => '0',
		                                       'style' => 'margin-top:18px; width:100%'
		                                   )
		                               )

		            . $DSP->tr()
		            . $DSP->td('tableHeading', '', '2')
		            . $LANG->line("check_for_extension_updates_title")
		            . $DSP->td_c()
		            . $DSP->tr_c()

		            . $DSP->tr()
		            . $DSP->td('', '', '2')
		            . '<div class="box" style="border-width:0 0 1px 0; margin:0; padding:10px 5px"><p>'.$LANG->line('check_for_extension_updates_info').'</p></div>'
		            . $DSP->td_c()
		            . $DSP->tr_c()

		            . $DSP->tr()
		            . $DSP->td('tableCellOne', '60%')
		            . $DSP->qdiv('defaultBold', $LANG->line("check_for_extension_updates_label"))
		            . $DSP->td_c()

		            . $DSP->td('tableCellOne')
		            . '<select name="check_for_extension_updates"'.($lgau_enabled ? '' : ' disabled="disabled"').'>'
		            . $DSP->input_select_option('y', $LANG->line('yes'), ($current['check_for_extension_updates'] == 'y' ? 'y' : ''))
		            . $DSP->input_select_option('n', $LANG->line('no'),  ($current['check_for_extension_updates'] != 'y' ? 'y' : ''))
		            . $DSP->input_select_footer()
		            . ($lgau_enabled ? '' : NBS.NBS.NBS.$LANG->line('check_for_extension_updates_nolgau'))
		            . $DSP->td_c()
		            . $DSP->tr_c()

		            . $DSP->table_c()

		// Close Form

		            . $DSP->qdiv('itemWrapperTop', $DSP->input_submit())
		            . $DSP->form_c();
	}



	/**
	 * Save Settings
	 *
	 * @since version 1.0.0
	 */
	function save_settings()
	{
		global $DB;

		// Save new settings
		$this->settings = array(
			'cp_url'                      => ($_POST['cp_url'] ? $_POST['cp_url'] : SELF),
			'check_for_extension_updates' => $_POST['check_for_extension_updates'],
		);

		$DB->query("UPDATE exp_extensions
		            SET settings = '".addslashes(serialize($this->settings))."'
		            WHERE class = '{$this->class_name}'");
	}



	/**
	 * Activate Extension
	 *
	 * Resets all Editor exp_extensions rows
	 *
	 * @since version 1.0.0
	 */
	function activate_extension()
	{
		global $DB;

		// Get settings
		$settings = array();

		// Delete old hooks
		$DB->query("DELETE FROM exp_extensions
		            WHERE class = '{$this->class_name}'");

		// Add new extensions
		$ext_template = array(
			'class'    => $this->class_name,
			'settings' => addslashes(serialize($settings)),
			'priority' => 10,
			'version'  => $this->version,
			'enabled'  => 'y'
		);

		$extensions = array(
			// LG Addon Updater
			array('hook'=>'lg_addon_update_register_source',    'method'=>'register_my_addon_source'),
			array('hook'=>'lg_addon_update_register_addon',     'method'=>'register_my_addon_id'),

			// Modify Template
			array('hook'=>'weblog_entries_tagdata',             'method'=>'modify_template')
		);

		foreach($extensions as $extension)
		{
			$ext = array_merge($ext_template, $extension);
			$DB->query($DB->insert_string('exp_extensions', $ext));
		}
	}



	/**
	 * Update Extension
	 *
	 * @param string   $current   Previous installed version of the extension
	 * @since version 1.0.0
	 */
	function update_extension($current='')
	{
		global $DB;

		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		$DB->query("UPDATE exp_extensions
		            SET version = '".$DB->escape_str($this->version)."'
		            WHERE class = '{$this->class_name}'");
	}



	/**
	 * Disable Extension
	 *
	 * @since version 1.0.0
	 */
	function disable_extension()
	{
		global $DB;

		$DB->query("UPDATE exp_extensions
		            SET enabled='n'
		            WHERE class='{$this->class_name}'");
	}



	/**
	 * Get Last Call
	 *
	 * @param  mixed   $param   Parameter sent by extension hook
	 * @return mixed            Return value of last extension call if any, or $param
	 * @since  version 1.0.0
	 */
	function get_last_call($param='')
	{
		global $EXT;

		return ($EXT->last_call !== FALSE) ? $EXT->last_call : $param;
	}



	/**
	 * Register a New Addon Source
	 *
	 * @param  array   $sources   The existing sources
	 * @return array              The new source list
	 * @see    http://leevigraham.com/cms-customisation/expressionengine/lg-addon-updater/
	 * @since  version 1.0.0
	 */
	function register_my_addon_source($sources)
	{
		$sources = $this->get_last_call($sources);

		if ($this->settings['check_for_extension_updates'] == 'y')
		{
		    $sources[] = 'http://brandon-kelly.com/apps/versions.xml';
		}
		return $sources;

	}



	/**
	 * Register a New Addon ID
	 *
	 * @param  array   $addons   The existing sources
	 * @return array             The new addon list
	 * @see    http://leevigraham.com/cms-customisation/expressionengine/lg-addon-updater/
	 * @since  version 1.0.0
	 */
	function register_my_addon_id($addons)
	{
		$addons = $this->get_last_call($addons);

	    if ($this->settings['check_for_extension_updates'] == 'y')
	    {
	        $addons[$this->class_name] = $this->version;
	    }
	    return $addons;
	}



	/**
	 * Modify Template
	 *
	 * Add edit images
	 *
	 * @param  string   $tagdata   The Weblog Entries tag data
	 * @param  array    $row       Array of data for the current entry
	 * @param  object   $weblog    The current Weblog object including all data relating to categories and custom fields
	 * @return string              Modified $tagdata
	 * @see    http://expressionengine.com/developers/extension_hooks/weblog_entries_tagdata/
	 * @author Mark Huot <docs@markhuot.com>
	 * @author Brandon Kelly <me@brandon-kelly.com>
	 * @since  version 1.0.0
	 */
	function modify_template($tagdata, $row=array(), &$weblog)
	{
		$tagdata = $this->get_last_call($tagdata);

		global $SESS;

		// return tagdata if user can't edit entries
		if ( ! (isset($SESS->userdata) AND isset($SESS->userdata['can_access_edit']) AND $SESS->userdata['can_access_edit'] == 'y') )
		{
			return $tagdata;
		}

		global $LANG, $EDITOR_ENTRIES, $EDITOR_BASE;

		// Import CP language file for "Edit" string
		$LANG->fetch_language_file('cp');

		// Define $EDITOR_ENTRIES to keep track of which
		// entries we've already created buttons for
		if ( ! isset($EDITOR_ENTRIES))
		{
			$EDITOR_ENTRIES = array();

			// Add Editor button styles
			$tagdata = '<style type="text/css"> '
			         . '.editor-button { position:relative; } '
			         . '.editor-button a { display:block; position:absolute; top:0; left:-16px; width:16px; height:12px; background:url('.PATH_CP_IMG.'edit_template.gif) no-repeat 0 0; opacity:0.1; text-indent:-9999em; overflow:hidden; } '
			         . '.editor-button a:hover { opacity:1; }'
			         . '</style>'
			         . $tagdata;
		}

		// Define $EDITOR_BASE
		if ( ! isset($EDITOR_BASE))
		{
			$EDITOR_BASE = ($this->settings['cp_url'] ? $this->settings['cp_url'] : PATH)
			             . '?S='.$SESS->userdata['session_id'];
		}

		if (isset($row['entry_id']) AND is_numeric($row['entry_id']) AND ( ! in_array($row['entry_id'], $EDITOR_ENTRIES)))
		{
			// Add button
			$title = $LANG->line('edit')." &ldquo;{$row['title']}&rdquo;";
			$tagdata = '<div class="editor-button">'
			         . '<a href="'.$EDITOR_BASE.'&amp;C=edit&amp;M=edit_entry&amp;weblog_id='.$row['weblog_id'].'&amp;entry_id='.$row['entry_id'].'" title="'.$title.'" target="_blank"></a>'
			         . '</div>'
			         . $tagdata;

			// Add entry id to $EDITOR_ENTRIES
			$EDITOR_ENTRIES[] = $row['entry_id'];
		}

		return $tagdata;
	}
}

?>