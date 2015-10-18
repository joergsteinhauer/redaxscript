<?php

/**
 * comments
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Comments
 * @author Henry Ruhs
 *
 * @param integer $article
 * @param string $route
 */

function comments($article = '', $route = '')
{
	$output = Redaxscript\Hook::trigger(__FUNCTION__ . '_start');

	/* query comments */

	$comments = Redaxscript\Db::forTablePrefix('comments')
		->where(array(
			'status' => 1,
			'article' => $article
		))
		->whereIn('language', array(
			Redaxscript\Registry::get('language'),
			''
		))
		->orderGlobal('rank');

	/* query result */

	$result = $comments->findArray();
	if ($result)
	{
		$num_rows = count($result);
		$sub_maximum = ceil($num_rows / s('limit'));
		$sub_active = LAST_SUB_PARAMETER;

		/* sub parameter */

		if (LAST_SUB_PARAMETER > $sub_maximum || LAST_SUB_PARAMETER == '')
		{
			$sub_active = 1;
		}
		else
		{
			$offset_string = ($sub_active - 1) * s('limit') . ', ';
		}
	}
	$comments->limit($offset_string . s('limit'));

	/* query result */

	$result = $comments->findArray();
	$num_rows_active = count($result);

	/* handle error */

	if ($result == '' || $num_rows == '')
	{
		$error = l('comment_no');
	}

	/* collect output */

	else if ($result)
	{
		$accessValidator = new Redaxscript\Validator\Access();
		$output .= '<div class="box_line"></div>';
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

				/* collect headline output */

				$output .= Redaxscript\Hook::trigger('comment_start', $r) . '<h3 id="comment-' . $id . '" class="title_comment">';
				if ($url)
				{
					$output .= anchor_element('external', '', '', $author, $url, '', 'rel="nofollow"');
				}
				else
				{
					$output .= $author;
				}
				$output .= '</h3>';

				/* collect box output */

				$output .= infoline('comments', $id, $author, $date);
				$output .= '<div class="box_comment">' . $text . '</div>' . Redaxscript\Hook::trigger('comment_end', $r);

				/* admin dock */

				if (LOGGED_IN == TOKEN && FIRST_PARAMETER != 'logout')
				{
					$output .= admin_dock('comments', $id);
				}
			}
			else
			{
				$counter++;
			}
		}

		/* handle access */

		if ($num_rows_active == $counter)
		{
			$error = l('access_no');
		}
	}

	/* handle error */

	if ($error)
	{
		$output = '<div class="box_comment_error">' . $error . l('point') . '</div>';
	}
	$output .= Redaxscript\Hook::trigger(__FUNCTION__ . '_end');
	echo $output;

	/* call pagination as needed */

	if ($sub_maximum > 1 && s('pagination') == 1)
	{
		pagination($sub_active, $sub_maximum, $route);
	}
}

/**
 * comment form
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Comments
 * @author Henry Ruhs
 *
 * @param integer $article
 * @param string $language
 */

function comment_form($article = '', $language = '')
{
	$output = Redaxscript\Hook::trigger(__FUNCTION__ . '_start');

	/* disable fields if attack blocked */

	if (ATTACK_BLOCKED > 9)
	{
		$code_readonly = $code_disabled = ' disabled="disabled"';
	}

	/* define fields if logged in */

	else if (LOGGED_IN == TOKEN)
	{
		$author = MY_USER;
		$email = MY_EMAIL;
		$code_readonly = ' readonly="readonly"';
	}

	/* captcha object */

	if (s('captcha') > 0)
	{
		$captcha = new Redaxscript\Captcha(Redaxscript\Language::getInstance());
		$captcha->init();
	}

	/* collect output */

	$output .= '<h2 class="title_content">' . l('comment_new') . '</h2>';
	$output .= form_element('form', 'form_comment', 'js_validate_form form_default form_comment', '', '', '', 'method="post"');
	$output .= form_element('fieldset', '', 'set_comment', '', '', l('fields_required') . l('point')) . '<ul>';
	$output .= '<li>' . form_element('text', 'author', 'rs-field-text rs-field-note', 'author', $author, '* ' . l('author'), 'maxlength="50" required="required"' . $code_readonly) . '</li>';
	$output .= '<li>' . form_element('email', 'email', 'rs-field-text rs-field-note', 'email', $email, '* ' . l('email'), 'maxlength="50" required="required"' . $code_readonly) . '</li>';
	$output .= '<li>' . form_element('url', 'url', 'rs-field-text', 'url', '', l('url'), 'maxlength="50"' . $code_disabled) . '</li>';
	$output .= '<li>' . form_element('textarea', 'text', 'rs-js-auto-resize rs-js-editor-textarea rs-field-textarea rs-field-note', 'text', '', '* ' . l('comment'), 'rows="5" cols="100" required="required"' . $code_disabled) . '</li>';

	/* collect captcha task output */

	if (LOGGED_IN != TOKEN && s('captcha') > 0)
	{
		$output .= '<li>' . form_element('number', 'task', 'rs-field-text rs-field-note', 'task', '', $captcha->getTask(), 'min="1" max="20" required="required"' . $code_disabled) . '</li>';
	}
	$output .= '</ul></fieldset>';

	/* collect hidden output */

	$output .= form_element('hidden', '', '', 'language', $language);
	$output .= form_element('hidden', '', '', 'date', NOW);
	$output .= form_element('hidden', '', '', 'article', $article);

	/* collect captcha solution output */

	if (s('captcha') > 0)
	{
		$captchaHash = new Redaxscript\Hash(Redaxscript\Config::getInstance());
		$captchaHash->init($captcha->getSolution());
		if (LOGGED_IN == TOKEN)
		{
			$output .= form_element('hidden', '', '', 'task', $captchaHash->getRaw());
		}
		$output .= form_element('hidden', '', '', 'solution', $captchaHash->getHash());
	}

	/* collect hidden and button output */

	$output .= form_element('hidden', '', '', 'token', TOKEN);
	$output .= form_element('button', '', 'rs-js-submit rs-button-default', 'comment_post', l('create'), '', $code_disabled);
	$output .= '</form>';
	$output .= Redaxscript\Hook::trigger(__FUNCTION__ . '_end');
	$_SESSION[ROOT . '/comment'] = 'visited';
	echo $output;
}

