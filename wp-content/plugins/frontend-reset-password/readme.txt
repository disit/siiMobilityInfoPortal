=== Frontend Reset Password ===
Contributors: squareonemedia, rwebster85
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VAYF6G99MCMHU
Author URI: https://squareonemedia.co.uk
Requires at Least: 4.4
Tested up to: 4.9.4
Stable tag: trunk
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: password, reset password, lost password, login

Let your users reset their forgotten passwords from the frontend of your website.

== Description ==

**Frontend Reset Password** lets your site users reset their lost or forgotten passwords in the frontend of your site. No more default WordPress reset form! Users fill in their username or email address and a reset password link is emailed to them. When they click this link they'll be redirected to your site and asked for a new password. Everything is handled using default WordPress methods including security, so you don't have to worry.

**Frontend Reset Password** is perfect for sites that have disabled access to the WordPress dashboard, or if you want to include a lost/reset password form on one of your custom site pages. It also works great with **Easy Digital Downloads**!

Any error messages display right on the form, including whether the username or email address is invalid.

The plugin works by hooking into the ``lostpassword_url`` WordPress filter, meaning compatibility with other plugins can be better maintained.

**Frontend Reset Password** is also translation ready.

== Setup Guide ==

= Step 1 =
Include our shortcode ``[reset_password]`` in any page you want

= Step 2 =
Go to the plugin settings page and select which page your shortcode is on.

= Step 3 =
Customise! This is optional, the plugin works right out of the box, but you're able to change the text for the form elements.

== Customisation ==

The text in the lost/reset password forms can be customised. Very little CSS styling is used, so the forms should style with your website theme beautifully.

If you use a frontend login page you can set that in the plugin also. Users are told they can login and are shown the url when they successfully change their password.

You can also set the minimum number of characters required for a password. Default is 0.

== Support ==

Quick start guide included on the plugin settings page. For anything else post on the wordpress.org support forum.

== Installation ==

**Manually in WordPress**

1. Download the plugin ZIP file from WordPress.org
2. From the WordPress admin dashboard go to Plugins, Add New
3. Click Upload Plugin, locate the file, upload
4. In the WordPress dashboard go to Plugins, Installed Plugins, and activate **Frontend Reset Password**
5. Make sure to read the quick start guide! (it's really short)

**Manually using FTP**

1. Download the plugin ZIP file, extract it
2. FTP to your server and go to your root WordPress directory
3. Navigate to wp-content/plugins
4. Upload the parent directory *som-frontend-reset-password* - the folder that contains the file som-frontend-reset-password.php - to that location
5. In the WordPress dashboard go to Plugins, Installed Plugins, and activate **Frontend Reset Password**
6. Make sure to read the quick start guide! (it's really short)

You can customise **Frontend Reset Password** on the Plugins, Frontend Reset Password dashboard page.

== Frequently Asked Questions ==

= Error Messages =

**The e-mail could not be sent:** This happens when the wp_mail() function call fails. If you're testing the plugin on a localhost and don't use a local email server, this error will show.

== Screenshots ==

1. Reset Password Form (Twenty Seventeen Theme)
2. Enter New Password Form (Twenty Seventeen Theme)

== Changelog ==

= 1.0.3 =
* Plugin now translation ready

= 1.0.2 =
* Textdomain set for language file

= 1.0.1 =
* Textdomain fix

= 1.0 =
* Initial release