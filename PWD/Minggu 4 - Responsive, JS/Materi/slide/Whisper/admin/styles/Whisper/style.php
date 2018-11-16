<?php

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

class Page extends DefaultPage
{
	
	function output_header($title="")
	{
		global $mybb, $admin_session, $lang, $plugins;

		$args = array(
			'this' => &$this,
			'title' => &$title,
		);

		$plugins->run_hooks("admin_page_output_header", $args);

		if (!$title) {
			$title = $lang->mybb_admin_panel;
		}

		$rtl = "";
		if ($lang->settings['rtl'] == 1) {
			$rtl = " dir=\"rtl\"";
		}
		
		$menu = $this->_build_menu();

		echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
		echo "<html xmlns=\"http://www.w3.org/1999/xhtml\"{$rtl}>\n";
		echo "<head profile=\"http://gmpg.org/xfn/1\">\n";
		echo "	<title>".$title."</title>\n";
		echo "	<meta name=\"author\" content=\"MyBB Group\" />\n";
		echo "	<meta name=\"copyright\" content=\"Copyright ".COPY_YEAR." MyBB Group.\" />\n";
		echo "	<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/".$this->style."/background.css\" />\n";
		echo "	<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/".$this->style."/main.min.css\" />\n";
		echo "	<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/".$this->style."/modal.css\" />\n";
		echo "	<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/".$this->style."/animate.css\" />\n";

		// Load stylesheet for this module if it has one
		if (file_exists(MYBB_ADMIN_DIR."styles/{$this->style}/{$this->active_module}.css")) {
			echo "	<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/{$this->style}/{$this->active_module}.css\" />\n";
		}

		echo "	<script type=\"text/javascript\" src=\"../jscripts/jquery.js\"></script>\n";
		echo "	<script type=\"text/javascript\" src=\"../jscripts/jquery.plugins.min.js\"></script>\n";
		echo "	<script type=\"text/javascript\" src=\"../jscripts/general.js\"></script>\n";
		echo "	<script type=\"text/javascript\" src=\"./jscripts/admincp.js\"></script>\n";
		echo "	<script type=\"text/javascript\" src=\"./jscripts/tabs.js\"></script>\n";
		echo "	<script type=\"text/javascript\" src=\"./jscripts/whisper.popup.js\"></script>\n";

		echo "	<link rel=\"stylesheet\" type=\"text/css\" href=\"jscripts/jqueryui/css/redmond/jquery-ui.min.css\" />\n";
		echo "	<link rel=\"stylesheet\" type=\"text/css\" href=\"jscripts/jqueryui/css/redmond/jquery-ui.structure.min.css\" />\n";
		echo "	<link rel=\"stylesheet\" type=\"text/css\" href=\"jscripts/jqueryui/css/redmond/jquery-ui.theme.min.css\" />\n";
		echo "	<script src=\"jscripts/jqueryui/js/jquery-ui.min.js\"></script>\n";

		// Stop JS elements showing while page is loading (JS supported browsers only)
		echo "  <style type=\"text/css\">.popup_button { display: none; } </style>\n";
		echo "  <script type=\"text/javascript\">\n".
				"//<![CDATA[\n".
				"	document.write('<style type=\"text/css\">.popup_button { display: inline; } .popup_menu { display: none; }<\/style>');\n".
                "//]]>\n".
                "</script>\n";

		echo "	<script type=\"text/javascript\">
//<![CDATA[
var loading_text = '{$lang->loading_text}';
var cookieDomain = '{$mybb->settings['cookiedomain']}';
var cookiePath = '{$mybb->settings['cookiepath']}';
var cookiePrefix = '{$mybb->settings['cookieprefix']}';
var imagepath = '../images';

lang.unknown_error = \"{$lang->unknown_error}\";
lang.saved = \"{$lang->saved}\";
//]]>
</script>\n";
		echo $this->extra_header;
		echo "</head>\n";
		echo "<body>\n";
		echo "<div id=\"wrapper\" style=\"display: none\"></div>";
		echo "<div id=\"sidebar\">";
		
		if (strpos($mybb->user['avatar'], 'http') === false) {
			$avatar = ".".$mybb->user['avatar'];
		}
		else {
			$avatar = $mybb->user['avatar'];
		}
		
		echo "	<a href=\"{$mybb->settings['bburl']}/member.php?action=profile&uid={$mybb->user['uid']}\" class=\"avatar\"><img src=\"{$avatar}\" /></a>";
		echo "	<div class=\"info\"><div>{$mybb->user['username']}</div>
<a href=\"{$mybb->settings['bburl']}\" target=\"_blank\" class=\"forum button\">{$lang->view_board}</a><a href=\"index.php?action=logout&amp;my_post_key={$mybb->post_code}\" class=\"logout button\">{$lang->logout}</a></div>";
		echo $this->submenu;
		echo $this->sidebar;
		echo "</div>\n";
		echo "<div id=\"container\">\n";
		echo "<div id=\"header\">";		
		echo $menu;
		echo "</div>\n";
		echo "	<div id=\"page\">\n";
		echo "		<div id=\"content\">\n";
		echo "			<div id=\"navigation\">\n";
		echo $this->_generate_breadcrumb();
		echo "			</div>\n";
		echo "           <div id=\"inner\">\n";
		if (isset($admin_session['data']['flash_message']) and $admin_session['data']['flash_message']) {
			$message = $admin_session['data']['flash_message']['message'];
			$type = $admin_session['data']['flash_message']['type'];
			
			// Ajaxify support
			if (defined('AJAXIFIED')) {
				echo "<div class='hidden _success'>{$message}</div>";
			}
			else {
				echo "<div id=\"flash_message\" class=\"{$type}\">\n";
				echo "<div class=\"content\">{$message}</div>\n";
				echo "</div>\n";
			}
			
			update_admin_session('flash_message', '');
		}

		if (!empty($this->extra_messages) and is_array($this->extra_messages))
		{
			foreach($this->extra_messages as $message)
			{
				
				if (defined('AJAXIFIED')) {
					echo "<div class='hidden _{$message['type']}'>{$message['message']}</div>";
				}
				else {
					switch($message['type'])
					{
						case 'success':
						case 'error':
							echo "<div id=\"flash_message\" class=\"{$message['type']}\">\n";
							echo "{$message['message']}\n";
							echo "</div>\n";
							break;
						default:
							$this->output_error($message['message']);
							break;
					}
				}
			}
		}

		if ($this->show_post_verify_error == true)
		{
			$this->output_error($lang->invalid_post_verify_key);
		}
	}
	
