<?php

/**
 * Editor
 *
 * @package   Editor
 * @author    Brandon Kelly <me@brandon-kelly.com>
 * @link      http://brandon-kelly.com/apps/editor/
 * @see       http://expressionengine.com/forums/viewthread/39595/
 * @see       http://expressionengine.com/forums/viewthread/78049/
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
	var $version = '0.0.1';

	/**
	 * Extension Description
	 *
	 * @var string
	 */
	var $description = 'In-site entry editing';

	/**
	 * Extension Settings Exist
	 *
	 * If set to 'y', a settings page will be shown in the Extensions Manager
	 *
	 * @var string
	 */
	var $settings_exist = 'n';

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
	function Editor($settings='')
	{
		$this->settings = $settings;
	}



	/**
	 * Activate Extension
	 *
	 * Resets all Playa exp_extensions rows
	 *
	 * @since version 1.0.0
	 */
	function activate_extension()
	{
		global $DB;

//		// Get settings
//		$settings = $this->get_all_settings();
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

//		if ($this->settings['check_for_extension_updates'] == 'y')
//		{
//		    $sources[] = 'http://brandon-kelly.com/apps/versions.xml';
//		}
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

//	    if ($this->settings['check_for_extension_updates'] == 'y')
//	    {
//	        $addons[$this->class_name] = $this->version;
//	    }
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

		global $PREFS, $SESS;

		// return tagdata if user can't edit entries
		if ( ! (isset($SESS->userdata) AND isset($SESS->userdata['can_access_edit']) AND $SESS->userdata['can_access_edit'] == 'y') )
		{
			return $tagdata;
		}

		global $PREFS, $LANG, $EDITOR_ENTRIES;

		// Define our base URL
		if ( ! defined('BASE'))
		{
			define('BASE', PATH.'?S='.$SESS->userdata['session_id']);
		}

		// Import CP language file for "Edit" string
		$LANG->fetch_language_file('cp');

		if ( ! isset($EDITOR_ENTRIES))
		{
			// create $EDITOR_ENTRIES to keep track of which
			// entries we've already created buttons for
			$EDITOR_ENTRIES = array();

			// Add Editor button styles
			$tagdata = '<style type="text/css"> '
			         . '.editor-button { position:relative; } '
			         . '.editor-button a { display:block; position:absolute; top:0; left:-16px; width:16px; height:12px; background:url('.PATH_CP_IMG.'edit_template.gif) no-repeat 0 0; opacity:0.1; text-indent:-9999em; overflow:hidden; } '
			         . '.editor-button a:hover { opacity:1; }'
			         . '</style>'
			         . $tagdata;
		}

		if (isset($row['entry_id']) AND is_numeric($row['entry_id']) AND ( ! in_array($row['entry_id'], $EDITOR_ENTRIES)))
		{
			// Add button
			$title = $LANG->line('edit')." &ldquo;{$row['title']}&rdquo;";
			$tagdata = '<div class="editor-button">'
			         . '<a href="'.BASE.'&amp;C=edit&amp;M=edit_entry&amp;weblog_id='.$row['weblog_id'].'&amp;entry_id='.$row['entry_id'].'" title="'.$title.'" target="_blank"></a>'
			         . '</div>'
			         . $tagdata;

			// Add entry id to $EDITOR_ENTRIES
			$EDITOR_ENTRIES[] = $row['entry_id'];
		}

		return $tagdata;
	}
}

?>