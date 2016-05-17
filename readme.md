=== Talk Wiki To Me ===
Contributors: burtlo, bass-blogger
Donate link: http://www.recursivegames.com/wordpress
Tags: wiki, link, links, linking
Requires at least: 2.0.2
Tested up to: 2.8
Stable tag: 1.0.0

Create wiki-style links to multiple destinations to help you link faster and protect your links from failing when other sites change their paths.

== Description ==

**Talk Wiki To Me** is based on the [Better-{{Wiki}}-Links](http://wordpress.org/extend/plugins/better-wiki-links/ "Better-{{Wiki}}-Links") system. However, **Talk Wiki To Me** allows you to define more that just one custom wiki tag. This allows you the ability to define your own tags to multiple different search engines, websites, directories, or reference sites. Defining your own wiki-like tags allows you to quickly compose links that use a common url structure, allowing you to spend more of your efforts composing posts and less time creating or managing the url links. It also protects your external links from becoming broken if an external site changes their url site structure, or paths.

After installation, you can find the plugin settings under Talk Wiki To Me in the Settings section.  First, select your style of brackets you want to use for your linking, by default double brackets are used.

* [[ ]], (( )), {{ }}

After setting that bracket, there are some default wiki style tags already created for examples.  You can use these right away or you can create your own.

With your new link tag created or using the existing one, you can get started by composing an entry and simply using the format:

* [[TAG|TERM|TEXT]]

For example, if I want to use the wiki tag (created on installation of the plugin), I compose an entry and insert the following text:

* [[wiki|NASA|My Dream Job]]

When the entry is rendered you will have a link to the NASA page on wikipedia.  If you would view the source code, you would roughly see that it was translated to: &lt;a href="http://en.wikipedia.org/wiki/NASA"&gt;My dream job&lt;/a&gt;

Notes:

A huge thanks to the Better-Wiki-Links plugin which initially allowed me to hard-code a version of this code for a few directories and search engines I found myself linking a lot to the msdn, wiki, and google. I AJAXed the control panel, but I`ll admit that it is fragile; multiple requests can be fired prior to a successful answer. Sadly I changed the plugin enough that I had to drop the internationalization support.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `talkwiki.php`,`adminmenu.php`,and `plugin.php` to `/wp-content/plugins/talkwikitome` directory.  If talkwikitome does not already exist create it.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. From the Admin Menu, select 'Settings' > 'Talk Wiki To Me'
1. Configure the plugin as you please


== Frequently Asked Questions ==

= Why would I want to use this plugin over using traditional links? =

I found that when I was composing entries that relied heavily upon external links that I spent too much time interrupting my flow creating those links and I thought that I could speed up the process by creating a tag system to allow the quick creation of these links.  This was specifically true when I was working with referencing a programming library or making extensive use of wikipedia.

I also worried that if I started to make entensive links to a particular website that if they moved the site completely or if they even changed their linking structure slightly, I would be left with a lot of work as I had to move through my entries to find the links that were now broken.  Using this link, creates an abstraction/proxy which allows you to protect you from losing precious links to simple changes.

= If I change the bracket format from [[]] to (()), will all my links continue to work? =

Currently if you make that change, all the previous links will be rendered inert.  I made the bracket setting global to the entire site, but I could also make it unique to the link tag that you create.  If there is a demand I can make that change in a future update.

= How do I save my settings when I make changes? / My settings do not appear to stay after I make them? =

The admin page uses AJAX to make changes after any value changes.  For the radio buttons (i.e. same browser / new browser ), the option is saved as you select the new option.  For the text fields, the value is saved when you move out away from that control (onblur).  So to ensure that a setting is saved: radio buttons, select the radio button option and it should be saved; text inputs and text areas, make the change and tab away or select another field.

Also, the page is written in what I would call 'fragile' AJAX.  When you click on a new input on the form, a new javascript event is fired even if one is already taking place.  This is essentially me writing poor code.  While testing, I found that it took about 300ms for each request to finish, so if you click on options faster than that you run the risk of having an option request not succeed.  I will address this in a later update.

= What can I use for a TAG? =

In the TAG of [[TAG|TERM|TEXT]] you can use any character that is not a pipe character.  Currently spaces are not trimmed from the front or the back of the TAG so [[ TAG|TERM|TEXT]] will not be equivalent to [[TAG|TERM|TEXT]].

= What can I use for the TERM? =

Again, anything but the pipe character and be mindful of the spaces.  The TERM is appended to the end of the URL that is associated with the TAG.  So if the TAG wiki, translates to http://en.wikipedia.org/Wiki/ then the TERM will immediately follow the URL (i.e. http://www.en.wikipedia.org/Wiki/TERM).

You cannot, at the moment, specify for the TERM to be composed in center or other parts of the link.  It is something that I would like to add in the future.

= What can I use for the TEXT?  / Do I have to use the TEXT? =

The TEXT field can be any character besides an open bracket style that is currently set (],),}) for the links.  The field is mandatory, in the future, I can remove the requirement if people would like for the [[TAG|TERM]] to translate to the link with the link url itself specified as the link.

= Does the URL have to include the http:// ?  / Can I use other things like ftp:// ? =

You must specify the full url of the link to include the http://.  You can also instead specify ftp:// as well.  Anything that is acceptable to be placed inside an anchor's href attribute.

= What is search engine behavior follow / nofollow? =

You can read more about it at [wikipedia](http://en.wikipedia.org/wiki/Nofollow "wikipedia"), but the jist is that search engines will follow links and attempt to index them if you do not specify nofollow.  This is not necessarily true for all web crawlers and does not have to be respected.

== Screenshots ==

1. Selecting a bracket style, choose between [[]], (()), {{}}.  These changes are global and used for all link-tags.
1. Shows the administrative options panel that you can use to manage your link-tags that you create.
1. When you compose an entry you use the format [[TAG|TERM|TEXT]]
1. It will automatically translate to the TAG into the URL, attaches the TERM to the end, and then uses the text you specified in your entry.

== Changelog ==

= 1.0 =
* Released.

== Thanks ==

A huge thanks to the Better-Wiki-Links plugin which initially allowed me to hard-code a version of this code for a few directories and search engines I found myself linking a lot to the msdn, wiki, and google.
