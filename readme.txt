=== Plus WebP or AVIF ===
Contributors: Katsushi Kawamori
Donate link: https://shop.riverforest-wp.info/donate/
Tags: media, upload, webp, avif
Requires at least: 4.7
Requires PHP: 8.1
Tested up to: 6.6
Stable tag: 5.00
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Generate WebP or AVIF.

== Description ==

= Generate WebP or AVIF =
* Generate WebP or AVIF file when adding image file in Media Library.
* Generate WebP or AVIF from all the images by async/await.
* Optionally, Can replace image files with WebP or AVIF when adding new media, and delete the original image file. Also, when generating all images, the original image file ID will be overwritten as WebP or AVIF and the original image file will be deleted. All URLs in the content are also replaced.

= WP-CLI =

WP-CLI commands are available. If you have a large number of files, WP-CLI commands is more reliable.
* `wp pluswebpavif`
* `wp pluswebpavif webp` -> Generated WebP.
* `wp pluswebpavif avif` -> Generated AVIF.
* `wp pluswebpavif webp --mail=true` -> Send results via email.
* `wp pluswebpavif webp --pid=12152` -> Process only specified Media ID.
* `wp pluswebpavif avif --quality=90` -> Specifies the quality of WebP or AVIF.
* `wp pluswebpavif webp --replace=false` -> WebP or AVIF replacement of images and contents.
* `wp pluswebpavif avif --addext=true` -> Append the webp or avif extension to the original filename.
* `wp pluswebpavif webp --types=image/png,image/gif` -> MIME type to convert.

= How it works =
[youtube https://youtu.be/EIheJryR7j0]

= Filter hooks =
~~~
/** ==================================================
 * Filter for advanced change database.
 * If you want to replace other databases besides content.
 */
add_filter(
	'plus_webp_advanced_change_db',
	function( $before_url, $after_url ) {
		/* Your processing */
		return array( $before_url, $after_url );
	},
	10,
	2
);
~~~

== Installation ==

1. Upload `plus-webp` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

none

== Screenshots ==

1. Media Library
2. Generate
3. Settings

== Changelog ==

= [5.00] 2024/08/23 =
* Changed - Plugin name changed from "Plus WebP" to "Plus WebP or AVIF".
* Added - AVIF conversion.

= [4.20] 2024/08/21 =
* Added - WP-CLI command arguments for settings.
* Changed - Classified WP-CLI commands.

= [4.11] 2024/08/17 =
* Fixed - Transparent PNG conversion issue.
* Added - Added whether to send email to WP-CLI command arguments.
* Added - Added the media ID to the WP-CLI command argument.

= [4.10] 2024/07/19 =
* Added - 'plus_webp_advanced_change_db' filter for Replacement of non-content databases.
* Removed - Blogname for mail from name.

= [4.09] 2024/07/18 =
* Added - WP-CLI command for warning indication at console output.
* Tweak - Webp bulk generation by React.
* Added - Blogname for mail from name.

= [4.08] 2024/07/15 =
* Fix - Support for cases where media exists in the database and does not exist as a file.
* Thanks - [Marcus Karlos](https://profiles.wordpress.org/power2009/).

= [4.07] 2024/06/20 =
* Added - WP-CLI command for webp bulk generation.

= [4.06] 2024/03/05 =
* Fix - Changed file operations to WP_Filesystem.

= 4.05 =
Changed json_encode to wp_json_encode.

= 4.04 =
Supported WordPress 6.4.

= 4.03 =
Fixed link to plugin page.

= 4.02 =
Fixed about sending e-mail.

= 4.01 =
Added description.
Removed unnecessary codes.

= 4.00 =
Moved the location of the Admin page to the Media Library.
The admin screen has been converted to React.
Batch generation of webp by async/await is now supported.
Fixed a problem where title name comparison failed and generated duplicate files.
Metadata generation for WordPress 6.1-alpha to match WordPress 6.2.

= 3.00 =
Supported WordPress 6.1-alpha.

= 2.07 =
Added a hook for [Organize Media Folder](https://wordpress.org/plugins/organize-media-folder/).

= 2.06 =
Fixed with processing when media does not exist.

= 2.05 =
Supported XAMPP.

= 2.04 =
Fixed problem of metadta.

= 2.03 =
Fixed problem of metadta.

= 2.02 =
Fixed content replacement issue.

= 2.01 =
Fixes various messages and branches in ajax.

= 2.00 =
Added ajax generation for webp.

= 1.13 =
Fixed an issue with saving options.
Added options for extension.

= 1.12 =
Fixed clear cron schedules issue.
Fixed problem of threshold big image.

= 1.11 =
Fixed the problem of title acquisition in bulk generation.

= 1.10 =
Fixed background processing.

= 1.09 =
Give details when email notification.
Added the management screen notification of the end of batch generation.

= 1.08 =
Added ability to choose which file types to convert and which not.

= 1.07 =
Supported transparent gif & png.
Fixed file name issue.

= 1.06 =
Fixed content URL replacement issue.

= 1.05 =
Fixed an image replacement issue.
Conformed to the WordPress coding standard.

= 1.04 =
Conformed to the WordPress coding standard.

= 1.03 =
Added html escape on the management screen.

= 1.02 =
Fixed an issue with image conversion.
Fixed an image replacement issue.
Added the setting of quality.

= 1.01 =
Fixed problem of PNG files.
Added uninstall script.

= 1.00 =
Initial release.

== Upgrade Notice ==

= 1.00 =
Initial release.
