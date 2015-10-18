<?php

/**
 * admin panel list
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_panel_list()
{
	$output = Redaxscript\Hook::trigger(__FUNCTION__ . '_start');

	/* define access variables */

	if (CATEGORIES_NEW == 1 || CATEGORIES_EDIT == 1 || CATEGORIES_DELETE == 1)
	{
		$categories_access = $contents_access = 1;
	}
	if (ARTICLES_NEW == 1 || ARTICLES_EDIT == 1 || ARTICLES_DELETE == 1)
	{
		$articles_access = $contents_access = 1;
	}
	if (EXTRAS_NEW == 1 || EXTRAS_EDIT == 1 || EXTRAS_DELETE == 1)
	{
		$extras_access = $contents_access = 1;
	}
	if (COMMENTS_NEW == 1 || COMMENTS_EDIT == 1 || COMMENTS_DELETE == 1)
	{
		$comments_access = $contents_access = 1;
	}
	if (USERS_NEW == 1 || USERS_EDIT == 1 || USERS_DELETE == 1)
	{
		$users_access = $access_access = 1;
	}
	if (GROUPS_NEW == 1 || GROUPS_EDIT == 1 || GROUPS_DELETE == 1)
	{
		$groups_access = $access_access = 1;
	}
	if (MODULES_INSTALL == 1 || MODULES_EDIT == 1 || MODULES_UNINSTALL == 1)
	{
		$modules_access = $system_access = 1;
	}
	if (SETTINGS_EDIT == 1)
	{
		$settings_access = $system_access = 1;
	}

	/* collect contents output */

	$counter = 1;
	if ($contents_access == 1)
	{
		$counter++;
		$output .= '<li class="admin-js-item-panel-admin admin-item-panel-admin admin-item-contents"><span>' . l('contents') . '</span><ul class="admin-list-panel-children-admin admin-list-contents">';
		if ($categories_access == 1)
		{
			$output .= '<li>' . anchor_element('internal', '', '', l('categories'), 'admin/view/categories') . '</li>';
		}
		if ($articles_access == 1)
		{
			$output .= '<li>' . anchor_element('internal', '', '', l('articles'), 'admin/view/articles') . '</li>';
		}
		if ($extras_access == 1)
		{
			$output .= '<li>' . anchor_element('internal', '', '', l('extras'), 'admin/view/extras') . '</li>';
		}
		if ($comments_access == 1)
		{
			$output .= '<li>' . anchor_element('internal', '', '', l('comments'), 'admin/view/comments') . '</li>';
		}
		$output .= '</ul></li>';
	}

	/* collect access output */

	if ($access_access == 1)
	{
		$counter++;
		$output .= '<li class="admin-js-item-panel-admin admin-item-panel-admin admin-item-access"><span>' . l('access') . '</span><ul class="admin-list-panel-children-admin admin-list-access">';
		if (MY_ID)
		{
			$output .= '<li>' . anchor_element('internal', '', '', l('profile'), 'admin/edit/users/' . MY_ID) . '</li>';
		}
		if ($users_access == 1)
		{
			$output .= '<li>' . anchor_element('internal', '', '', l('users'), 'admin/view/users') . '</li>';
		}
		if ($groups_access == 1)
		{
			$output .= '<li>' . anchor_element('internal', '', '', l('groups'), 'admin/view/groups') . '</li>';
		}
		$output .= '</ul></li>';
	}

	/* collect system output */

	if ($system_access == 1)
	{
		$counter++;
		$output .= '<li class="admin-js-item-panel-admin admin-item-panel-admin admin-item-system"><span>' . l('system') . '</span><ul class="admin-list-panel-children-admin admin-list-stystem">';
		if ($modules_access == 1)
		{
			$output .= '<li>' . anchor_element('internal', '', '', l('modules'), 'admin/view/modules');

			/* collect modules list */

			$admin_panel_list_modules = Redaxscript\Hook::trigger('admin_panel_list_modules');
			if ($admin_panel_list_modules)
			{
				$output .= '<ul class="admin-js-admin-list-panel-children-admin admin-list-panel-children-admin">' . $admin_panel_list_modules . '</ul>';
			}
			$output .= '</li>';
		}
		if ($settings_access == 1)
		{
			$output .= '<li>' . anchor_element('internal', '', '', l('settings'), 'admin/edit/settings') . '</li>';
		}
		$output .= '</ul></li>';
	}

	/* collect profile */

	if (MY_USER && MY_ID)
	{
		$counter++;
		$output .= '<li class="admin-js-item-panel-admin admin-item-panel-admin admin-item-profile">' . anchor_element('internal', '', '', l('profile'), 'admin/edit/users/' . MY_ID) . '</li>';
	}

	/* collect logout */

	$output .= '<li class="admin-js-item-panel-admin admin-item-panel-admin admin-item-logout">' . anchor_element('internal', '', '', l('logout'), 'logout') . '</li>';

	/* collect list output */

	if ($output)
	{
		$output = '<ul class="admin-js-list-panel-admin admin-list-panel-admin admin-c' . $counter . '">' . $output . '</ul>';
	}
	$output .= Redaxscript\Hook::trigger(__FUNCTION__ . '_end');
	echo $output;
}

/**
 * admin dock
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 *
 * @param string $table
 * @param integer $id
 * @return string
 */

