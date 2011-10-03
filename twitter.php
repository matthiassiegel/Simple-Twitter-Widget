<?php
/*
Plugin Name: Simple Twitter Widget
Plugin URI: http://chipsandtv.com/
Description: A simple but powerful widget to display updates from a Twitter feed. Configurable and reliable.
Version: 1.03
Author: Matthias Siegel
Author URI: http://chipsandtv.com/


Copyright 2011  Matthias Siegel  (email : chipsandtv@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



if (!class_exists('Twitter_Widget')) :

	class Twitter_Widget extends WP_Widget {


		function Twitter_Widget() {
									
			// Widget settings
			$widget_ops = array('classname' => 'twitter-widget', 'description' => 'Display your latest tweets.');

			// Create the widget
			$this->WP_Widget('twitter-widget', 'Twitter', $widget_ops);
		}
		
		
		function widget($args, $instance) {
			
			extract($args);
			
			global $interval;
			
			// User-selected settings
			$title = apply_filters('widget_title', $instance['title']);
			$username = $instance['username'];
			$posts = $instance['posts'];
			$interval = $instance['interval'];
			$date = $instance['date'];
			$datedisplay = $instance['datedisplay'];
			$datebreak = $instance['datebreak'];
			$clickable = $instance['clickable'];
			$hideerrors = $instance['hideerrors'];
			$encodespecial = $instance['encodespecial'];

			// Before widget (defined by themes)
			echo $before_widget;

			// Set internal Wordpress feed cache interval, by default it's 12 hours or so
			add_filter('wp_feed_cache_transient_lifetime', array(&$this, 'setInterval'));
			include_once(ABSPATH . WPINC . '/feed.php');

			// Get current upload directory
			$upload = wp_upload_dir();
			$cachefile = $upload['basedir'] . '/_twitter_' . $username . '.txt';

			// Title of widget (before and after defined by themes)
			if (!empty($title)) echo $before_title . $title . $after_title;

			// If cachefile doesn't exist or was updated more than $interval ago, create or update it, otherwise load from file
			if (!file_exists($cachefile) || (file_exists($cachefile) && (filemtime($cachefile) + $interval) < time())) :

				$feed = fetch_feed('http://api.twitter.com/1/statuses/user_timeline.rss?screen_name=' . $username);
				
				// This check prevents fatal errors — which can't be turned off in PHP — when feed updates fail
				if (method_exists($feed, 'get_items')) :

					$tweets = $feed->get_items(0, $posts);

					$result = '
						<ul>';

					foreach	($tweets as $t) :
						$result .= '
							<li>';

						// Get message
						$text = $t->get_description();
						
						// Get date/time and convert to Unix timestamp
						$time = strtotime($t->get_date());

						// If status update is newer than 1 day, print time as "... ago" instead of date stamp
						if ((abs(time() - $time)) < 86400) :
							$time = human_time_diff($time) . ' ago';
						else :
							$time = date(($date), $time);
						endif;
						
						// HTML encode special characters like ampersands
						if ($encodespecial) :
							$text = htmlspecialchars($text);
						endif;

						// Make links and Twitter names clickable
						if ($clickable) :
							// Match URLs
				    	$text = preg_replace('`\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))`', '<a href="$0">$0</a>', $text);

				    	// Match @name
				    	$text = preg_replace('/(@)([a-zA-Z0-9\_]+)/', '@<a href="http://twitter.com/$2">$2</a>', $text);
						endif;

			    	// Display date/time
						if ($datedisplay) $result .= '
								<span class="twitter-date"><a href="'. $t->get_permalink() .'">' . $time . '</a></span>' . ($datebreak ? '<br />' : '');

			    	// Display message without username prefix
						$prefixlen = strlen($username . ": ");
						$result .= '
								<span class="twitter-text">' . substr($text, $prefixlen, strlen($text) - $prefixlen) . '</span>';

						$result .= '
							</li>';
					endforeach;
					
					$result .= '
						</ul>
						';
					
					// Save updated feed to cache file
					@file_put_contents($cachefile, $result);

					// Display everything
					echo $result;


				// If loading from Twitter fails, try loading from the file instead
				else :
					if (file_exists($cachefile)) :
						$result = @file_get_contents($cachefile);
					endif;

					if (!empty($result)) :
						echo $result;

					// If loading from the file failed too, display error
					elseif (!$hideerrors) :
						echo '<p>Error while loading Twitter feed.</p>';
					endif;
				endif;


			// If cache file exists or if it was updated not long ago, load from file straight away
			else :
				$result = @file_get_contents($cachefile);

				if (!empty($result)) :
					echo $result;
				elseif (!$hideerrors) :
					echo '<p>Error while loading Twitter feed.</p>';			
				endif;
			endif;


			// After widget (defined by themes)
			echo $after_widget;
		}
		
		
		// Callback helper for the cache interval filter
		function setInterval() {
			
			global $interval;
			
			return $interval;
		}

		
		function update($new_instance, $old_instance) {
			
			$instance = $old_instance;

			$instance['title'] = $new_instance['title'];
			$instance['username'] = $new_instance['username'];
			$instance['posts'] = $new_instance['posts'];
			$instance['interval'] = $new_instance['interval'];
			$instance['date'] = $new_instance['date'];
			$instance['datedisplay'] = $new_instance['datedisplay'];
			$instance['datebreak'] = $new_instance['datebreak'];
			$instance['clickable'] = $new_instance['clickable'];
			$instance['hideerrors'] = $new_instance['hideerrors'];
			$instance['encodespecial'] = $new_instance['encodespecial'];
			
			// Delete the cache file when options were updated so the content gets refreshed on next page load
			$upload = wp_upload_dir();
			$cachefile = $upload['basedir'] . '/_twitter_' . $old_instance['username'] . '.txt';
			@unlink($cachefile);

			return $instance;
		}
		
		
		function form($instance) {

			// Set up some default widget settings
			$defaults = array('title' => 'Latest Tweets', 'username' => '', 'posts' => 5, 'interval' => 1800, 'date' => 'j F Y', 'datedisplay' => true, 'datebreak' => true, 'clickable' => true, 'hideerrors' => true, 'encodespecial' => false);
			$instance = wp_parse_args((array) $instance, $defaults);
?>
				
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
				<input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>">
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('username'); ?>">Your Twitter username:</label>
				<input class="widefat" type="text" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" value="<?php echo $instance['username']; ?>">
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('posts'); ?>">Display how many posts?</label>
				<input class="widefat" type="text" id="<?php echo $this->get_field_id('posts'); ?>" name="<?php echo $this->get_field_name('posts'); ?>" value="<?php echo $instance['posts']; ?>">
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('interval'); ?>">Update interval (in seconds):</label>
				<input class="widefat" type="text" id="<?php echo $this->get_field_id('interval'); ?>" name="<?php echo $this->get_field_name('interval'); ?>" value="<?php echo $instance['interval']; ?>">
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('date'); ?>">Date format (see PHP <a href="http://php.net/manual/en/function.date.php">date</a>):</label>
				<input class="widefat" type="text" id="<?php echo $this->get_field_id('date'); ?>" name="<?php echo $this->get_field_name('date'); ?>" value="<?php echo $instance['date']; ?>">
			</p>
								
			<p>
				<input class="checkbox" type="checkbox" <?php if ($instance['datedisplay']) echo 'checked="checked" '; ?>id="<?php echo $this->get_field_id('datedisplay'); ?>" name="<?php echo $this->get_field_name('datedisplay'); ?>">
				<label for="<?php echo $this->get_field_id('datedisplay'); ?>">Display date?</label>
				
				<br>
				
				<input class="checkbox" type="checkbox" <?php if ($instance['datebreak']) echo 'checked="checked" '; ?>id="<?php echo $this->get_field_id('datebreak'); ?>" name="<?php echo $this->get_field_name('datebreak'); ?>">
				<label for="<?php echo $this->get_field_id('datebreak'); ?>">Add linebreak after date?</label>
				
				<br>

				<input class="checkbox" type="checkbox" <?php if ($instance['clickable']) echo 'checked="checked" '; ?>id="<?php echo $this->get_field_id('clickable'); ?>" name="<?php echo $this->get_field_name('clickable'); ?>">
				<label for="<?php echo $this->get_field_id('clickable'); ?>">Make URLs &amp; usernames clickable?</label>
				
				<br>

				<input class="checkbox" type="checkbox" <?php if ($instance['hideerrors']) echo 'checked="checked" '; ?>id="<?php echo $this->get_field_id('hideerrors'); ?>" name="<?php echo $this->get_field_name('hideerrors'); ?>">
				<label for="<?php echo $this->get_field_id('hideerrors'); ?>">Hide error message if update fails?</label>

				<br>

				<input class="checkbox" type="checkbox" <?php if ($instance['encodespecial']) echo 'checked="checked" '; ?>id="<?php echo $this->get_field_id('encodespecial'); ?>" name="<?php echo $this->get_field_name('encodespecial'); ?>">
				<label for="<?php echo $this->get_field_id('encodespecial'); ?>">HTML-encode special characters?</label>
			</p>
			
<?php
		}
	}	
endif;





// Register the plugin/widget
if (class_exists('Twitter_Widget')) :

	function loadTwitterWidget() {
		
		register_widget('Twitter_Widget');
	}

	add_action('widgets_init', 'loadTwitterWidget');

endif;
?>