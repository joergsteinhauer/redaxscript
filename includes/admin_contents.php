<?php

/**
 * admin contents list
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_contents_list()
{
	$output = Redaxscript\Hook::trigger('adminContentListStart');

	/* define access variables */

	$table_new = TABLE_NEW;
	if (TABLE_PARAMETER == 'comments')
	{
		$articles_total = Redaxscript\Db::forTablePrefix('articles')->count();
		$articles_comments_disable = Redaxscript\Db::forTablePrefix('articles')->where('comments', 0)->count();
		if ($articles_total == $articles_comments_disable)
		{
			$table_new = 0;
		}
	}

	/* switch table */

	switch (TABLE_PARAMETER)
	{
		case 'categories':
			$wording_single = 'category';
			$wording_parent = 'category_parent';
			break;
		case 'articles':
			$wording_single = 'article';
			$wording_parent = 'category';
			break;
		case 'extras':
			$wording_single = 'extra';
			break;
		case 'comments':
			$wording_single = 'comment';
			$wording_parent = 'article';
			break;
	}

	/* query contents */

	$result = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->orderByAsc('rank')->findArray();
	$num_rows = count($result);

	/* collect listing output */

	$output .= '<h2 class="title_content">' . l(TABLE_PARAMETER) . '</h2>';
	$output .= '<div class="wrapper_button_admin">';
	if ($table_new == 1)
	{
		$output .= anchor_element('internal', '', 'button_admin button_plus_admin', l($wording_single . '_new'), 'admin/new/' . TABLE_PARAMETER);
	}
	if (TABLE_EDIT == 1 && $num_rows)
	{
		$output .= anchor_element('internal', '', 'button_admin button_sort_admin', l('sort'), 'admin/sort/' . TABLE_PARAMETER . '/' . TOKEN);
	}
	$output .= '</div><div class="wrapper_table_admin"><table class="table table_admin">';

	/* collect thead */

	$output .= '<thead><tr><th class="s3o6 column_first">' . l('title') . '</th><th class="';
	if (TABLE_PARAMETER != 'extras')
	{
		$output .= 's1o6';
	}
	else
	{
		$output .= 's3o6';
	}
	$output .= ' column_second">';
	if (TABLE_PARAMETER == 'comments')
	{
		$output .= l('identifier');
	}
	else
	{
		$output .= l('alias');
	}
	$output .= '</th>';
	if (TABLE_PARAMETER != 'extras')
	{
		$output .= '<th class="column_third">' . l($wording_parent) . '</th>';
	}
	$output .= '<th class="column_move column_last">' . l('rank') . '</th></tr></thead>';

	/* collect tfoot */

	$output .= '<tfoot><tr><td class="column_first">' . l('title') . '</td><td class="column_second">';
	if (TABLE_PARAMETER == 'comments')
	{
		$output .= l('identifier');
	}
	else
	{
		$output .= l('alias');
	}
	$output .= '</td>';
	if (TABLE_PARAMETER != 'extras')
	{
		$output .= '<td class="column_third">' . l($wording_parent) . '</td>';
	}
	$output .= '<td class="column_move column_last">' . l('rank') . '</td></tr></tfoot>';
	if ($result == '' || $num_rows == '')
	{
		$error = l($wording_single . '_no') . l('point');
	}
	else if ($result)
	{
		$accessValidator = new Redaxscript\Validator\Access();
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

				/* prepare name */

				if (TABLE_PARAMETER == 'comments')
				{
					$name = truncate($author . l('colon') . ' ' . strip_tags($text), 80, '...');
				}
				else
				{
					$name = $title;
				}

				/* build class string */

				if ($status == 1)
				{
					$class_status = '';
				}
				else
				{
					$class_status = 'row_disabled';
				}

				/* build route */

				if (TABLE_PARAMETER != 'extras' && $status == 1)
				{
					if (TABLE_PARAMETER == 'categories' && $parent == 0 || TABLE_PARAMETER == 'articles' && $category == 0)
					{
						$route = $alias;
					}
					else
					{
						$route = build_route(TABLE_PARAMETER, $id);
					}
				}
				else
				{
					$route = '';
				}

				/* collect tbody output */

				if (TABLE_PARAMETER == 'categories')
				{
					if ($before != $parent)
					{
						$output .= '<tbody><tr class="row_group"><td colspan="4">';
						if ($parent)
						{
							$output .= Redaxscript\Db::forTablePrefix('categories')->where('id', $parent)->findOne()->title;
						}
						else
						{
							$output .= l('none');
						}
						$output .= '</td></tr>';
					}
					$before = $parent;
				}
				if (TABLE_PARAMETER == 'articles')
				{
					if ($before != $category)
					{
						$output .= '<tbody><tr class="row_group"><td colspan="4">';
						if ($category)
						{
							$output .= Redaxscript\Db::forTablePrefix('categories')->where('id', $category)->findOne()->title;
						}
						else
						{
							$output .= l('uncategorized');
						}
						$output .= '</td></tr>';
					}
					$before = $category;
				}
				if (TABLE_PARAMETER == 'comments')
				{
					if ($before != $article)
					{
						$output .= '<tbody><tr class="row_group"><td colspan="4">';
						if ($article)
						{
							$output .= Redaxscript\Db::forTablePrefix('articles')->where('id', $article)->findOne()->title;
						}
						else
						{
							$output .= l('none');
						}
						$output .= '</td></tr>';
					}
					$before = $article;
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
				$output .= '><td class="column_first">';
				if ($language)
				{
					$output .= '<span class="icon_flag language_' . $language . '" title="' . l($language) . '">' . $language . '</span>';
				}
				if ($status == 1)
				{
					$output .= anchor_element('internal', '', 'link_view', $name, $route);
				}
				else
				{
					$output .= $name;
				}

				/* collect control output */

				$output .= admin_control('contents', TABLE_PARAMETER, $id, $alias, $status, TABLE_NEW, TABLE_EDIT, TABLE_DELETE);

				/* collect alias and id output */

				$output .= '</td><td class="column_second">';
				if (TABLE_PARAMETER == 'comments')
				{
					$output .= $id;
				}
				else
				{
					$output .= $alias;
				}
				$output .= '</td>';

				/* collect parent output */

				if (TABLE_PARAMETER != 'extras')
				{
					$output .= '<td class="column_third">';
					if (TABLE_PARAMETER == 'categories')
					{
						if ($parent)
						{
							$parent_title = Redaxscript\Db::forTablePrefix('categories')->where('id', $parent)->findOne()->title;
							$output .= anchor_element('internal', '', 'link_parent', $parent_title, 'admin/edit/categories/' . $parent);
						}
						else
						{
							$output .= l('none');
						}
					}
					if (TABLE_PARAMETER == 'articles')
					{
						if ($category)
						{
							$category_title = Redaxscript\Db::forTablePrefix('categories')->where('id', $category)->findOne()->title;
							$output .= anchor_element('internal', '', 'link_parent', $category_title, 'admin/edit/categories/' . $category);
						}
						else
						{
							$output .= l('uncategorized');
						}
					}
					if (TABLE_PARAMETER == 'comments')
					{
						if ($article)
						{
							$article_title = Redaxscript\Db::forTablePrefix('articles')->where('id', $article)->findOne()->title;
							$output .= anchor_element('internal', '', 'link_parent', $article_title, 'admin/edit/articles/' . $article);
						}
						else
						{
							$output .= l('none');
						}
					}
					$output .= '</td>';
				}
				$output .= '<td class="column_move column_last">';

				/* collect control output */

				if (TABLE_EDIT == 1)
				{
					$rank_desc = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->max('rank');
					if ($rank > 1)
					{
						$output .= anchor_element('internal', '', 'move_up', l('up'), 'admin/up/' . TABLE_PARAMETER . '/' . $id . '/' . TOKEN);
					}
					else
					{
						$output .= '<span class="move_up">' . l('up') . '</span>';
					}
					if ($rank < $rank_desc)
					{
						$output .= anchor_element('internal', '', 'move_down', l('down'), 'admin/down/' . TABLE_PARAMETER . '/' . $id . '/' . TOKEN);
					}
					else
					{
						$output .= '<span class="move_down">' . l('down') . '</span>';
					}
					$output .= '</td>';
				}
				$output .= '</tr>';

				/* collect tbody output */

				if (TABLE_PARAMETER == 'categories')
				{
					if ($before != $parent)
					{
						$output .= '</tbody>';
					}
				}
				if (TABLE_PARAMETER == 'articles')
				{
					if ($before != $category)
					{
						$output .= '</tbody>';
					}
				}
				if (TABLE_PARAMETER == 'comments')
				{
					if ($before != $article)
					{
						$output .= '</tbody>';
					}
				}
			}
			else
			{
				$counter++;
			}
		}

		/* handle access */

		if ($num_rows == $counter)
		{
			$error = l('access_no') . l('point');
		}
	}

	/* handle error */

	if ($error)
	{
		$output .= '<tbody><tr><td colspan="4">' . $error . '</td></tr></tbody>';
	}
	$output .= '</table></div>';
	$output .= Redaxscript\Hook::trigger('adminContentListEnd');
	echo $output;
}

