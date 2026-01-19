=== MpesaPaywallPro ===
Contributors: festusmurimi
Donate link: https://festuswp.gumroad.com/l/MpesaPaywallPro
Tags: m-pesa, paywall, payment-gateway, content-monetization, subscription
Requires at least: 6.2.1
Requires PHP: 7.4
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Monetize your WordPress content with M-Pesa paywall integration. Lock premium posts behind a secure payment gateway and get paid instantly.

== Description ==

MpesaPaywallPro is a powerful WordPress plugin that integrates the M-Pesa payment gateway with your website, enabling you to restrict premium content behind a secure paywall. Monetize your content effortlessly and accept payments directly from your audience.

**Key Features:**

* **M-Pesa Payment Integration** - Seamless M-Pesa Daraja integration for payment processing
* **Content Paywall** - Lock posts and pages behind a secure paywall with custom pricing
* **Easy Configuration** - Simple admin interface for setup and management
* **Responsive Design** - Mobile-friendly payment interface
* **Secure Transactions** - Encrypted payment processing and data handling
* **User Management** - Track user access and subscription status
* **Automatic Updates** - Built-in GitHub update checker
* **Multi-Language Support** - Internationalization ready

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/MpesaPaywallPro` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to MpesaPaywallPro in the admin menu to configure your M-Pesa credentials
4. Start protecting your content!

== Configuration ==

1. Go to **MpesaPaywallPro Settings** in your WordPress admin panel
2. Enter your M-Pesa Daraja credentials:
   - Consumer Key
   - Consumer Secret
   - Business Shortcode
   - Pass Key
3. Select your environment (Sandbox for testing, Production for live)
4. Save your settings

== Usage ==

**Protecting Content:**

1. Edit a post or page you want to protect
2. Scroll to the **MpesaPaywall** meta box
3. Check "Lock this post behind a paywall"
4. Enter the price in KES (Kenyan Shillings)
5. Update/Publish the post

Users will see a paywall notice and must complete M-Pesa payment to access the content.

== Frequently Asked Questions ==

= What is M-Pesa Daraja? =
M-Pesa Daraja is Safaricom's developer API that allows businesses to integrate M-Pesa payments into their applications.

= Can I test before going live? =
Yes! Use the sandbox environment during setup. Switch to production when ready.

= What currencies are supported? =
Currently, the plugin supports KES (Kenyan Shillings) for M-Pesa payments.

= Do users need an M-Pesa account? =
Yes, users must have an active M-Pesa account to complete payments.

= Can I set different prices for different posts? =
Yes, you can set custom prices for each protected post or page.

= Is my payment data secure? =
All payment data is encrypted and processed through M-Pesa's secure servers. No sensitive information is stored on your website.

= Does this plugin work with WordPress multisite? =
Yes, the plugin is compatible with WordPress multisite installations.

== Screenshots ==

1. Admin settings panel for M-Pesa configuration
2. Meta box for protecting individual posts with paywall
3. Frontend paywall display for users
4. Payment completion screen

== Changelog ==

= 1.0.0 =
* Initial release
* M-Pesa payment gateway integration
* Content paywall functionality
* Admin dashboard and settings
* GitHub update checker integration
* Multi-language support (i18n ready)

== Upgrade Notice ==

= 1.0.0 =
Initial release of MpesaPaywallPro. Start monetizing your WordPress content today!

== Requirements ==

* WordPress 6.2.1 or higher
* PHP 7.4 or higher (PHP 8.1+ recommended)
* Active M-Pesa Daraja account
* Composer for dependency management

== Support ==

For support, bug reports, and feature requests, visit the GitHub repository:
https://github.com/peanutsx50/MpesaPaywallPro

Contact the author: murimifestus09@gmail.com

== Author ==

**Festus Murimi**
- Email: murimifestus09@gmail.com
- LinkedIn: https://www.linkedin.com/in/festus-murimi-b41aa2251/
- Gumroad: https://festuswp.gumroad.com/l/MpesaPaywallPro