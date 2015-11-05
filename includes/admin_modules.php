<?php

/**
 * admin modules list
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_modules_list()
{
	$output = Redaxscript\Hook::trigger('adminModuleListStart');

	/* query modules */

	$result = Redaxscript\Db::forTablePrefix('modules')->orderByAsc('name')->findArray();
	$num_rows = count($result);

	/* collect listing output */

	$output .= '<h2 class="title_content">' . l('modules') . '</h2>';
	$output .= '<div class="wrapper_table_admin"><table class="table table_admin">';

	/* collect thead and tfoot */

	$output .= '<thead><tr><th class="s4o6 column_first">' . l('name') . '</th><th class="s1o6 column_second">' . l('alias') . '</th><th class="s1o6 column_last">' . l('version') . '</th></tr></thead>';
	$output .= '<tfoot><tr><td class="column_first">' . l('name') . '</td><td class="column_second">' . l('alias') . '</td><td class="column_last">' . l('version') . '</td></tr></tfoot>';
	if ($result == '' || $num_rows == '')
	{
		$error = l('module_no') . l('point');
	}
	else if ($result)
	{
		$accessValidator = new Redaxscript\Validator\Access();
		$output .= '<tbody>';
		foreach ($result as $r)
		{
			$access = $r['access'];

			/* access granted */

			if ($accessValidator->validate($access, MY_GROUPS) === Redaxscript\Validator\ValidatorInterface::PASSED)
			{
				if ($r)
				{
					foreach ($r as $key => $value)
					{
						$$key = stripslashes($value);
					}
				}
				$modules_installed_array[] = $alias;

				/* build class string */

				if ($status == 1)
				{
					$class_status = '';
				}
				else
				{
					$class_status = 'row_disabled';
				}

				/* collect table row */

				$output .= '<tr';
				if ($alias)
				{
					$output .= ' id="' . $alias . '"';
				}
				if ($class_status)
				{
					$output .= ' class="' . $class_status . '"';
				}
				$output .= '><td class="column_first">' . $name;

				/* collect control output */

				$output .= admin_control('modules_installed', 'modules', $id, $alias, $status, MODULES_INSTALL, MODULES_EDIT, MODULES_UNINSTALL);

				/* collect alias and version output */

				$output .= '</td><td class="column_second">' . $alias . '</td><td class="column_last">' . $version . '</td></tr>';
			}
			else
			{
				$counter++;
			}
		}
		$output .= '</tbody>';

		/* handle access */

		if ($num_rows == $counter)
		{
			$error = l('access_no') . l('point');
		}
	}

	/* handle error */

	if ($error)
	{
		$output .= '<tbody><tr><td colspan="3">' . $error . '</td></tr></tbody>';
	}

	/* modules not installed */

	if (MODULES_INSTALL == 1)
	{
		/* modules directory object */

		$modules_directory = new Redaxscript\Directory();
		$modules_directory->init('modules');
		$modules_directory_array = $modules_directory->getArray();
		if ($modules_directory_array && $modules_installed_array)
		{
			$modules_not_installed_array = array_diff($modules_directory_array, $modules_installed_array);
		}
		else if ($modules_directory_array)
		{
			$modules_not_installed_array = $modules_directory_array;
		}
		if ($modules_not_installed_array)
		{
			$output .= '<tbody><tr class="row_group"><td colspan="3">' . l('install') . '</td></tr>';
			foreach ($modules_not_installed_array as $alias)
			{
				/* collect table row */

				$output .= '<tr';
				if ($alias)
				{
					$output .= ' id="' . $alias . '"';
				}
				$output .= '><td colspan="3">' . $alias;

				/* collect control output */

				$output .= admin_control('modules_not_installed', 'modules', $id, $alias, $status, MODULES_INSTALL, MODULES_EDIT, MODULES_UNINSTALL);
				$output .= '</td></tr>';
			}
			$output .= '</tbody>';
		}
	}
	$output .= '</table></div>';
	$output .= Redaxscript\Hook::trigger('adminModuleListEnd');
	echo $output;
}