	function output_footer($quit=true)
	{
		global $mybb, $maintimer, $db, $lang, $plugins;

		$args = array(
			'this' => &$this,
			'quit' => &$quit,
		);

		$plugins->run_hooks("admin_page_output_footer", $args);

		$memory_usage = get_friendly_size(get_memory_usage());

		$totaltime = format_time_duration($maintimer->stop());
		$querycount = $db->query_count;

		if(my_strpos(getenv("REQUEST_URI"), "?"))
		{
			$debuglink = htmlspecialchars_uni(getenv("REQUEST_URI")) . "&amp;debug=1#footer";
		}
		else
		{
			$debuglink = htmlspecialchars_uni(getenv("REQUEST_URI")) . "?debug=1#footer";
		}

		echo "			</div>\n";
		echo "		</div>\n";
		echo "	</div>\n";
		echo "<div id=\"footer\"><p class=\"generation\">".$lang->sprintf($lang->generated_in, $totaltime, $debuglink, $querycount, $memory_usage)."</p><p class=\"powered\">Powered By <a href=\"http://www.mybb.com/\" target=\"_blank\">MyBB</a>, &copy; 2002-".COPY_YEAR." <a href=\"http://www.mybb.com/\" target=\"_blank\">MyBB Group</a>.</p></div>\n";
		if($mybb->debug_mode)
		{
			echo $db->explain;
		}
		echo "</div>\n";
		echo "</body>\n";
		echo "</html>\n";

		if($quit != false)
		{
			exit;
		}
	}
	
