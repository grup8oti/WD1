<?php
/**
 * Whisper
 * 
 * Flat ACP theme for MyBB.
 *
 * @package Whisper
 * @author  Shade <legend_k@live.it>
 * @license http://opensource.org/licenses/mit-license.php MIT license
 * @version 1.0.1
 */

if (!defined('IN_MYBB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

function whisper_info()
{
	return array(
		'name' => 'Whisper',
		'description' => 'Flat ACP theme for MyBB.',
		'author' => 'Shade',
		'version' => '1.0.1',
		'compatibility' => '18*',
	);
}

function whisper_is_installed()
{
	global $cache;
	
	$info = whisper_info();
	$installed = $cache->read("shade_plugins");
	if ($installed[$info['name']]) {
		return true;
	}
}

function whisper_install()
{
	global $db, $cache;
	
	// Update all the admins' settings to use Whisper
	$db->update_query('settings', array('value' => "Whisper"), "name = 'cpstyle'");
	$db->update_query('adminoptions', array('cpstyle' => "Whisper"));
	rebuild_settings();
	
	// Add to cache
	$info = whisper_info();
	$shadePlugins = $cache->read('shade_plugins');
	$shadePlugins[$info['name']] = array(
		'title' => $info['name'],
		'version' => $info['version']
	);
	$cache->update('shade_plugins', $shadePlugins);
	
}

function whisper_uninstall()
{
	global $db, $cache;
		
	// Restore the previous admins' settings and use the default theme
	$db->update_query('settings', array('value' => "default"), "name = 'cpstyle'");
	$db->update_query('adminoptions', array('cpstyle' => "default"));
	rebuild_settings();
	
	// Remove from cache
	$info = whisper_info();
	$shadePlugins = $cache->read('shade_plugins');
	unset($shadePlugins[$info['name']]);
	$cache->update('shade_plugins', $shadePlugins);
}

$plugins->add_hook('admin_page_output_footer', 'whisper_global');
$plugins->add_hook('admin_home_index_output_message', 'whisper_homepage');

// Everywhere
function whisper_global($args)
{
	global $admin_options;
	
	// Check admin's preferences
	if ($admin_options['cpstyle'] != 'Whisper') {
		return false;
	}
	
	echo '<link rel="stylesheet" href="./styles/Whisper/select2/select2.css">
<script type="text/javascript" src="../jscripts/select2/select2.min.js"></script>
<script type="text/javascript">
	$("select").not("#table_select").select2({"width": "copy"});
</script>';

}

// Homepage
function whisper_homepage()
{
	global $cache, $lang, $page, $db, $mybb, $update_check, $threads, $new_reported_posts, $newposts, $reported_posts, $activeusers, $awaitingusers, $newusers, $attachs, $approved_attachs, $serverload, $posted, $posts, $unapproved_attachs, $unapproved_posts, $unapproved_threads, $newthreads, $users, $admin_options;
	
	// Check admin's preferences	
	if ($admin_options['cpstyle'] != 'Whisper') {
		return false;
	}
	
	$adminmessage = $cache->read("adminnotes");
	$table = new Table;

	// Server status
	$up_to_date = '<span class="over up_to_date">Up to date</span>';
	if (isset($update_check['latest_version_code']) && $update_check['latest_version_code'] > $mybb->version_code) {
		$up_to_date = '<span class="over not_up_to_date">Not up to date</span>';
	}
	
	$count = 0;
	$welcome_back = 'You are currently using <span class="label">PHP ' . PHP_VERSION . '</span> and <span class="label">' . $db->short_title . ' ' . $db->get_version() . '</span>.<br /><br />';
	
	if ($newthreads or $newposts) {
		$welcome_back .= ' Today ';
	}
	
	if ($newthreads > 0) {
		$count++;
		$welcome_back .= " <span class='label new'>{$newthreads} new threads</span>";
	}
	
	if ($newposts > 0) {
		if ($count) {
			$welcome_back .= ' and';
		}
		$count++;
		$welcome_back .= " <span class='label new'>{$newposts} new posts</span>";
	}
	
	if ($count) {
		$welcome_back .= ' have been posted.';
	}
	
	$count = 0;
	
	if ($unapproved_threads or $unapproved_posts) {
		$welcome_back .= ' There are';
	}
	
	if ($unapproved_threads > 0) {
		$count++;
		$welcome_back .= " <a href=\"index.php?module=forum-moderation_queue&amp;type=threads\"><span class='label new'>{$unapproved_threads} unapproved threads</span></a>";
	}
	
	if ($unapproved_posts > 0) {
		if ($count) {
			$welcome_back .= ' and';
		}
		$count++;
		$welcome_back .= " <a href=\"index.php?module=forum-moderation_queue&amp;type=posts\"><span class='label new'>{$unapproved_posts} unapproved posts</span></a>";
	}
	
	if ($count) {
		$welcome_back .= ' you might want to review.';
	}
	
	if ($new_reported_posts > 0) {
		$welcome_back .= " You still have <span class='label new'>{$new_reported_posts} reported posts</span> to read.";
	}
	
	if (!$unapproved_posts and !$unapproved_threads and !$new_reported_posts) {
		$welcome_back .= " There are not any threads or posts that needs to be moderated at the moment.";
	}
	
	$second_chart = <<<EOF
	<div class="other_analytics">
		<h2>Hi, <strong>{$mybb->user['username']}</strong></h2>
		{$welcome_back}
		
	</div>
	<div class="other_analytics">
		<div class="central">
			{$up_to_date}
			$mybb->version
			<span class="under">MyBB version</span>
		</div>
		<div class="left top">
			$threads
			<span>Threads</span>
		</div>
		<div class="left bottom">
			$users
			<span>Users</span>
		</div>
		<div class="right bottom">
			$posts
			<span>Posts</span>
		</div>
		<div class="right top">
			$serverload
			<span>Server load</span>
		</div>
	</div>
EOF;

	$table->construct_cell($second_chart, array(
		'style' => 'text-align: center'	
	));
	$table->construct_row();
	
	
	
	$start = TIME_NOW-(60*60*24*30);
	$end = TIME_NOW;
	
	// Fancy statistics
	$query = $db->simple_select("stats", "dateline,numposts,numthreads,numusers", "dateline >= '".$start."' AND dateline <= '".$end."'", array('order_by' => 'dateline', 'order_dir' => 'asc'));
	while ($stat = $db->fetch_array($query)) {
	
		$stats['postcount'][] = $stat['numposts'];
		$stats['threadcount'][] = $stat['numthreads'];
		$stats['usercount'][] = $stat['numusers'];
		$x_labels[] = date("j M", $stat['dateline']);
		
	}
	
	$x_labels = '["' . implode('", "', (array) $x_labels) . '"]';
	$_posts = '["' . implode('", "', (array) $stats['postcount']) . '"]';
	$_threads = '["' . implode('", "', (array) $stats['threadcount']) . '"]';
	$_users = '["' . implode('", "', (array) $stats['usercount']) . '"]';
	
	// Set a reasonable deadline under which we display the "Not enough data" message
	if (end($stats['postcount']) > 10) {
	
		$first_chart = <<<EOF
		<canvas id="mybb_chart" width="700" height="400"></canvas>
		<script type="text/javascript" src="jscripts/Chart.min.js"></script>
		<script type="text/javascript">
		
			$(document).ready(function() {
			
				canvas_container = $('.canvas_container');
				canvas_width = canvas_container.width();
				
				canvas_container.find('canvas').attr('width', canvas_width);
		
				var ctx = $("#mybb_chart").get(0).getContext("2d");
				var data = {
				    labels: $x_labels,
				    datasets: [
				        {
				            label: "Users",
				            fillColor: "rgba(255,255,255,.06)",
				            strokeColor: "#fff",
				            pointColor: "#fff",
				            pointStrokeColor: "#fff",
				            pointHighlightFill: "#fff",
				            pointHighlightStroke: "#fff",
				            data: $_users
				        }
				    ]
				},
				options = {
					scaleGridLineColor: "rgba(255,255,255,.08)",
					scaleFontColor: "#fff",
					scaleLineColor: "rgba(255,255,255,.3)",
					bezierCurveTension: 0.15,
					scaleShowVerticalLines: false,
					pointDotRadius: 3
				},
				posts_data = $_posts,
				threads_data = $_threads,
				users_data = $_users,
				x_labels = $x_labels;
				
				var analytics = new Chart(ctx).Line(data, options);
				
				$('.analytics ul li a').on('click', function() {
					
					var val = $(this).text(),
						pool;
					
					switch (val) {
						
						case 'Posts':
							pool = posts_data;
							break;
						
						case 'Threads':
							pool = threads_data;
							break;
							
						case 'Users':
							pool = users_data;
							break;
						
					}
					
					for (i = 0; i < x_labels.length; i++) {
						analytics.datasets[0].points[i].value = pool[i];
					}
					
					$(this).parent().siblings().removeClass('active');
					$(this).parent().addClass('active');
					
					analytics.update();
					
				});
			
			});
			
		</script>
	
EOF;
		$legend = <<<EOF
		<div class="nav_tabs analytics">
			<ul>
				<li class="active"><a>Users</a></li>
				<li><a>Posts</a></li>
				<li><a>Threads</a></li>
			</ul>
		</div>
EOF;
		$table->construct_cell($legend . $first_chart, array(
			'style' => 'text-align: center',
			'class' => 'canvas_container'
		));
		$table->construct_row();
	
	}
	else {
		$table->construct_cell('<h1>Not enough data to generate accurate analytics</h1>', array(
			'style' => 'text-align: center',
			'class' => 'canvas_container'
		));
		$table->construct_row();
	}
	
	$table->output('Analytics');

	echo '
	<div class="float_right" style="width: 48%; margin-top: -20px;">';

	$table = new Table;
	$table->construct_header($lang->admin_notes_public);

	$form = new Form("index.php", "post");
	$table->construct_cell($form->generate_text_area("adminnotes", $adminmessage['adminmessage'], array('style' => 'width: 99%; height: 200px;')));
	$table->construct_row();

	$table->output($lang->admin_notes);

	$buttons[] = $form->generate_submit_button($lang->save_notes);
	$form->output_submit_wrapper($buttons);
	$form->end();

	echo '</div><div class="float_left" style="width: 48%;">';

	// Latest news widget
	$table = new Table;
	$table->construct_header($lang->news_description);

	if(!empty($update_check['news']) && is_array($update_check['news']))
	{
		foreach($update_check['news'] as $news_item)
		{
			$posted = my_date('relative', $news_item['dateline']);
			$table->construct_cell("<strong><a href=\"{$news_item['link']}\" target=\"_blank\">{$news_item['title']}</a></strong><br /><span class=\"smalltext\">{$posted}</span>");
			$table->construct_row();

			$table->construct_cell($news_item['description']);
			$table->construct_row();
		}
	}
	else
	{
		$table->construct_cell($lang->no_announcements);
		$table->construct_row();
	}

	$table->output($lang->latest_mybb_announcements);
	echo '</div>';

	$page->output_footer();

}