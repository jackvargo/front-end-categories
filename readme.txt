=== Front-end Categories ===
Contributors: voltronik
Tags: categories, sub-categories, frontend
Requires at least: 3.4
Tested up to: 3.9.2
Stable tag: 0.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A tiny and super simple plugin for creating categories and sub-categories on the WordPress front-end using Ajax.

== Description ==

Front-end Categories is a tiny and super simple plugin for creating categories and sub-categories on the WordPress front-end using Ajax.

The plugin comes with two shortcodes for use: 

1. [front-end-cat] will add a form consisting of a text input and submit button to create a category.
2. [front-end-subcat] will add another form consisting of a text input, dropdown of existing categories to choose from and a submit button.

The forms can be used independently of each other (neither one needs the other to work) and all output messages are wrapped in span tags with appropriate classes for easy styling. 

Any problems / queries / feature requests / etc. please leave a message on the support tab.

== Installation ==

1. Install.
2. Activate.
3. Use [front-end-cat] and/or [front-end-subcat] anywhere in a post or page.
4. Style span.fec-error and/or span.fec-success if you want to.
5. You're done! 

== Changelog ==

= 0.2.2 =
* Fixed compatibility with WordPress 3.9.2 (categories are now created and status messages are shown again).
* New categories/sub-categories are submitted via Ajax so the page doesn’t have to reload.

= 0.2.1 =
* Fixed an issue with the shortcode not respecting it’s set position on a page (props to gribbler in the Forums).

= 0.2 =
* Added ability to add sub-categories.

= 0.1 =
* Initial version. Capability to add categories.