	function output_error($error)
	{
		if (defined('AJAXIFIED')) {
			echo "<div class='hidden _error'>{$message}</div>";
		}
		else {
			echo "<div class=\"error\">\n";
			echo "<div class=\"content\">{$error}</div>\n";
			echo "</div>\n";
		}
	}
	
	function output_inline_error($errors)
	{
		global $lang;

		if(!is_array($errors))
		{
			$errors = array($errors);
		}
		if (defined('AJAXIFIED')) {
			foreach($errors as $error)
			{
				echo "<div class='hidden _error'>{$error}</div>";
			}
		}
		else {
			echo "<div class=\"error\">\n";
			echo "<p><em>{$lang->encountered_errors}</em></p>\n";
			echo "<ul>\n";
			foreach($errors as $error)
			{
				echo "<li>{$error}</li>\n";
			}
			echo "</ul>\n";
			echo "</div>\n";
		}
	}
	
	function output_success($message)
	{
		echo "<div class=\"success\">\n";
		echo "<div class=\"content\">{$message}</div>\n";
		echo "</div>\n";
	}
	
	function output_alert($message, $id="")
	{
		if ($id)
		{
			$id = " id=\"{$id}\"";
		}
		echo "<div class=\"alert\"{$id}><div class=\"content\">{$message}</div></div>\n";
	}
	
	function _generate_breadcrumb()
	{
		if (!is_array($this->_breadcrumb_trail)) {
			return false;
		}
		$trail = "";
		foreach ($this->_breadcrumb_trail as $key => $crumb) {
			if ($this->_breadcrumb_trail[$key + 1]) {
				$trail .= "<li><a href=\"" . $crumb['url'] . "\"><span>" . $crumb['name'] . "</span></a></li> / ";
			} else {
				$trail .= "<li class=\"active\"><a href=\"" . $crumb['url'] . "\"><span>" . $crumb['name'] . "</span></a></li>";
			}
		}
		return $trail;
	}
	
	function output_nav_tabs($tabs=array(), $active='')
	{
		global $plugins;
		$tabs = $plugins->run_hooks("admin_page_output_nav_tabs_start", $tabs);
		echo "<div class=\"nav_tabs\">";
		echo "\t<ul>\n";
		foreach($tabs as $id => $tab)
		{
			$class = $classes = '';
			if ($id == $active)
			{
				$classes[] = 'active';
			}
			if (isset($tab['align']) == "right")
			{
				$classes[] = "right";
			}
			if ($classes) {
				$class = ' class="' . implode(',', $classes) . '"';
			}
			$target = '';
			if (isset($tab['link_target']))
			{
				$target = " target=\"{$tab['link_target']}\"";
			}
			if (!isset($tab['link']))
			{
				$tab['link'] = '';
			}
			echo "<li{$class}><a href=\"{$tab['link']}\"{$target}>{$tab['title']}</a></li>";
			$target = '';
		}
		echo "\t</ul>\n";
		if ($tabs[$active]['description'])
		{
			echo "\t<div class=\"tab_description\">{$tabs[$active]['description']}</div>\n";
		}
		echo "</div>";
		$arguments = array('tabs' => $tabs, 'active' => $active);
		$plugins->run_hooks("admin_page_output_nav_tabs_end", $arguments);
	}
	