/**
 * comment post
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Comments
 * @author Henry Ruhs
 */

function comment_post()
{
	$emailValidator = new Redaxscript\Validator\Email();
	$captchaValidator = new Redaxscript\Validator\Captcha();
	$urlValidator = new Redaxscript\Validator\Url();

	/* clean post */

	if (ATTACK_BLOCKED < 10 && $_SESSION[ROOT . '/comment'] == 'visited')
	{
		$author = $r['author'] = clean($_POST['author'], 0);
		$email = $r['email'] = clean($_POST['email'], 3);
		$url = $r['url'] = clean($_POST['url'], 4);
		$text = break_up($_POST['text']);
		$text = $r['text'] = clean($text, 1);
		$r['language'] = clean($_POST['language'], 0);
		$r['date'] = clean($_POST['date'], 5);
		$article = $r['article'] = clean($_POST['article'], 0);
		$r['rank'] = Redaxscript\Db::forTablePrefix('comments')->max('rank') + 1;
		$r['access'] = Redaxscript\Db::forTablePrefix('articles')->whereIdIs($article)->access;
		if ($r['access'] == '')
		{
			$r['access'] = null;
		}
		$task = $_POST['task'];
		$solution = $_POST['solution'];
		$route = build_route('articles', $article);
	}

	/* validate post */

	if ($author == '')
	{
		$error = l('author_empty');
	}
	else if ($email == '')
	{
		$error = l('email_empty');
	}
	else if ($text == '')
	{
		$error = l('comment_empty');
	}
	else if ($emailValidator->validate($email) == Redaxscript\Validator\ValidatorInterface::FAILED)
	{
		$error = l('email_incorrect');
	}
	else if ($url && $urlValidator->validate($url) == Redaxscript\Validator\ValidatorInterface::FAILED)
	{
		$error = l('url_incorrect');
	}
	else if ($captchaValidator->validate($task, $solution) == Redaxscript\Validator\ValidatorInterface::FAILED)
	{
		$error = l('captcha_incorrect');
	}
	else
	{
		if (COMMENTS_NEW == 0 && s('moderation') == 1)
		{
			$r['status'] = 0;
			$success = l('comment_moderation');
		}
		else
		{
			$r['status'] = 1;
			$success = l('comment_sent');
		}

		/* send comment notification */

		if (s('notification') == 1)
		{
			/* prepare body parts */

			$emailLink = anchor_element('email', '', '', $email);
			if ($url)
			{
				$urlLink = anchor_element('external', '', '', $url);
			}
			$articleRoute = ROOT . '/' . REWRITE_ROUTE . $route;
			$articleLink = anchor_element('external', '', '', $articleRoute, $articleRoute);

			/* prepare mail inputs */

			$toArray = array(
				s('author') => s('email')
			);
			$fromArray = array(
				$author => $email
			);
			$subject = l('comment_new');
			$bodyArray = array(
				'<strong>' . l('author') . l('colon') . '</strong> ' . $author,
				'<br />',
				'<strong>' . l('email') . l('colon') . '</strong> ' . $emailLink,
				'<br />',
				'<strong>' . l('url') . l('colon') . '</strong> ' . $urlLink,
				'<br />',
				'<strong>' . l('article') . l('colon') . '</strong> ' . $articleLink,
				'<br />',
				'<br />',
				'<strong>' . l('comment') . l('colon') . '</strong> ' . $text
			);

			/* mailer object */

			$mailer = new Redaxscript\Mailer();
			$mailer->init($toArray, $fromArray, $subject, $bodyArray);
			$mailer->send();
		}

		/* create comment */

		Redaxscript\Db::forTablePrefix('comments')
			->create()
			->set($r)
			->save();
	}

	/* handle error */

	if ($error)
	{
		if (s('blocker') == 1)
		{
			$_SESSION[ROOT . '/attack_blocked']++;
		}
		notification(l('error_occurred'), $error, l('back'), $route);
	}

	/* handle success */

	else
	{
		notification(l('operation_completed'), $success, l('continue'), $route);
	}
	$_SESSION[ROOT . '/comment'] = '';
}