/**
 * admin modules form
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_modules_form()
{
	$output = Redaxscript\Hook::trigger('adminModuleFormStart');

	/* define fields for existing user */

	if (ADMIN_PARAMETER == 'edit' && ID_PARAMETER)
	{
		/* query modules */

		$result = Redaxscript\Db::forTablePrefix('modules')->where('id', ID_PARAMETER)->findArray();
		$r = $result[0];
		if ($r)
		{
			foreach ($r as $key => $value)
			{
				$$key = stripslashes($value);
			}
		}
		$wording_headline = $name;
		$wording_submit = l('save');
		$route = 'admin/process/modules/' . $id;
	}

	/* directory object */

	$docs_directory = new Redaxscript\Directory();
	$docs_directory ->init('modules/' . $alias . '/docs');
	$docs_directory_array = $docs_directory->getArray();

	/* collect output */

	$output .= '<h2 class="title_content">' . $wording_headline . '</h2>';
	$output .= form_element('form', 'form_admin', 'js_validate_form js_tab form_admin hidden_legend', '', '', '', 'action="' . REWRITE_ROUTE . $route . '" method="post"');

	/* collect tab list output */

	$output .= '<ul class="js_list_tab list_tab list_tab_admin">';
	$output .= '<li class="js_item_active item_first item_active">' . anchor_element('internal', '', '', l('module'), FULL_ROUTE . '#tab-1') . '</li>';
	$output .= '<li class="item_second">' . anchor_element('internal', '', '', l('customize'), FULL_ROUTE . '#tab-2') . '</li>';
	foreach ($docs_directory_array as $key => $value)
	{
		$output .= '<li class="item_third">' . anchor_element('internal', '', '', str_replace('.phtml', '', $value), FULL_ROUTE . '#tab-'. ($key + 3)) . '</li>';
	}
	$output .= '</ul>';

	/* collect tab box output */

	$output .= '<div class="js_box_tab box_tab box_tab_admin">';

	/* collect module set */

	$output .= form_element('fieldset', 'tab-1', 'js_set_tab js_set_active set_tab set_tab_admin set_active', '', '', l('user')) . '<ul>';
	$output .= '<li>' . form_element('text', 'name', 'field_text_admin field_note', 'name', $name, l('name'), 'maxlength="50" required="required" autofocus="autofocus"') . '</li>';
	$output .= '<li>' . form_element('textarea', 'description', 'js_auto_resize field_textarea_admin field_small', 'description', $description, l('description'), 'rows="1" cols="15"') . '</li>';
	$output .= '</ul></fieldset>';

	/* collect customize set */

	$output .= form_element('fieldset', 'tab-2', 'js_set_tab set_tab set_tab_admin', '', '', l('customize')) . '<ul>';
	$output .= '<li>' . select_element('status', 'field_select_admin', 'status', array(
			l('enable') => 1,
			l('disable') => 0
		), $status, l('status')) . '</li>';

	/* build access select */

	if (GROUPS_EDIT == 1)
	{
		$access_array[l('all')] = null;
		$access_result = Redaxscript\Db::forTablePrefix('groups')->orderByAsc('name')->findArray();
		if ($access_result)
		{
			foreach ($access_result as $g)
			{
				$access_array[$g['name']] = $g['id'];
			}
		}
		$output .= '<li>' . select_element('access', 'field_select_admin', 'access', $access_array, $access, l('access'), 'multiple="multiple"') . '</li></ul></fieldset>';
	}

	/* template object */

	$template = new Redaxscript\Template;

	/* collect docs set */

	foreach ($docs_directory_array as $key => $value)
	{
		$output .= form_element('fieldset', 'tab-' . ($key + 3), 'js_set_tab set_tab set_tab_admin', '', '', 'docs') . '<ul>';
		$output .= '<li>' . $template->partial('modules/' . $alias . '/docs/' . $value) . '</li></ul></fieldset>';
	}
	$output .= '</div>';

	/* collect hidden output */

	$output .= form_element('hidden', '', '', 'alias', $alias);
	$output .= form_element('hidden', '', '', 'token', TOKEN);

	/* cancel button */

	if (MODULES_EDIT == 1 || MODULES_UNINSTALL == 1)
	{
		$cancel_route = 'admin/view/modules';
	}
	else
	{
		$cancel_route = 'admin';
	}
	$output .= anchor_element('internal', '', 'js_cancel button_admin button_large button_cancel_admin', l('cancel'), $cancel_route);

	/* uninstall button */

	if (MODULES_UNINSTALL == 1)
	{
		$output .= anchor_element('internal', '', 'js_delete js_confirm button_admin button_large button_uninstall_admin', l('uninstall'), 'admin/uninstall/modules/' . $alias . '/' . TOKEN);
	}

	/* submit button */

	if (MODULES_EDIT == 1)
	{
		$output .= form_element('button', '', 'js_submit button_admin button_large button_submit_admin', ADMIN_PARAMETER, $wording_submit);
	}
	$output .= '</form>';
	$output .= Redaxscript\Hook::trigger('adminModuleFormEnd');
	echo $output;
}