	// 2FA page
	function show_2fa()
	{
		global $lang, $cp_style, $mybb;

		$mybb2fa_page = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head profile="http://gmpg.org/xfn/1">
<title>{$lang->my2fa}</title>
<meta name="author" content="MyBB Group" />
<meta name="copyright" content="Copyright {$copy_year} MyBB Group." />
<link rel="stylesheet" href="./styles/{$cp_style}/login.css" type="text/css" />
<link rel="stylesheet" href="./styles/{$cp_style}/background.css" type="text/css" />
<script type="text/javascript" src="../jscripts/jquery.js"></script>
<script type="text/javascript" src="../jscripts/general.js?ver=1806"></script>
<script type="text/javascript" src="./jscripts/admincp.js"></script>
<script type="text/javascript">
//<![CDATA[
	loading_text = '{$lang->loading_text}';
//]]>
</script>
</head>
<body>
<div id="wrapper"{$login_container_width}>
	<div class="header">
		<div id="logo">
			<h1><strong>2-factor auth</strong> verification</h1>
		</div>
	</div>
	<div id="content">
		<h2>{$lang->my2fa}</h2>
EOF;
		// Make query string nice and pretty so that user can go to his/her preferred destination
		$query_string = '';
		if($_SERVER['QUERY_STRING'])
		{
			$query_string = '?'.preg_replace('#adminsid=(.{32})#i', '', $_SERVER['QUERY_STRING']);
			$query_string = preg_replace('#my_post_key=(.{32})#i', '', $query_string);
			$query_string = str_replace('action=logout', '', $query_string);
			$query_string = preg_replace('#&+#', '&', $query_string);
			$query_string = str_replace('?&', '?', $query_string);
			$query_string = htmlspecialchars_uni($query_string);
		}
		$mybb2fa_page .= <<<EOF
		<p>{$lang->my2fa_code}</p>
		<form method="post" action="index.php{$query_string}">
		<div class="form_container">
			<div class="label"><label for="code">{$lang->my2fa_label}</label></div>
			<div class="field"><input type="text" name="code" id="code" class="text_input initial_focus" placeholder="2-factor authorization code" /></div>
		</div>
		<p class="submit">
			<input type="submit" value="{$lang->login}" />
			<input type="hidden" name="do" value="do_2fa" />
		</p>
		</form>
	</div>
</div>
</body>
</html>
EOF;
		echo $mybb2fa_page;
		exit;
	}
	
