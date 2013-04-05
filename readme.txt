=== My Related Posts ===
Contributors: Atomixstar,Gyurka Mircea
Donate link: 
Tags: related post, posts, all posts, my, atomixstar
Requires at least: 3.2
Tested up to: 3.5.1
Stable tag: related post
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

**This plugin** is based around the concept of lists. You can create a list, then assign posts to it. (A post can only be on one list.)

== Description ==

This plugin is based around the concept of lists. You can create a list, then assign posts to it. (A post can only be on one list.) When a list is created, you edit the blog posts and assign them to the list. When a blog post is assigned to a list, they can be listed in your blog posts.

This plugin differs from the other ones out there, because it does no magic! (And that’s a good thing). You have to create a list. Assign various blog posts to that list.

You have complete control of your related posts, making this plugin a great asset when you create a series of blog posts, with different parts or chapters.

== Installation ==

1. Upload `my-related-posts` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'My related posts' menu in WordPress
= It easy to use! =

There are two ways to use this plugin. You can either edit your theme (file: my-theme/single.php) and add the following snippet: `echo myrelposts_getrelated($title);`
(The $title is optional, but let’s you specify a title to to appear before the actual related posts are listed.)

Or you can use a shortcode tag and add it manually to the posts you’d like!

Just add the following shortcode tag: `[myrelposts-related title=”my title”]`
(The title is optional, but let’s you specify a title to to appear before the actual related posts are listed.)
Get related posts and pages, using the list name!
A new feature in version 1.2 is that you can get a list of related posts, by passing the list name in the shortcode tag!
`[myrelposts-related list=”my related post list”]`
Using the list tag, causes the plugin NOT to look at the current post’s meta information, so use this tag wisely.
(as a bonus feature, you can use wildcards, text , in the list title.)
**There’s more!**

== Frequently asked questions ==

= A question that someone might have =

An answer to that question.

== Screenshots ==

1. http://s16.postimg.org/3v9qgp6x1/2_plugin_configuration.png
2. http://s9.postimg.org/jpk4tyezz/1_my_related_posts_plugin_in_action.png

== Changelog ==

Short list of changes in the different versions of the plugin.
Version 1.2
* You can now add pages to the related lists. * You can show a list of pages and posts, based on a list name