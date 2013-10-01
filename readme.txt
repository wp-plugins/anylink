=== anyLink ===
Contributors: SivaDu
Donate link: http://dudo.org/
Tags: seo, link sanitize, covert external links to internal links
Requires at least: 3.4
Tested up to: 3.6.1
Stable tag: 0.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

AnyLink is a Wordpress plugin which allow you to customise you external link like an internal one.

== Description ==

Anylink allows you to covert the external links in your Wordpress to internal links. Of course, it's automatically. It's advantage
is that Anylink Plugin doesn't destroy your data in Wordpress, which means once you removed it, you needn't do anything to your 
posts.

Also, you can customise the style of the link, such as its length, component, etc. You can customise the redirect type(http status) such as
301, 307 as well.

Mainly feature:

*   covert external links to internal links, e.g. http://wordpress.org -> http://yourdomain/goto/a1b2
*   customise the redirect category, e.g. you can change "goto" in the link above to any word you like
*   allow you change the components of the slug, by default it's 4 letters and numbers. e.g. a1b2
*   you can customise the redirect http status code, such as 301, 307

== Frequently Asked Questions == 
= What to do after installation? =
Once Anylink is installed in your wordpress, you need running scan for the first time. Anylink will scan all your posts and grab all the
external links.

== Screenshots ==

== Installation ==

1. Upload `anylink.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Scan all your post for the first time.

== Changelog ==

= 0.1.2 =
*	Fixed some bugs in previous version
*	Customed post type posts supported
*	Javascript to redirect a page is available now

= 0.1.1 =
*	Fixed some bugs in v0.1
*	Both English and Chinese languages are now supported
*	POT file is supplied, so you can tranlate it into your own language as well

= 0.1 =
*	Covert all the external links to internal links by default
*	Customise your link type
*	Customise redirect http status code

== Upgrade Notice ==

=0.1.1=
Fixed some bugs in v0.1
Both English and Chinese languages are now supported
POT file is supplied, so you can tranlate it into your own language as well

=0.1=
Main feature is developed.