	// Login page
	function show_login($message="", $class="success")
	{
		global $plugins, $lang, $cp_style, $mybb, $db;

		$args = array(
			'this' => &$this,
			'message' => &$message,
			'class' => &$class
		);

		$plugins->run_hooks('admin_page_show_login_start', $args);

		$copy_year = COPY_YEAR;

		$login_container_width = "";
		$login_label_width = "";

		// If the language string for "Username" is too cramped then use this to define how much larger you want the gap to be (in px)
		if(isset($lang->login_field_width))
        {
        	$login_label_width = " style=\"width: ".((int)$lang->login_field_width+100)."px;\"";
			$login_container_width = " style=\"width: ".(410+((int)$lang->login_field_width))."px;\"";
        }

		$login_page .= <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head profile="http://gmpg.org/xfn/1">
<title>{$lang->mybb_admin_login}</title>
<meta name="author" content="MyBB Group" />
<meta name="copyright" content="Copyright {$copy_year} MyBB Group." />
<link rel="stylesheet" type="text/css" href="./styles/{$cp_style}/background.css" />
<link rel="stylesheet" type="text/css" href="./styles/{$cp_style}/login.css" />
<script type="text/javascript" src="../jscripts/jquery.js"></script>
<script type="text/javascript" src="../jscripts/jquery.plugins.min.js"></script>
<script type="text/javascript" src="../jscripts/general.js"></script>
<script type="text/javascript" src="./jscripts/admincp.js"></script>
{$this->extra_scripts}
<script type="text/javascript">
//<![CDATA[
	loading_text = '{$lang->loading_text}';
//]]>
</script>
</head>
<body>
<div id="sidebar" style="display: none"></div>
<div id="container" style="display: none">
	<div id="header"></div>
	<div id="page"></div>
	<div id="footer"></div>
</div>
<div id="wrapper"{$login_container_width}>
	<div class="header">
		<div id="logo">
			<h1><strong>Administration</strong> Panel</h1>
		</div>
	</div>
	<div id="content">
		<h2>{$lang->please_login}</h2>
EOF;
		if ($message) {
			if (defined('AJAXIFIED')) {
				$login_page .= "<div class='hidden _{$class}'>{$message}</div>";
			}
			else {
				$login_page .= "<div id=\"message\" class=\"{$class}\"><div>{$message}</div></div>";
			}
		}
		// Make query string nice and pretty so that user can go to his/her preferred destination
		$query_string = '';
		if($_SERVER['QUERY_STRING'])
		{
			$query_string = '?'.preg_replace('#adminsid=(.{32})#i', '', $_SERVER['QUERY_STRING']);
			$query_string = preg_replace('#my_post_key=(.{32})#i', '', $query_string);
			$query_string = str_replace('action=logout', '', $query_string);
			$query_string = preg_replace('#&+#', '&', $query_string);
			$query_string = str_replace('?&', '?', $query_string);
			$query_string = htmlspecialchars_uni($query_string);
		}
		switch($mybb->settings['username_method'])
		{
			case 0:
				$lang_username = $lang->username;
				break;
			case 1:
				$lang_username = $lang->username1;
				break;
			case 2:
				$lang_username = $lang->username2;
				break;
			default:
				$lang_username = $lang->username;
				break;
		}

		// Secret PIN
		global $config;
		if(isset($config['secret_pin']) && $config['secret_pin'] != '')
		{
			$secret_pin = "<div class=\"label\"{$login_label_width}><label for=\"pin\">{$lang->secret_pin}</label></div>
            <div class=\"field\"><input type=\"password\" name=\"pin\" id=\"pin\" class=\"text_input\" placeholder=\"Secret PIN\" /></div>";
		}
		else
		{
			$secret_pin = '';
		}

		$login_lang_string = $lang->enter_username_and_password;

		switch($mybb->settings['username_method'])
		{
			case 0: // Username only
				$login_lang_string = $lang->sprintf($login_lang_string, $lang->login_username);
				break;
			case 1: // Email only
				$login_lang_string = $lang->sprintf($login_lang_string, $lang->login_email);
				break;
			case 2: // Username and email
			default:
				$login_lang_string = $lang->sprintf($login_lang_string, $lang->login_username_and_password);
				break;
		}

       	$_SERVER['PHP_SELF'] = htmlspecialchars_uni($_SERVER['PHP_SELF']);

		$login_page .= <<<EOF
		<p>{$lang->enter_username_and_password}</p>
		<form method="post" action="{$_SERVER['PHP_SELF']}{$query_string}">
		<div class="form_container">

			<div class="label"{$login_label_width}><label for="username">{$lang_username}</label></div>

			<div class="field"><input type="text" name="username" id="username" class="text_input initial_focus" placeholder="Username" /></div>

			<div class="label"{$login_label_width}><label for="password">{$lang->password}</label></div>
			<span class="forgot_password">
				<a href="../member.php?action=lostpw">{$lang->lost_password}</a>
			</span>
			<div class="field"><input type="password" name="password" id="password" class="text_input" placeholder="Password" /></div>
            {$secret_pin}
		</div>
		<div class="submit">
			<div><input type="submit" value="{$lang->login}" /></div>
			<input type="hidden" name="do" value="login" />
		</div>
		</form>
	</div>	
	{$admins}
    <div class="footer"><p>Whisper</p><p>Shade &copy; 2015</div>
</div>
</body>
</html>
EOF;

		$args = array(
			'this' => &$this,
			'login_page' => &$login_page
		);

		$plugins->run_hooks('admin_page_show_login_end', $args);

		echo $login_page;
		exit;
	}
	
