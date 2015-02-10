=== Smart Passworded Pages ===
Contributors: BrianLayman 
Donate link: http://thecodecave.com/donate
Tags: password,security,page,member,login,cms
Requires at least: 2.5
Tested up to: 3.8.1
Stable tag: 1.1.5

Allows a central login page for password protected child pages. Enter a password and you are taken to the newest child page with a matching password.

== Description ==

The Smart Passworded Pages enhances WordPress by allowing the creation of central login pages that grant access to a any number of passworded child pages. In this fashion you can give each client/member/organization a central place to enter a password and they will be taken to the page that has their information.  
  
The password field is displayed as a field followed by a button with customizable text. The form is can be uniquely stylized with CSS. The child pages can in turn link to other pages protected with the same password and the password will not need to be re-entered.  
  
To add the password field to a page, simply enter the short code  
[smartpwpages]  
  
If you wish to assign a unique label to the submit button or give the form a unique ID for CSS identification, the attributes in the following example can be used:  
[smartpwpages label=\"Login\" ID=\"sppForm1\"]

This plugin doesn't add the ability to add passwords to pages.  WordPress has that built in.  On the right hand side of the page editing screen in WordPress, you can change the visibility to Password protected and enter in a password. If you are unfamiliar with using passwords in WordPress, you might want to read this page first:  http://codex.wordpress.org/Using_Password_Protection

This plugin does make the password handling smarter and enhances it so that you can enter one password on a parent page and gain access to all the children pages using that password.  If you don't know what children pages or sub-pages are, you might want to read about it here:  http://codex.wordpress.org/Pages#Creating_Pages

You can find out more about the Smart Passworded Pages plugin here: http://thecodecave.com/smart-passworded-pages-plugin/

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.
== Screenshots ==
1. A login form can be added to any page. That login form will take you to the newest child page that has a matching password.

This is a screen shot of the first demo page for the plugin:

<a href=\"http://thecodecave.com/smart-passworded-pages-demonstration-1/\" title=\"The first demo page for the plugin\">http://thecodecave.com/smart-passworded-pages-demonstration-1/</a>

There is also the second demo page for the plugin:

<a href=\"http://thecodecave.com/smart-passworded-pages-demonstration-1/\" title=\"The second demo page for the plugin\">http://thecodecave.com/smart-passworded-pages-demonstration-2/</a> 

2. The plugin is activated through the simple use of a shortcode in a post.

This video describes the usage of the plugin:
 `[youtube http://www.youtube.com/watch?v=DTliSyBPBWI]`
 
3. On the right hand side of the page editing screen in WordPress, you can change the visibility to Password protected and enter in a password.

== Changelog ==
= 1.0 = 
* Initial Release

= 1.0.1 =
* Added compatiblity with the exclude pages plugin

= 1.1.0 =
* Updated the plugin to include the security enhancements added in WordPress 3.4

= 1.1.1 =
* The version labeled 1.1.0 hadn't included my final fixes and did not work actually work with 3.4. 1.1.1 simply includes the code that was intended to be 1.1.0.
* Updated the styling of the changelog section of the readme file to allow it to parse correctly on WordPress.org

= 1.1.2 =
* Tweeked the Exclude Pages plugin compatibilty

= 1.1.3 =
* Added 3.6 compatiblity
* Improved internationalization
* Added support for plugins that replace the default hashing protocol

= 1.1.4 =
* Updated header to 3.8.1 compatibility
* Improved documentation
* NOTE: Released on the same day 1.1.3 was publicly checked in. This made 3.6+ compatibility universally available.

= 1.1.5 =
* Fixed an error on the Invalid Password response