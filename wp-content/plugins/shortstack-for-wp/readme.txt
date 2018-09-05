=== ShortStack for WP ===
Contributors: shortstackben, revoltmedia
Tags: shortstack, facebook, campaign, embed, contest, promotion
Requires at least: 3.1
Tested up to: 4.8.2
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
**Easily add contests, giveaways, forms and promotions to your WordPress site**
_ShortStack for WordPress_

Create a ShortStack account and get your contest or promotion launched in minutes. Choose one of our professionally designed templates (or start from scratch) and customize it to your liking. When you are ready, [embed it anywhere](https://www.shortstack.com/embedded-campaigns/) in your WordPress site using our plugin. ShortStack’s plugin provides a shortcode to easily place your campaign anywhere you like.

When your contest or promotion is up and running, ShortStack will store all entry data: email addresses, photos, videos, votes and more. You can export your data at any time to use for future marketing efforts. ShortStack also provides in-depth analytics to help you understand where your entrants are coming from, best times to promote, and the overall “health” of your campaign.

More than 400,000 businesses, brands and agencies use ShortStack to create promotions, photos contests and landing pages that generate more sales.

Don’t have a ShortStack account yet? Just head to [shortstack.com](https://www.shortstack.com) and get started. Our [pricing](https://www.shortstack.com/pricing/) starts at free!

== Installation ==

= Prerequisites =
- This plugin utilizes ShortStack's [__Embed__](https://www.shortstack.com/embedded-campaigns/) feature.
- The Campaign must be published. You can only embed published Campaigns.

= Setup =
1. Upload the `shortstack-wordpress` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

= Usage =
1. Within the Campaign Designer, open the [Embed Settings](https://help.shortstackapp.com/hc/en-us/articles/231473348-Embedding-A-Campaign) panel for your published campaign.
2. Click on the __WordPress__ link on the left of the dialog.
3. Select and copy the __WordPress Shortcode__.
4. Paste the shortcode anywhere inside the body of a Post or Page within your WordPress installation.

_Example:_ `[shortstack smart_url='//a.pgtb.me/#####' responsive='true' autoscroll_p='true']`

== Frequently Asked Questions ==

= Will you add a feature to ShortStack or this WordPress plugin? =

Maybe! We live on feedback. [Send our support team a note!](http://shortstack.zendesk.com/)

= How do I get help? =

Check our [detailed help docs](https://help.shortstackapp.com/hc/en-us) or [contact our support team](http://shortstack.zendesk.com/).

== Screenshots ==


== Changelog ==
= 1.0.3 =
* Added function_exists checks (Per [Plugin Handbook](https://developer.wordpress.org/plugins/the-basics/best-practices/#check-for-existing-implementations))
* Added function prefixes to avoid conflicts (Per [Plugin Handbook](https://developer.wordpress.org/plugins/the-basics/best-practices/#avoid-naming-collisions))
* Added JS Subresource integrity attributes to JS files.
* Moved js directory under public directory (Per [Plugin Handbook](https://developer.wordpress.org/plugins/the-basics/best-practices/#folder-structure))
* Check for HTTPS rather than using // for absolute URLs to support older versions.

= 1.0.2 =
* Moved JS out of the PHP
* Fixed warnings about $settings variable being undefined with WP_DEBUG enabled.

= 1.0.1 =
* Adds support for the ShortStack responsive embed code and improves mobile support
* Removes iframe border
* Adds scrollbar to fixed embed code when necessary

= 0.0.1 =
* First release.
