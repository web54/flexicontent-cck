<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');


/**
 * Fccats HTML helper
 *
 * @since  3.2
 */
abstract class JHtmlFccats
{
	static $tooltip_class  = FLEXI_J40GE ? 'hasTooltip' : 'hasTooltip';
	static $btn_sm_class   = FLEXI_J40GE ? 'btn btn-sm' : 'btn btn-small';
	static $btn_iv_class   = FLEXI_J40GE ? 'btn-dark' : 'btn-inverse';
	static $btn_mbar_class = FLEXI_J40GE ? 'btn-outline-info' : '';
	static $ctrl = 'categories';

	/**
	 * Create the publish/unpublish links
	 *
	 * @param   int      $value      The state value
	 * @param   int      $i          Row number
	 * @param   boolean  $canChange  Is user allowed to change?
	 *
	 * @return  string       HTML code
	 */
	public static function published($value = 0, $i, $canChange = true)
	{
		static $states = null;
		//return JHtml::_('jgrid.published', $_published = $value, $i, 'categories.', $canChange);

		if ($states === null)
		{
			// Array of image, task, title, action
			$states = array(
				0 => array('unpublish', 'categories.publish', JHtml::tooltipText('JUNPUBLISHED'), JHtml::tooltipText('JPUBLISHED')),
				1 => array('publish', 'categories.unpublish', JHtml::tooltipText('JPUBLISHED'), JHtml::tooltipText('JUNPUBLISHED')),
			);
		}
		$value = (int) $value;
		$state = isset($states[$value]) ? $states[$value] : $states[1];

		return $canChange
			? '
			<a href="javascript:;" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="statetoggler btn btn-small ntxt hasTooltip'
				. ($value == 1 ? ' active' : '') . '" title="' . $state[3] . '">
				<span class="icon-' . $state[0] . '"></span>
			</a>
			' : '
			<a class="statetoggler btn btn-small ntxt hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="'
				. $state[2] . '">
				<span class="icon-' . $state[0] . '"></span>
			</a>';
	}


	/**
	 * Create the checkin link, also showing if a record is checkedout
	 *
	 * @param   object   $row        The row
	 * @param   object   $user       The user
	 * @param   int      $i          Row number
	 *
	 * @return  string       HTML code
	 */
	public static function checkedout($row, $user, $i)
	{
		//return JHtml::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, static::$ctrl . '.', $row->canCheckin);

		if (!$row->checked_out)
		{
			return '';
		}

		if (!$row->canCheckin)
		{
			return '<span class="icon-lock ' . static::$tooltip_class . '" title="'.JHtml::tooltipText('FLEXI_RECORD_CHECKED_OUT_DIFF_USER').'"></span> ';
		}

		$_tip_title = $row->checked_out == $user->id
			? JText::sprintf('FLEXI_CLICK_TO_RELEASE_YOUR_LOCK_DESC', $row->editor, $row->checked_out_time)
			: JText::sprintf('FLEXI_CLICK_TO_RELEASE_FOREIGN_LOCK_DESC', $row->editor, $row->checked_out_time);

		return 
		($row->checked_out != $user->id ? '<input id="cb'.$i.'" type="checkbox" value="'.$row->id.'" name="cid[]" style="display:none!important;">' : '') . '
		<a class="btn btn-micro btn-outline-secondary ntxt ' . static::$tooltip_class . '" title="'.JHtml::tooltipText($_tip_title).'" href="javascript:;" onclick="var ccb=document.getElementById(\'cb'.$i.'\'); ccb.checked=1; ccb.form.task.value=\'' . static::$ctrl . '.checkin\'; ccb.form.submit();">
			<span class="icon-checkedout"></span>
		</a>
		';
	}


