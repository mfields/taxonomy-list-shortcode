===Plugin Name===
Taxonomy List Shortcode
Contributors: mfields
Donate link: http://mfields.org/donate/
Tags: taxonomy, tag, category, index, list
Requires at least: 2.8.6
Tested up to: 3.0-RC3
Stable tag: trunk

The Taxonomy List Shortcode plugin adds a shortcode to your WordPress installation which enables you to display multiple unordered lists containing every term of a given taxonomy.

==Description==
The Taxonomy List Shortcode plugin adds a [shortcode](http://codex.wordpress.org/Shortcode_API) to your [WordPress](http://wordpress.org/) installation which enables you to display multiple unordered lists containing every term of a given [taxonomy](http://codex.wordpress.org/WordPress_Taxonomy ).


__Usage__
Adding `[taxonomy-list]` to your post content will render a set of three unordered lists containing all terms of a given taxonomy. Custom css will be printed in a style tag to the head of every page view. If this is unacceptable to you, you may turn it off by using the checkbox labeled "Enable CSS" under the "Taxonomy" section of "Settings" in the Administration Panels.

__Supported Parameters__

1. __tax__ To define the taxonomy that you would like to list terms from, you will need to specifiy the name of the taxonomy in the `tax` parameter: `[taxonomy-list tax="category"]`. In an "out-of-the-box" installation of WordPress, the following taxonomies will be recognized: `post_tag`, `category`, and `link_category`. If you have defined custom taxonomies through use of a plugin or your own hacker-skillz, you can use the name of this taonomy as well: `[taxonomy-list tax="fishy-squiggles"]`. If the taxonomy cannot be located, due to a spelling error or missing code, the `[taxonomy-list]` shortcode will return an empty string.

1. __cols__ To define the number of columns that the `[taxonomy-list]` shortcode generates, you will want to use the `cols` parameter. This parameter will accept an integer from 1 - 5. If this parameter is left empty, or a value outside of it's range is defined, it will default to three columns. Example: `[taxonomy-list tax="category" cols="4"]`.

1. __color__ Use this to define the color of the text. The color passed should be in hexidecimal notation (ff0000) or short hand (f00) Please do not include the hash character (#).

1. __background__ Use this to define the color of the background. The color passed should be in hexidecimal notation (ff0000) or short hand (f00) Please do not include the hash character (#).

1. __show_counts__ (bool) If you would like to disable the counts from displaying after your term name, set this parameter to `0`. Default value is `1`.

1. __NEW! per_page__ (int) Using this parameter enables paging for your term list. It accepts a numerical value from 1 to "your hearts desire". It is suggested that this feature only be activated on pages that display a list of terms from a single taxonomy. No need to worry, if you have 2 or more lists of terms on a page and you add the per_page parameter to both shortcodes, your site will not break. Please refrain from using this parameter on shortcodes within posts.

__Examples__

1. __Post Tags (default)__ `[taxonomy-list]` - Display a three column, list of Post Tags in alphetbetical order. Although this is the default usage, it is synonymous with `[taxonomy-list tax="post_tag" cols="3"]`.

1. __Post Categories__ `[taxonomy-list tax='category']` - Display a three column, unordered list of Post Categories.

1. __Link Categories__ `[taxonomy-list tax='link_category']` - Display a three column, unordered list of Link Categories. Special note: it is rather pointless to use link categories do to the fact that WordPress does not support front-end display of individual links.

1. __Custom Taxonomy__ `[taxonomy-list tax="fishy-squiggles"]` - Display a three column, unordered list of the [custom taxonomy](http://justintadlock.com/archives/2009/05/06/custom-taxonomies-in-wordpress-28) "fishy-squiggles".

1. __Two Columns__ `[taxonomy-list cols="2"]` - Display a two, horizontally-aligned unordered lists of Post Tags.

1. __Five Columns (maximum)__ `[taxonomy-list cols="5"]` - Display a 5, horizontally-aligned unordered lists of Post Tags.

1. __Custom Taxonomy with 5 Columns__ `[taxonomy-list tax="fishy-squiggles" cols="5"]` - Display 5, horizontally-aligned unordered lists of the custom taxonomy "fishy-squiggles".

__Notes on post status__

* Terms containing published, password protected posts will be shown in the list.
* Terms containing only private, scheduled, draft or posts pending review will not be displayed in the list.

__The XHTML + CSS was Tested in the Following User Agents__

* Windows XP: Internet Explorer 6
* Windows XP: FireFox 3.5.3
* Windows XP: FireFox 3.6.3
* Windows XP: Opera 9.26
* Windows XP: Safari 4.0.3

__This Plugin has been tested with the Following WordPress Themes__

* Twenty Ten
* WordPress Classic
* [Kubrick](http://binarybonsai.com/wordpress/kubrick/)
* [Hybrid](http://themehybrid.com/)
* [Thematic](http://themeshaper.com/)


== Screenshots ==
1. This is the output of the shortcode when set to display the terms of custom taxonomy "Topics".
2. Same view as above, but when you are logged in - edit links will display next to each term.

== Upgrade Notice ==

= 0.9 =
Temporary fix for taxonomy permissions. Need to take a closer look at this later.

= 0.9 =
Added edit links to each term displayed in a list. Please see Changelog for all changes.

= 0.8 =
Paging has been added for the term lists. Full support for WordPress version 3.0 has been added.

= 0.7 =
You are now able to disable term counts using the "show_counts" argument.


==Changelog==

= 0.9 =
* Replaced is_taxonmy() with taxonomy_exists() for WordPress 3.0.
* Defined taxonomy_exists() for 2.9 branch.
* Added edit links to the terms lists.
* show_all argument has been added to shortcode. It is set to false by default.
* Tested in 3.0-RC3 using the TwentyTen theme.
* Shortcode's 'args' argument has been removed.
* Pad counts is now always set to true for get_terms().

= 0.8.1 =
* Fixed bug in form control for css toggle.

= 0.8 =
* Added Support for paging via the __per_page__ parameter.
* Added cutom subpage under "Settings" called "Taxonomy" for 3.0 support. They killed "Miscelaneous" where our setting used to live.
* Changed the name of the CSS setting in the database. Sorry, it won't happen again :)

= 0.7 =
* Allowed for disabling of term counts via the show_counts argument. Props to nicolas for the suggestion.

= 0.6 =
* Added parameters for background and color.
* CSS bug fixes.
* Tested in 3.0 Beta.

= 0.5 =
* Main plugin file's name has been changed from "mf-taxonomy-list-shortcode.php" to "taxonomy-list-shortcode.php".
* Style tag has been optimized to only use one line when rendered.
* No longer using an object.
* Removed the include_css() function.
* Added option for CSS. This can be Set under Miscellaneous -> "Enable CSS for Taxonomy List Shortcode Plugin".
* Added PhpDoc style comments to code.
* Added "args" attribute to the shortcode.

= 0.4 =
* pad_counts is now set to true by default for get_terms();

= 0.3 =
* PHP Bugfix with empty array being passed to array_chunk().

= 0.2 =
* Added the `sanitize_cols()` method.

= 0.1 =
* Original Release - Works With: wp 2.8.6 + wp 2.9 beta 2.

==Installation==
1. [Download](http://wordpress.org/extend/plugins/taxonomy-list-shortcode/)
1. Unzip the package and upload to your /wp-content/plugins/ directory.
1. Log into WordPress and navigate to the "Plugins" panel.
1. Activate the plugin.