	function _build_menu()
	{	
		if(!is_array($this->_menu))
		{
			return false;
		}
		$build_menu = "<div id=\"menu\"><ul>\n";
		ksort($this->_menu);
		foreach($this->_menu as $items)
		{
			foreach($items as $menu_item)
			{
				$menu_item['link'] = htmlspecialchars_uni($menu_item['link']);
				if($menu_item['id'] == $this->active_module)
				{
					$sub_menu = $menu_item['submenu'];
					$sub_menu_title = $menu_item['title'];
					$build_menu .= "<li class=\"active\"><a href=\"{$menu_item['link']}\">{$menu_item['title']}</a></li>\n";

				}
				else
				{
					$build_menu .= "<li><a href=\"{$menu_item['link']}\">{$menu_item['title']}</a></li>\n";
				}
			}
		}
		$build_menu .= "</ul>\n</div>";

		if($sub_menu)
		{
			$this->_build_submenu($sub_menu_title, $sub_menu);
		}
		return $build_menu;
	}
	
	function output_tab_control($tabs = array(), $observe_onload = true, $id = "tabs")
	{
		global $plugins;
		$tabs = $plugins->run_hooks("admin_page_output_tab_control_start", $tabs);
		echo "<div id=\"tabs-wrapper\"><ul class=\"tabs\" id=\"{$id}\">\n";
		$tab_count = count($tabs);
		$done = 1;
		foreach($tabs as $anchor => $title)
		{
			$class = "";
			if($tab_count == $done)
			{
				$class .= " last";
			}
			if($done == 1)
			{
				$class .= " first";
			}
			++$done;
			echo "<li class=\"{$class}\"><a href=\"#tab_{$anchor}\">{$title}</a></li>\n";
		}
		echo "</ul></div>\n";
		$plugins->run_hooks("admin_page_output_tab_control_end", $tabs);
	}
	
}

class SidebarItem extends DefaultSidebarItem
{
	
	function __construct($title = "")
	{
		$this->_title = $title;
	}
	
	function add_menu_items($items, $active)
	{
		global $run_module;
		
		$this->_contents = "<ul>";
		foreach ($items as $item) {
			if (!check_admin_permissions(array(
				"module" => $run_module,
				"action" => $item['id']
			), false)) {
				continue;
			}
			
			$class = "";
			if ($item['id'] == $active) {
				$class = " class=\"active\"";
			}
			$item['link'] = htmlspecialchars($item['link']);
			$this->_contents .= "<li{$class}><a href=\"{$item['link']}\">{$item['title']}</a></li>\n";
		}
		$this->_contents .= "</ul>";
	}
	
	function set_contents($html)
	{
		$this->_contents = $html;
	}
	
	function get_markup()
	{
		$markup = "<div class=\"menu\">\n";
		$markup .= "<div class=\"title\">{$this->_title}</div>\n";
		if ($this->_contents) {
			$markup .= $this->_contents;
		}
		$markup .= "</div>";
		return $markup;
	}
}

class PopupMenu extends DefaultPopupMenu
{
	/**
	 * @var string The title of the popup menu to be shown on the button.
	 */
	private $_title;
	
	/**
	 * @var string The ID of this popup menu. Must be unique.
	 */
	private $_id;
	
	/**
	 * @var string Built HTML for the items in the popup menu.
	 */
	private $_items;
	
	/**
	 * Initialise a new popup menu.
	 *
	 * @var string The ID of the popup menu.
	 * @var string The title of the popup menu.
	 */
	function __construct($id, $title = '')
	{
		$this->_id = $id;
		$this->_title = $title;
	}
	
	/**
	 * Add an item to the popup menu.
	 *
	 * @param string The title of this item.
	 * @param string The page this item should link to.
	 * @param string The onclick event handler if we have one.
	 */
	function add_item($text, $link, $onclick = '')
	{
		if ($onclick) {
			$onclick = " onclick=\"{$onclick}\"";
		}
		$this->_items .= "<div class=\"popup_item_container\"><a href=\"{$link}\"{$onclick} class=\"popup_item\">{$text}</a></div>";
	}
	