	/**
	 * Create the statetoggler button for cats view
	 *
	 * @param   object   $row        The row
	 * @param   int      $i          Row number
	 *
	 * @return  string       HTML code
	 */
	public static function statebutton($row, $i)
	{
		static $params = null;
		static $addToggler = true;
		static $tooltip_placement = 'top';
		static $ops = null;

		if ($ops === null)
		{
			$ops = array(
				'controller' => static::$ctrl,
				'state_propname' => 'published',
			);
		}

		return flexicontent_html::statebutton($row, $params, $addToggler, $tooltip_placement, static::$btn_sm_class . ' ' . static::$btn_mbar_class, $ops);
	}


	/**
	 * Create the preview link icon
	 *
	 * @param   object   $row        The row
	 * @param   string   $target     The target of the link
	 * @param   int      $i          Row number
	 *
	 * @return  string       HTML code
	 */
	public static function preview($row, $target, $i)
	{
		global $globalcats;

		// Route the record URL to an appropriate menu item
		$record_url = FlexicontentHelperRoute::getCategoryRoute($globalcats[$row->id]->slug, 0, array(), $row);

		// Force language to be switched to the language of the record, thus showing the record (and not its associated translation of current FE language)
		if ($row->language !== '*' && isset(FLEXIUtilities::getLanguages()->{$row->language}))
		{
			$record_url .= '&lang=' . FLEXIUtilities::getLanguages()->{$row->language}->sef;
		}

		// Build a frontend SEF url
		$link = flexicontent_html::getSefUrl($record_url);

		$attribs = ''
			. ' class="fc-preview-btn ntxt ' .  static::$btn_mbar_class . ' ' . static::$btn_sm_class . ' ' . static::$tooltip_class . '"'
			. ' title="' . flexicontent_html::getToolTip('FLEXI_PREVIEW', 'FLEXI_DISPLAY_ENTRY_IN_FRONTEND_DESC', 1, 1) . '"'
			. ' href="' . $link .'"'
			. '	target="' . $target . '"';

		return '
		<a ' . $attribs . '>
			<span class="icon-screen"></span>
		</a> ';
	}



	/**
	 * Create the RSS link icon
	 *
	 * @param   object   $row        The row
	 * @param   string   $target     The target of the link
	 * @param   int      $i          Row number
	 *
	 * @return  string       HTML code
	 */
	public static function rss_link($row, $target, $i)
	{
		global $globalcats;

		// Route the record URL to an appropriate menu item
		$record_url = FlexicontentHelperRoute::getCategoryRoute($globalcats[$row->id]->slug, 0, array(), $row);

		// Force language to be switched to the language of the record, thus showing the record (and not its associated translation of current FE language)
		if ($row->language !== '*' && isset(FLEXIUtilities::getLanguages()->{$row->language}))
		{
			$record_url .= '&lang=' . FLEXIUtilities::getLanguages()->{$row->language}->sef;
		}

		// Build a frontend SEF url
		$link = flexicontent_html::getSefUrl($record_url);

		// Add feed / type variables
		$link = $link . (strstr($link, '?') ? '&amp;' : '?') . 'format=feed&amp;type=rss';

		$attribs = ''
			. ' class="fc-preview-btn ntxt ' .  static::$btn_mbar_class . ' ' . static::$btn_sm_class . ' ' . static::$tooltip_class . '"'
			. ' title="' . flexicontent_html::getToolTip('FLEXI_PREVIEW', 'FLEXI_DISPLAY_ENTRY_IN_FRONTEND_DESC', 1, 1) . '"'
			. ' href="' . $link .'"'
			. '	target="' . $target . '"';

		return '
		<a ' . $attribs . '>
			<span class="icon-feed"></span>
		</a> ';
	}