/**
 * admin contents form
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

function admin_contents_form()
{
	$output = Redaxscript\Hook::trigger('adminContentFormStart');

	/* switch table */

	switch (TABLE_PARAMETER)
	{
		case 'categories':
			$wording_single = 'category';
			$wording_sibling = 'category_sibling';
			break;
		case 'articles':
			$wording_single = 'article';
			$wording_sibling = 'article_sibling';
			break;
		case 'extras':
			$wording_single = 'extra';
			$wording_sibling = 'extra_sibling';
			break;
		case 'comments':
			$wording_single = 'comment';
			break;
	}

	/* define fields for existing user */

	if (ADMIN_PARAMETER == 'edit' && ID_PARAMETER)
	{
		/* query content */

		$result = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->where('id', ID_PARAMETER)->findArray();
		$r = $result[0];
		if ($r)
		{
			foreach ($r as $key => $value)
			{
				$$key = stripslashes($value);
			}
		}
		if (TABLE_PARAMETER == 'comments')
		{
			$wording_headline = $author;
		}
		else
		{
			$wording_headline = $title;
		}
		if (TABLE_PARAMETER != 'categories')
		{
			$text = htmlspecialchars($text);
		}
		$wording_submit = l('save');
		$route = 'admin/process/' . TABLE_PARAMETER . '/' . $id;
	}

	/* else define fields for new content */

	else if (ADMIN_PARAMETER == 'new')
	{
		if (TABLE_PARAMETER == 'comments')
		{
			$author = MY_USER;
			$email = MY_EMAIL;
			$code_readonly = ' readonly="readonly"';
		}
		if (TABLE_PARAMETER == 'categories')
		{
			$sibling = 0;
			$parent = 0;
		}
		if (TABLE_PARAMETER == 'articles' || TABLE_PARAMETER == 'extras')
		{
			$category = 0;
			$headline = 1;
		}
		if (TABLE_PARAMETER == 'articles')
		{
			$sibling = 0;
			$infoline = 0;
			$comments = 0;
		}
		if (TABLE_PARAMETER == 'extras')
		{
			$sibling = 0;
		}
		$status = 1;
		$rank = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->max('rank') + 1;
		$access = null;
		$wording_headline = l($wording_single . '_new');
		$wording_submit = l('create');
		$route = 'admin/process/' . TABLE_PARAMETER;
	}

	/* collect output */

	$output .= '<h2 class="title_content">' . $wording_headline . '</h2>';
	$output .= form_element('form', 'form_admin', 'js_validate_form js_tab form_admin hidden_legend', '', '', '', 'action="' . REWRITE_ROUTE . $route . '" method="post"');

	/* collect tab list output */

	$output .= '<ul class="js_list_tab list_tab list_tab_admin">';
	$output .= '<li class="js_item_active item_first item_active">' . anchor_element('internal', '', '', l($wording_single), FULL_ROUTE . '#tab-1') . '</li>';
	$output .= '<li class="item_second">' . anchor_element('internal', '', '', l('customize'), FULL_ROUTE . '#tab-2') . '</li>';
	if (TABLE_PARAMETER != 'categories')
	{
		$output .= '<li class="item_last">' . anchor_element('internal', '', '', l('date'), FULL_ROUTE . '#tab-3') . '</li>';
	}
	$output .= '</ul>';

	/* collect tab box output */

	$output .= '<div class="js_box_tab box_tab box_tab_admin">';

	/* collect content set */

	$output .= form_element('fieldset', 'tab-1', 'js_set_tab js_set_active set_tab set_tab_admin set_active', '', '', l($wording_single)) . '<ul>';
	if (TABLE_PARAMETER == 'comments')
	{
		$output .= '<li>' . form_element('text', 'author', 'field_text_admin field_note', 'author', $author, '* ' . l('author'), 'maxlength="50" required="required" autofocus="autofocus"' . $code_readonly) . '</li>';
		$output .= '<li>' . form_element('email', 'email', 'field_text_admin field_note', 'email', $email, '* ' . l('email'), 'maxlength="50" required="required"' . $code_readonly) . '</li>';
		$output .= '<li>' . form_element('url', 'url', 'field_text_admin', 'url', $url, l('url'), 'maxlength="50"') . '</li>';
	}
	else
	{
		$output .= '<li>' . form_element('text', 'title', 'js_generate_alias_input field_text_admin field_note', 'title', $title, l('title'), 'maxlength="50" required="required" autofocus="autofocus"') . '</li>';
		$output .= '<li>' . form_element('text', 'alias', 'js_generate_alias_output field_text_admin field_note', 'alias', $alias, l('alias'), 'maxlength="50" required="required"') . '</li>';
	}
	if (TABLE_PARAMETER == 'categories' || TABLE_PARAMETER == 'articles')
	{
		$output .= '<li>' . form_element('textarea', 'description', 'js_auto_resize field_textarea_admin field_small', 'description', $description, l('description'), 'rows="1" cols="15"') . '</li>';
		$output .= '<li>' . form_element('textarea', 'keywords', 'js_auto_resize js_generate_keyword_output field_textarea_admin field_small', 'keywords', $keywords, l('keywords'), 'rows="1" cols="15"') . '</li>';
	}
	if (TABLE_PARAMETER != 'categories')
	{
		$output .= '<li>' . form_element('textarea', 'text', 'js_auto_resize js_generate_keyword_input js_editor_textarea field_textarea_admin field_note', 'text', $text, l('text'), 'rows="5" cols="100" required="required"') . '</li>';
	}
	$output .= '</ul></fieldset>';

	/* collect customize set */

	$output .= form_element('fieldset', 'tab-2', 'js_set_tab set_tab set_tab_admin', '', '', l('customize')) . '<ul>';

	/* languages directory object */

	$languages_directory = new Redaxscript\Directory();
	$languages_directory->init('languages');
	$languages_directory_array = $languages_directory->getArray();

	/* build languages select */

	$language_array[l('select')] = '';
	foreach ($languages_directory_array as $value)
	{
		$value = substr($value, 0, 2);
		$language_array[l($value, '_index')] = $value;
	}
	$output .= '<li>' . select_element('language', 'field_select_admin', 'language', $language_array, $language, l('language')) . '</li>';
	if (TABLE_PARAMETER == 'categories' || TABLE_PARAMETER == 'articles')
	{
		/* templates directory object */

		$templates_directory = new Redaxscript\Directory();
		$templates_directory->init('templates', array(
			'admin',
			'install'
		));
		$templates_directory_array = $templates_directory->getArray();

		/* build templates select */

		$template_array[l('select')] = '';
		foreach ($templates_directory_array as $value)
		{
			$template_array[$value] = $value;
		}
		$output .= '<li>' . select_element('template', 'field_select_admin', 'template', $template_array, $template, l('template')) . '</li>';
	}

	/* build sibling select */

	if (TABLE_PARAMETER == 'categories' || TABLE_PARAMETER == 'articles' || TABLE_PARAMETER == 'extras')
	{
		$sibling_array[l('none')] = 0;
		$sibling_result = Redaxscript\Db::forTablePrefix(TABLE_PARAMETER)->orderByAsc('rank')->findArray();
		if ($sibling_result)
		{
			foreach ($sibling_result as $s)
			{
				if (ID_PARAMETER != $s['id'])
				{
					$sibling_array[$s['title']] = $s['id'];
				}
			}
		}
		$output .= '<li>' . select_element('sibling', 'field_select_admin', 'sibling', $sibling_array, $sibling, l($wording_sibling)) . '</li>';
	}

	/* build category and parent select */

	if (TABLE_PARAMETER != 'comments')
	{
		if (TABLE_PARAMETER == 'extras')
		{
			$category_array[l('all')] = 0;
		}
		else
		{
			$category_array[l('none')] = 0;
		}
		$categories_result = Redaxscript\Db::forTablePrefix('categories')->orderByAsc('rank')->findArray();
		if ($categories_result)
		{
			foreach ($categories_result as $c)
			{
				if (TABLE_PARAMETER != 'categories')
				{
					$category_array[$c['title']] = $c['id'];
				}
				else if (ID_PARAMETER != $c['id'] && $c['parent'] == 0)
				{
					$category_array[$c['title']] = $c['id'];
				}
			}
		}
		if (TABLE_PARAMETER == 'categories')
		{
			$output .= '<li>' . select_element('parent', 'field_select_admin', 'parent', $category_array, $parent, l('category_parent')) . '</li>';
		}
		else
		{
			$output .= '<li>' . select_element('category', 'field_select_admin', 'category', $category_array, $category, l('category')) . '</li>';
		}
	}

	/* build article select */

	if (TABLE_PARAMETER == 'extras' || TABLE_PARAMETER == 'comments')
	{
		if (TABLE_PARAMETER == 'extras')
		{
			$article_array[l('all')] = 0;
		}
		$articles = Redaxscript\Db::forTablePrefix('articles');
		if (TABLE_PARAMETER == 'comments')
		{
			$articles->where('comments', 0);
		}
		$articles_result = $articles->orderByAsc('rank')->findArray();
		if ($articles_result)
		{
			foreach ($articles_result as $a)
			{
				$article_array[$a['title']] = $a['id'];
			}
		}
		$output .= '<li>' . select_element('article', 'field_select_admin', 'article', $article_array, $article, l('article')) . '</li>';
	}
	if (TABLE_PARAMETER == 'articles' || TABLE_PARAMETER == 'extras')
	{
		$output .= '<li>' . select_element('headline', 'field_select_admin', 'headline', array(
			l('enable') => 1,
			l('disable') => 0
		), $headline, l('headline')) . '</li>';
	}
	if (TABLE_PARAMETER == 'articles')
	{
		$output .= '<li>' . select_element('infoline', 'field_select_admin', 'infoline', array(
			l('enable') => 1,
			l('disable') => 0
		), $infoline, l('infoline')) . '</li>';
		$output .= '<li>' . select_element('comments', 'field_select_admin', 'comments', array(
			l('enable') => 1,
			l('freeze') => 2,
			l('restrict') => 3,
			l('disable') => 0
		), $comments, l('comments')) . '</li>';
	}
	if ($status != 2)
	{
		$output .= '<li>' . select_element('status', 'field_select_admin', 'status', array(
			l('publish') => 1,
			l('unpublish') => 0
		), $status, l('status')) . '</li>';
	}

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
		$output .= '<li>' . select_element('access', 'field_select_admin', 'access', $access_array, $access, l('access'), 'multiple="multiple"') . '</li>';
	}
	$output .= '</ul></fieldset>';

	/* collect date set */

	if (TABLE_PARAMETER != 'categories')
	{
		$output .= form_element('fieldset', 'tab-3', 'js_set_tab set_tab set_tab_admin', '', '', l('date')) . '<ul>';
		$output .= '<li>' . select_date('day', 'field_select_admin', 'day', $date, 'd', 1, 32, l('day')) . '</li>';
		$output .= '<li>' . select_date('month', 'field_select_admin', 'month', $date, 'm', 1, 13, l('month')) . '</li>';
		$output .= '<li>' . select_date('year', 'field_select_admin', 'year', $date, 'Y', 2000, 2021, l('year')) . '</li>';
		$output .= '<li>' . select_date('hour', 'field_select_admin', 'hour', $date, 'H', 0, 24, l('hour')) . '</li>';
		$output .= '<li>' . select_date('minute', 'field_select_admin', 'minute', $date, 'i', 0, 60, l('minute')) . '</li>';
		$output .= '</ul></fieldset>';
	}
	$output .= '</div>';

	/* collect hidden output */

	if (TABLE_PARAMETER != 'comments')
	{
		$output .= form_element('hidden', '', '', 'author', MY_USER);
	}
	if ($status == 2)
	{
		$output .= form_element('hidden', '', '', 'publish', 2);
	}
	$output .= form_element('hidden', '', '', 'rank', $rank);
	$output .= form_element('hidden', '', '', 'token', TOKEN);

	/* cancel button */

	if (TABLE_EDIT == 1 || TABLE_DELETE == 1)
	{
		$cancel_route = 'admin/view/' . TABLE_PARAMETER;
	}
	else
	{
		$cancel_route = 'admin';
	}
	$output .= anchor_element('internal', '', 'js_cancel button_admin button_large button_cancel_admin', l('cancel'), $cancel_route);

	/* delete button */

	if (TABLE_DELETE == 1 && $id)
	{
		$output .= anchor_element('internal', '', 'js_delete js_confirm button_admin button_large button_delete_admin', l('delete'), 'admin/delete/' . TABLE_PARAMETER . '/' . $id . '/' . TOKEN);
	}

	/* submit button */

	if (TABLE_NEW == 1 || TABLE_EDIT == 1)
	{
		$output .= form_element('button', '', 'js_submit button_admin button_large button_submit_admin', ADMIN_PARAMETER, $wording_submit);
	}
	$output .= '</form>';
	$output .= Redaxscript\Hook::trigger('adminContentFormEnd');
	echo $output;
}
