=== WooCommerce Simple Product Export ===
Contributors: iaps
Donate link: http://www.iaps.ca/wordpress-plugins/
Tags: debug
Requires at least: 3.0
Tested up to: 5.1.1
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Provides PHP variable debugging.

== Description ==

Upon install, your IP address, along with 127.0.0.1, is saved as the default debug::enabled() IPs.

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Why don't the file name and line number show up sometimes?  =

This plugin uses the PHP function debug_backtrace(), which may not be available on your system.  You may pass in the file and line as arguments #3 and #4 respectively.

== Screenshots ==

1. Example of: global $post; debug::print_r($post);
2. Add each IP or Hostname on its' own line.

== Changelog ==

= 1.1.0 =

* Fixed nonce issue.
* Fixed link under Admin > Settings.

= 1.0.0 =

* Initial plugin development and testing.

== Upgrade Notice ==

= 1.0.0 =

* init