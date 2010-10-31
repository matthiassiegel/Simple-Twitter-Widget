=== Simple Twitter Widget ===
Contributors: chipsandtv
Donate link: http://chipsandtv.com/
Tags: twitter, widget
Requires at least: 2.8
Tested up to: 3.0
Stable tag: 1.02

A simple but powerful widget to display updates from a Twitter feed. Configurable, reliable and with advanced caching.

== Description ==

This widget lets you display your latest tweets on your blog. Various configuration options are available.

Features:

* No need to enter your Twitter password
* No Javascript
* Simple but reliable caching, so your website won't slow down
* Various options such as clickable URLs and usernames, date format etc.
* Supports multiple instances at the same time
* Fully compatible with widget-ready themes and Wordpress 3.0


== Installation ==

**Option 1 - Automatic install**

Use the plugin installer built into Wordpress to search. Wordpress will then download and install it for you.

**Option 2 - Manual install**

1. Make sure the files are within a folder.
2. Copy the whole folder into the wp-content/plugins/ folder.
3. Log into Wordpress and activate the plugin. You can now select the widget under Appearance / Widgets.


== Frequently Asked Questions ==

= What could be the reason if the widget doesn't work? =

The code has been extensively tested and most issues can be resolved by trying the following:

1. make sure your server / Wordpress can connect to Twitter
2. check if your Wordpress upload directory is writable. This is where the widget tries to store the cache file
3. try other public Twitter feeds
4. temporarily set the caching interval to a low value like 10 and see if it makes a difference.

Also keep in mind it will only work with public Twitter feeds, not private ones.

== Screenshots ==

1. Widget options

== Changelog ==

= 1.02 =
* Bugfix for multiple feeds

= 1.01 =
* URL detection should be more reliable now
* Fix for potential problem with feed update interval
* Tested with Wordpress 3.0

= 1.0 =
* Initial release.