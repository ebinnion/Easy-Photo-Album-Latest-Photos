=== Plugin Name ===
Contributors: ebinnion
Donate link: http://manofhustle.com/
Tags:
Requires at least: 3.0.1
Tested up to: 3.9.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easy Photo Album Latest Photos allows you to quickly and easily generate a gallery for use anywhere in your theme with the latest
photos that you have uploaded to Easy Photo galleries.

== Description ==

Easy Photo Album Latest Photos makes it easy to generate a gallery of the latest photos you've added to Easy Photo Album galleries
and place it anywhere on your site.

This came out of a project where I needed to put the latest photos that were uploaded on the home page. While I could have used the latest uploads
in the media directory, I wanted to use the latest photos used in Easy Photo Album as that showed the client already wanted
to show those pictures off.

The plugin has two methods that will allow you to generate a latest photo gallery.

`Latest_Easy_Photo_Album::get_latest_epa_ids();` will return an array of the latest photos uploaded to Easy Photo Album

`Latest_Easy_Photo_Album::output_latest_epa_photos();` will output a basic gallery using the same settings that Easy Photo Album does.

This plugin is more oriented towards developers who'd like to cut a few hours of work out of their day. But, if you're a hacker type and don't know
where to get started, find me on [twitter at @ebinnion](http://twitter.com/ebinnion) and I will gladly give you a few pointers.

For any questions or concerns about using these methods.

== Installation ==
Installation is standard, via FTP or via the plugin downloader in WordPress admin.

You will need to add the method calls to get output though, for which you will need to modify your theme.


== Screenshots ==


== Changelog ==

= 1.0 =
* First working version of the plugin