function admin_dock($table = '', $id = '')
{
	$output = Redaxscript\Hook::trigger(__FUNCTION__ . '_start');

	/* define access variables */

	$edit = constant(strtoupper($table) . '_EDIT');
	$delete = constant(strtoupper($table) . '_DELETE');

	/* collect output */

	if ($edit == 1 || $delete == 1)
	{
		$output .= '<div class="admin-wrapper-dock-admin"><div class="admin-js-dock-admin admin-box-dock-admin rs-clearfix">';
		if ($edit == 1)
		{
			$output .= anchor_element('internal', '', 'admin-js-link-dock-admin admin-link-dock-admin admin-link-unpublish', l('unpublish'), 'admin/unpublish/' . $table . '/' . $id . '/' . TOKEN, l('unpublish'));
			$output .= anchor_element('internal', '', 'admin-js-link-dock-admin admin-link-dock-admin admin-link-edit', l('edit'), 'admin/edit/' . $table . '/' . $id, l('edit'));
		}
		if ($delete == 1)
		{
			$output .= anchor_element('internal', '', 'admin-js-confirm admin-js-link-dock-admin admin-link-dock-admin admin-link-delete', l('delete'), 'admin/delete/' . $table . '/' . $id . '/' . TOKEN, l('delete'));
		}
		$output .= '</div></div>';
	}
	$output .= Redaxscript\Hook::trigger(__FUNCTION__ . '_end');
	return $output;
}

/**
 * admin notification
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_notification()
{
	$output = Redaxscript\Hook::trigger(__FUNCTION__ . '_start');

	/* insecure file warning */

	if (MY_ID == 1)
	{
		if (file_exists('install.php'))
		{
			$output .= '<div class="admin-box-note admin-note-warning">' . l('file_remove') . l('colon') . ' install.php' . l('point') . '</div>';
		}
		if (is_writable('config.php'))
		{
			$output .= '<div class="admin-box-note admin-note-warning">' . l('file_permission_revoke') . l('colon') . ' config.php' . l('point') . '</div>';
		}
	}
	$output .= Redaxscript\Hook::trigger(__FUNCTION__ . '_end');
	echo $output;
}

/**
 * admin control
 *
 * @since 2.0.0
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 *
 * @param string $type
 * @param string $table
 * @param integer $id
 * @param string $alias
 * @param integer $status
 * @param string $new
 * @param string $edit
 * @param string $delete
 * @return string
 */

function admin_control($type = '', $table = '', $id = '', $alias = '', $status = '', $new = '', $edit = '', $delete = '')
{
	$output = Redaxscript\Hook::trigger(__FUNCTION__ . '_start');

	/* define access variables */

	if ($type == 'access' && $id == 1)
	{
		$delete = 0;
	}
	if ($type == 'modules_not_installed')
	{
		$edit = $delete = 0;
	}

	/* collect modules output */

	if ($new == 1 && $type == 'modules_not_installed')
	{
		$output .= '<li class="admin-item-control-admin admin-link-install">' . anchor_element('internal', '', 'install', l('install'), 'admin/install/' . $table . '/' . $alias . '/' . TOKEN) . '</li>';
	}

	/* collect contents output */

	if ($type == 'contents')
	{
		if ($status == 2)
		{
			$output .= '<li class="admin-item-control-admin admin-item-future-posting"><span>' . l('future_posting') . '</span></li>';
		}
		if ($edit == 1)
		{
			if ($status == 1)
			{
				$output .= '<li class="admin-item-control-admin admin-item-unpublish">' . anchor_element('internal', '', '', l('unpublish'), 'admin/unpublish/' . $table . '/' . $id . '/' . TOKEN) . '</li>';
			}
			else if ($status == 0)
			{
				$output .= '<li class="admin-item-control-admin admin-item-publish">' . anchor_element('internal', '', '', l('publish'), 'admin/publish/' . $table . '/' . $id . '/' . TOKEN) . '</li>';
			}
		}
	}

	/* collect access and system output */

	if ($edit == 1 && ($type == 'access' && $id > 1 || $type == 'modules_installed'))
	{
		if ($status == 1)
		{
			$output .= '<li class="admin-item-control-admin admin-item-disable">' . anchor_element('internal', '', '', l('disable'), 'admin/disable/' . $table . '/' . $id . '/' . TOKEN) . '</li>';
		}
		else if ($status == 0)
		{
			$output .= '<li class="admin-item-control-admin admin-item-enable">' . anchor_element('internal', '', '', l('enable'), 'admin/enable/' . $table . '/' . $id . '/' . TOKEN) . '</li>';
		}
	}

	/* collect general edit and delete output */

	if ($edit == 1)
	{
		$output .= '<li class="admin-item-control-admin admin-item-edit">' . anchor_element('internal', '', '', l('edit'), 'admin/edit/' . $table . '/' . $id) . '</li>';
	}
	if ($delete == 1)
	{
		if ($type == 'modules_installed')
		{
			$output .= '<li class="admin-item-control-admin admin-item-uninstall">' . anchor_element('internal', '', 'js_confirm', l('uninstall'), 'admin/uninstall/' . $table . '/' . $alias . '/' . TOKEN) . '</li>';
		}
		else
		{
			$output .= '<li class="admin-item-control-admin admin-item-delete">' . anchor_element('internal', '', 'js_confirm', l('delete'), 'admin/delete/' . $table . '/' . $id . '/' . TOKEN) . '</li>';
		}
	}

	/* collect list output */

	if ($output)
	{
		$output = '<ul class="admin-list-control-admin">' . $output . '</ul>';
	}
	$output .= Redaxscript\Hook::trigger(__FUNCTION__ . '_end');
	return $output;
}