	/**
	 * Create the edit layout icon
	 *
	 * @param   object   $row           The row
	 * @param   string   $target        The target of the link
	 * @param   int      $i             Row number
	 * @param   boolean  $canTemplates  Is user allowed to edit template layouts
	 * @param   string   $layout        Layout if the record row
	 *
	 * @return  string       HTML code
	 */
	public static function edit_layout($row, $target, $i, $canTemplates, $layout)
	{
		if (!$canTemplates || !$layout)
		{
			return '';
		}

		$layout_type = 'category';
		$layout_url  = 'index.php?option=com_flexicontent&amp;view=template&amp;type=' . $layout_type . '&amp;tmpl=component&amp;ismodal=' . ($target === '__modal__' ? '1' : '0') . '&amp;folder=' . $layout;

		if ($target === '__modal__')
		{
			$edit_title = htmlspecialchars(JText::_('FLEXI_EDIT_LAYOUT_N_GLOBAL_PARAMETERS', true), ENT_QUOTES, 'UTF-8');
			$target_attr = ' onclick="var url = jQuery(this).attr(\'href\'); fc_showDialog(url, \'fc_modal_popup_container\', 0, 0, 0, 0, {title: \'' . $edit_title . '\'}); return false;"';
		}
		else
		{
			$target_attr = '	target="' . $target . '"';
		}

		$attribs = ''
			. ' class="fc-edit-layout-btn ntxt ' .  static::$btn_mbar_class . ' ' . static::$btn_sm_class . ' ' . static::$tooltip_class . '"'
			. ' title="'.flexicontent_html::getToolTip( 'FLEXI_EDIT_LAYOUT_N_GLOBAL_PARAMETERS', null, 1, 1).'"'
			. ' href="' . $layout_url .'"'
			. $target_attr;

		return '
		<a ' . $attribs . '>
			<span class="icon-pencil"></span>
		</a> ';
	}


	/**
	 * Create the edit record link
	 *
	 * @param   object   $row         The row
	 * @param   int      $i           Row number
	 * @param   boolean  $canEdit     Is user allowed to edit the item
	 *
	 * @return  string       HTML code
	 */
	public static function edit_link($row, $i, $canEdit)
	{
		static $common_attrs = null;
		
		if ($common_attrs === null)
		{
			$commont_attrs = 'title="' . JText::_('FLEXI_EDIT_ITEM', true) . '" class="fc-iblock text-dark"';
		}

		// Display title with no edit link ... if row is not-editable for any reason (no ACL or checked-out by other user)
		if (!$canEdit || ($row->checked_out && (int) $row->checked_out !== (int) JFactory::getUser()->id))
		{
			return htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8');
		}

		// Display title with edit link ... (row editable and not checked out)
		else
		{
			$edit_task = 'task=category.edit';
			$edit_link = 'index.php?option=com_flexicontent&amp;' . $edit_task . '&amp;view=item&amp;id=' . $row->id;
			return
			'<a href="' . $edit_link . '" ' . $common_attrs . '>'
				. '<span class="icon-pencil-2"></span>'
				. htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8') .
			'</a>';
		}
	}


	/**
	 * Method to create a checkbox for a grid row.
	 *
	 * @param   integer  $rowNum      The row index
	 * @param   integer  $recId       The record id
	 * @param   boolean  $checkedOut  True if item is checked out
	 * @param   string   $name        The name of the form element
	 * @param   string   $stub        The name of stub identifier
	 * @param   string   $title       The name of the item
	 *
	 * @return  mixed    String of html with a checkbox if item is not checked out, null if checked out.
	 *
	 * @since   3.3
	 */
	public static function grid_id($rowNum, $recId, $checkedOut = false, $name = 'cid', $stub = 'cb', $title = '')
	{
		if ($checkedOut)
		{
			return '';
		}

		return '
			<div class="group-fcset">
				<input type="checkbox" id="' . $stub . $rowNum . '" name="' . $name . '[]" value="' . $recId . '" onclick="Joomla.isChecked(this.checked);">
				<label for="' . $stub . $rowNum . '"><span class="sr-only">' . JText::_('JSELECT') . ' ' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '</span></label>
			</div>';
	}
}
