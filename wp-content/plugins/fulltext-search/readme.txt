=== WP FullText Search ===
Contributors: Epsiloncool
Donate link: https://fulltextsearch.org/
Tags: wordpress, search, text-search, indexed search, fulltext search, search algorithm, search in PDF, search in MS Word, search in Excel, search customization
Requires at least: 3.0.1
Tested up to: 4.5.3
Stable tag: 1.2.3
License: GPL3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin creates a search index to speedup of search and to implement custom search on metadata. No external software/service required.

== Description ==

This plugin creates a special search index for all existing posts (and posts' metadata too) and modify standard Wordpress
search behaviour (using hooks) to use search index for sufficiently increase search speed, increase relevance quality
and allow to implement complex search algorithms on post metadata.

It provides an ability to configure indexing mechanism by defining special filter function. It is useful to include
dynamic-generated texts to seach index or, for example, read text content from MS Excel/Word of Adobe PDF files
to implement search in PDF files.

= Documentation =

Please refer [Documentation](https://fulltextsearch.org/documentation/ "WP FullText Search Documentation").

== Installation ==

1. Unpack and upload `fulltext-search` folder with all files to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Run `Autoindex` function to initialize index (index will be updated automatically live when created)

== Frequently Asked Questions ==

= Where can I put some notices, comments or bugreports? =

Do not hesistate to write to us at [Contact Us](https://fulltextsearch.org/contact/ "Contact Us") page.

== Screenshots ==

1. Plugin configuration screen
2. Indexing Engine configuration screen
3. Search tester screen
4. Simple wpfts_post_index filter example

== Changelog ==

= 1.2.3 =
* Changed regexp which is splitting texts to words (non-english characters are now supported)
* Added `wpftp_split_to_words` filter which enables you to define your own "text splitting" algorithm

= 1.2.1 =
* Added complex query analyzer (support quotes)

= 1.1.7 =
* Added plugin icon
* Fixed description

= 1.1.6 =
* Lowered save_post hook priority to index metadata correctly

= 1.1.5 =
* Small bug fixes
* Debug logging removed

= 1.1.4 =
* Added cluster weights capability
* Plugin assigned to GPL license

= 1.0 =
* First Wordpress version

= 0.4 =
* Automatic indexing were added, over 30 bugs were fixed

= 0.1 =
* Initial edition. Basic functions added

== Upgrade Notice ==

= 1.1.4 =
* Upgrade immediately, because of some security issues found and fixed

= 1.0 =
* First version to be in Wordpress repository, just install it
