=== Plugin Name ===
Contributors: mariovalney, leobaiano
Donate link: http://mariovalney.com/
Tags: users, developer, debug
Requires at least: 3.1.0
Tested up to: 4.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Switch to another user account quickly.

== Description ==

It's a plugin for developers.
The intent is create a simple interface where the logged user can switch to another user account.

This make tests and debug infos more fast (especially when you are working with different roles). 

For security:

* Only logged users can switch user accounts in website with WP_DEBUG false.
* Passwords are not revealed (of course).
* Uses the cookie authentication function 'wp_set_auth_cookie' to switch accounts.
* Implements the nonce security system (of course).

Obs: For now, the interface appears only in website, not in dashboard.

== Installation ==

Through the panel:

1. Go to the Plugins menu and click Add New.
1. Search for Switch User.
1. Click 'Install Now' next to the Switch User plugin.
1. Activate the plugin.

Uploading by yourself:

1. Upload the entire switch-user folder to the /wp-content/plugins/ directory or the .zip file in Plugins menu.
1. Activate the plugin.

== Frequently Asked Questions ==

= How switch users? =

The interface to switch users will appear fixed on right side of your website.

= I can't find the interface! =

Make sure your theme uses get_footer() function... or read next question.

= Can I use it in production (real) website? =

NO! For secury reasons, of course...
But it will works only if you set WP_DEBUG to true or you're already logged.

== Changelog ==

= 2.1 =
* Fix ajax request

= 2.0 =
* Code refactored (POO)
* Improved front-end
* I18n by @leobaiano

= 1.0.1 =
* File architecture improved by @leobaiano

= 1.0 =
* First version: you can switch through all users registered.