	/**
	 * Fetch the contents of the popup menu.
	 *
	 * @return string The popup menu.
	 */
	function fetch()
	{
		$popup = "<div class=\"pjxPopup-wrap\"><div class=\"popup_menu\" id=\"{$this->_id}_popup\">\n{$this->_items}</div>\n";
		if ($this->_title) {
			$popup .= "<a href=\"javascript:;\" id=\"{$this->_id}\" class=\"popup_button\">{$this->_title}</a>\n";
		}
		$popup .= "<script type=\"text/javascript\">\n";
		$popup .= "$(\"#{$this->_id}\").popupMenu();\n";
		$popup .= "</script></div>\n";
		return $popup;
	}
	
	/**
	 * Outputs a popup menu to the browser.
	 */
	function output()
	{
		echo $this->fetch();
	}
}

class Table extends DefaultTable
{
}

class Form extends DefaultForm
{

	function generate_yes_no_radio($name, $value=1, $int=true, $yes_options=array(), $no_options = array())
	{
		global $lang;

		// Checked status
		if($value == "no" || $value === '0')
		{
			$no_checked = 1;
			$yes_checked = 0;
		}
		else
		{
			$yes_checked = 1;
			$no_checked = 0;
		}
		// Element value
		if($int == true)
		{
			$yes_value = 1;
			$no_value = 0;
		}
		else
		{
			$yes_value = "yes";
			$no_value = "no";
		}

		if(!isset($yes_options['class']))
		{
			$yes_options['class'] = '';
		}

		if(!isset($no_options['class']))
		{
			$no_options['class'] = '';
		}

		// Set the options straight
		$yes_options['class'] = "radio_yes ".$yes_options['class'];
		$yes_options['checked'] = $yes_checked;
		$no_options['class'] = "radio_no ".$no_options['class'];
		$no_options['checked'] = $no_checked;

		$yes = $this->generate_radio_button($name, $yes_value, $lang->yes, $yes_options);
		$no = $this->generate_radio_button($name, $no_value, $lang->no, $no_options);
		return "<div class='button_container'>".$yes." ".$no."</div>";
	}
	
	function generate_on_off_radio($name, $value=1, $int=true, $on_options=array(), $off_options = array())
	{
		global $lang;

		// Checked status
		if($value == "off" || (int) $value !== 1)
		{
			$off_checked = 1;
			$on_checked = 0;
		}
		else
		{
			$on_checked = 1;
			$off_checked = 0;
		}
		// Element value
		if($int == true)
		{
			$on_value = 1;
			$off_value = 0;
		}
		else
		{
			$on_value = "on";
			$off_value = "off";
		}

		// Set the options straight
		if(!isset($on_options['class']))
		{
			$on_options['class'] = '';
		}

		if(!isset($off_options['class']))
		{
			$off_options['class'] = '';
		}

		$on_options['class'] = "radio_on ".$on_options['class'];
		$on_options['checked'] = $on_checked;
		$off_options['class'] = "radio_off ".$off_options['class'];
		$off_options['checked'] = $off_checked;

		$on = $this->generate_radio_button($name, $on_value, $lang->on, $on_options);
		$off = $this->generate_radio_button($name, $off_value, $lang->off, $off_options);
		return "<div class='button_container'>".$on." ".$off."</div>";
	}
	
	function generate_radio_button($name, $value = "", $label = "", $options = array())
	{
		global $lang;
		$input = "<input type=\"radio\" name=\"{$name}\" value=\"" . htmlspecialchars($value) . "\"";
		if (isset($options['class'])) {
			$input .= " class=\"radio_input " . $options['class'] . "\"";
		} else {
			$input .= " class=\"radio_input\"";
		}
		if (isset($options['id'])) {
			$input .= " id=\"" . $options['id'] . "\"";
		} else {
			$input .= " id=\"" . htmlspecialchars($value) . "_{$name}\"";
		}
		if (isset($options['checked']) and $options['checked'] != 0) {
			$input .= " checked=\"checked\"";
		}
		$input .= " />";
		
		$input .= "<label";
		if (isset($options['id'])) {
			$input .= " for=\"{$options['id']}\"";
		} else {
			$input .= " for=\"" . htmlspecialchars($value) . "_{$name}\"";
		}
		$input .= ">";
		if ($label != "") {
			if ($label == $lang->no || $label == $lang->yes || $label == $lang->on || $label == $lang->off) {
				$input .= "";
			} else {
				$input .= $label;
			}
		}
		$input .= "</label>";
		return $input;
	}
	
