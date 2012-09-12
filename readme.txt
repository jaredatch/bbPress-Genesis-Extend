=== bbPress Genesis Extend ===
Contributors: jaredatch
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AD8KTWTTDX9JL
Tags: bbpress, genesis
Requires at least: 3.3
Tested up to: 3.4.x
Stable tag: 0.8.2
 
Provides basic compatibility with bbPress and the Genesis Framework with a few extra goodies.

== Description ==

**This plugin is for bbPress 2.1+ and Genesis 1.8+**.

bbPress and the Genesis Framework are both fantastic additions to any WordPress site. However, depending on your setup they don't always play nice right away.

bbPress Genesis Extend does some behind-the-scenes tweaks and fixes to make bbPress/Genesis integration as quick and painless as possible. 

Additionally, this plugin also:

* Adds option for a forum specific sidebar
* Adds option to control the layout of your forum, separate from Genesis
* Adds Genesis Layout Controls for Forums
* If a forum has a specific layout set, all topics in that forum will use that layout.
* Adds Genesis SEO Controls for Forums

The forum sidebar and layout options are located on the Genesis Settings page, look for 'bbPress'.


== Installation ==

1. Upload `bbpress-genesis-extend` to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure optional settings on the Genesis Settings page

== Frequently Asked Questions ==

= Random Genesis elements are showing up on the forums =

This can happen if you are using a custom Genesis child theme that does not use default element positions. This plugin removes elements such as breadcrumbs, post into/meta, and post navigation from their *default* location. If you child theme has moved them then you will have to make changes accordingly.

For example: the Genesis breadcrumbs are removed using `remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );` - however if they are not hooked into genesis_before_loop then they will not be removed as they should be.

= I'm still having problems, bbPress doesn't look right! =

Genesis and bbPress are both their own beasts. This plugin provides some basic "out of the box" fixes and features that greatly improves integration. However additional tweaks may be needed to your Genesis and/or bbPress themes.

Specifically, font sizes often need to be adjusted so bbPress "blends" with your Genesis child theme. You can fix this by changing/adding styles to your child theme's `style.css`.

If you need to tweak the CSS, I recommend using a plugin similar to http://wordpress.org/extend/plugins/bbpress-custom-css-file/

== Screenshots ==

1. Options on the Genesis Settings page. 

== Changelog ==

= 0.8.2 =
* Fixed compatibility issue with the Genesis Simple Sidebar plugin
* Cleaned up documentation

= 0.8.1 =
* A few CSS tweaks
* Added filter `bbpge_css` so the CSS can be disabled if needed

= 0.8 =
* Initial launch, heavily based off of Genesis compatibility class in bbPress 2.0.x
* Added optional forum sidebar setting
* Added optional forum layout setting
* Other goodies
* Props to @jjj for various code cleanup and @deckerweb for testing/translations