	function generate_submit_button($value, $options = array())
	{
		global $lang;
		if ($value == $lang->yes || $value == $lang->no) {
			$value = "";
		}
		$input = "<label ";
		if (isset($options['class'])) {
			$input .= " class=\"" . $options['class'] . "\"";
		}
		$input .= ">";
		$input .= "<input type=\"submit\" value=\"" . htmlspecialchars($value) . "\"";
		
		if (isset($options['class'])) {
			$input .= " class=\"submit_button " . $options['class'] . "\"";
		} else {
			$input .= " class=\"submit_button\"";
		}
		if (isset($options['id'])) {
			$input .= " id=\"" . $options['id'] . "\"";
		}
		if (isset($options['name'])) {
			$input .= " name=\"" . $options['name'] . "\"";
		}
		if ($options['disabled']) {
			$input .= " disabled=\"disabled\"";
		}
		if ($options['onclick']) {
			$input .= " onclick=\"" . str_replace('"', '\"', $options['onclick']) . "\"";
		}
		$input .= " /></label>";
		return $input;
	}
}

class FormContainer extends DefaultFormContainer
{
	private $_container;
	public $_title;
	
	function __construct($title = '', $extra_class = '')
	{
		$this->_container = new Table;
		$this->extra_class = $extra_class;
		$this->_title = $title;
	}
	
	function output_row_header($title, $extra = array())
	{
		$this->_container->construct_header($title, $extra);
	}
	
	function output_row($title, $description = "", $content = "", $label_for = "", $options = array(), $row_options = array())
	{
		global $plugins;
		$pluginargs = array(
			'title' => &$title,
			'description' => &$description,
			'content' => &$content,
			'label_for' => &$label_for,
			'options' => &$options,
			'row_options' => &$row_options,
			'this' => &$this
		);
		$plugins->run_hooks("admin_formcontainer_output_row", $pluginargs);
		if ($label_for != '') {
			$for = " for=\"{$label_for}\"";
		}
		
		if ($title) {
			$row = "<label{$for}>{$title}</label>";
		}
		
		if ($options['id']) {
			$options['id'] = " id=\"{$options['id']}\"";
		}
		$row .= "<div class=\"row\">";
		$row .= "<div class=\"form_row\"{$options['id']}>{$content}</div>\n";
		
		if ($description != '') {
			$row .= "\n<div class=\"description\">{$description}</div>\n";
		}
		
		$row .= "</div>";
		
		$this->_container->construct_cell($row, $options);
		
		if (!isset($options['skip_construct'])) {
			$this->_container->construct_row($row_options);
		}
	}
	
	function output_cell($data, $options = array())
	{
		$this->_container->construct_cell($data, $options);
	}
	
	function construct_row($extra = array())
	{
		$this->_container->construct_row($extra);
	}
	
	function output_row_cells($row_id, $return = false)
	{
		if (!$return) {
			echo $this->_container->output_row_cells($row_id, $return);
		} else {
			return $this->_container->output_row_cells($row_id, $return);
		}
	}
	
	function num_rows()
	{
		return $this->_container->num_rows();
	}
	
	function end($return = false)
	{
		global $plugins;
		
		$hook = array(
			'return' => &$return,
			'this' => &$this
		);
		
		$plugins->run_hooks("admin_formcontainer_end", $hook);
		if ($return == true) {
			return $this->_container->output($this->_title, 1, "general form_container {$this->extra_class}", true);
		} else {
			echo $this->_container->output($this->_title, 1, "general form_container {$this->extra_class}", false);
		}
	}
}
?>