# MpesaPaywallPro

A WordPress plugin that integrates the M-Pesa payment gateway and enables you to restrict premium content behind a paywall, allowing you to monetize your website effectively.

<p align="center">
  <img src="assets/demo.gif" width="100%" alt="MpesaPaywallPro Demo">
</p>


## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Plugin Structure](#plugin-structure)
- [Author & Contact](#author--contact)
- [Contributing](#contributing)
- [License](#license)
- [Support](#support)

## Features

- **M-Pesa Payment Integration**: Seamless integration with the M-Pesa Daraja API (STK Push)
- **Content Paywall**: Restrict premium content behind a secure paywall
- **Easy Configuration**: Simple admin interface with tabbed settings
- **Responsive Design**: Mobile-friendly payment interface
- **Secure Transactions**: SSL enforcement, callback authentication, and IP validation
- **User Management**: Track user subscriptions and access via cookies/user meta
- **Multiple Content Types**: Protect pages, posts, or custom content
- **Error Logging**: Comprehensive logging system with daily rotation
- **GitHub Updates**: Automatic update notifications from GitHub (self-hosted version)
- **Internationalization**: Translation-ready with Swahili (Kenya) included

## Requirements

- **WordPress**: 6.2.1 or higher
- **PHP**: 7.4 or higher (recommended: 8.1+)
- **Composer**: For dependency management
- **M-Pesa Account**: M-Pesa daraja Details

## Installation

### Method 1: Manual Installation

1. Download the plugin files from the [GitHub repository](https://github.com/peanutsx50/MpesaPaywallPro)
2. Extract the plugin folder to `/wp-content/plugins/` directory
3. Navigate to **Plugins** in your WordPress admin panel
4. Find **MpesaPaywallPro** and click **Activate**

### Method 2: Upload via WordPress Admin

1. In your WordPress admin panel, go to **Plugins** → **Add New**
2. Click **Upload Plugin**
3. Select the plugin zip file and click **Install Now**
4. Click **Activate Plugin**

## Configuration

### Initial Setup

1. After activation, navigate to the plugin settings in the WordPress admin panel
2. Configure your M-Pesa merchant details:
   - Consumer Key
   - Consumer Secret
   - Business Shortcode
   - Pass Key
   - Environment (Sandbox/Production)

3. Set up payment options:
   - Payment amounts

### Dashboard

The plugin admin interface provides:

- **Overview**: Summary of recent transactions and subscription status
- **Settings**: M-Pesa and general configuration options
- **Subscriptions**: Manage user subscriptions and access
- **Reports**: View payment history and analytics

## Usage

### Protecting Content

1. **Edit a Post/Page**: Navigate to the post or page you want to protect
2. **Paywall Settings**: Look for the MpesaPaywallPro meta box
3. **Enable Paywall**: Check "Enable Paywall for this content"
4. **Set Price**: Enter the amount users must pay to access
5. **Publish**: Save your changes

### User Experience

Users visiting protected content will:

1. See a paywall notice with the payment amount
2. Click the "Unlock Content" button
3. Enter their phone number
4. Complete the M-Pesa payment
5. Gain immediate access to protected content

### Payment Flow

```
User Views Protected Content
    ↓
Paywall Displayed
    ↓
User Initiates Payment
    ↓
M-Pesa Prompt Sent to Phone
    ↓
User Enters M-Pesa PIN
    ↓
Payment Processed
    ↓
Content Access Granted
```

### Local Development with ngrok

To test the plugin locally with HTTPS (required for secure M-Pesa callbacks), you can use ngrok to expose your local WordPress installation.

#### Setup Steps

1. **Install ngrok**: Download from [ngrok.com](https://ngrok.com) or install via package manager

2. **Configure wp-config.php**: Add the following code to your `wp-config.php` file before the line `/* That's all, stop editing! */`:

```php
define('WP_HOME', 'https://XXXX.ngrok-free.dev');
define('WP_SITEURL', 'https://XXXX.ngrok-free.dev');

define('FORCE_SSL_ADMIN', true);
define('FORCE_SSL_LOGIN', true);

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}
```

Replace `XXXX` with your actual ngrok subdomain.

3. **restart apache2**: once you add the code to your wp-config, you need to restart apache2 for changes to take effect
```bash
sudo systemctl restart apache2
```

4. **Start ngrok**: Run the following command in your terminal:

```bash
ngrok http <port>
```

Replace `<port>` with your local WordPress server port (typically 8000, 8080, 3000, etc.)

Example:
```bash
ngrok http 8000
```

5. **Test Payment Flow**: You can now test M-Pesa payment processing with full HTTPS support

## Plugin Structure

```
mpesapaywallpro/
├── admin/                              # Admin panel functionality
│   ├── MpesaPaywallProAdmin.php        # Admin class
│   ├── index.php                       # Security index
│   ├── css/
│   │   └── admin-settings.css          # Admin styles
│   ├── js/
│   │   ├── dist/                       # Minified production scripts
│   │   │   ├── admin-settings.min.js
│   │   │   ├── content-locked-meta-box.min.js
│   │   │   └── test-connection.min.js
│   │   └── temp/                       # Development scripts
│   │       ├── admin-settings.js
│   │       ├── content-locked-meta-box.js
│   │       └── test-connection.js
│   └── partials/                       # Admin templates
│       ├── access-control.php
│       ├── admin-settings.php
│       ├── content-locked-meta-box.php
│       ├── mpesa-setup.php
│       └── paywall-settings.php
├── includes/
│   ├── base/
│   │   ├── MpesaPaywallPro.php         # Core plugin class
│   │   ├── MpesaPaywallProActivator.php    # Activation hooks
│   │   ├── MpesaPaywallProDeactivator.php  # Deactivation hooks
│   │   ├── MpesaPaywallProI18n.php     # Internationalization
│   │   ├── MpesaPaywallProLoader.php   # Hook loader
│   │   └── index.php                   # Security index
│   └── core/
│       ├── MpesaPaywallProLogger.php   # Error logging system
│       ├── MpesaPaywallProMpesa.php    # M-Pesa API integration
│       ├── MpesaPaywallProUtils.php    # Utility functions
│       └── index.php                   # Security index
├── public/                             # Frontend functionality
│   ├── MpesaPaywallProPublic.php       # Public class
│   ├── index.php                       # Security index
│   ├── css/
│   │   ├── phone-number-modal.css      # Payment modal styles
│   │   └── public-paywall.css          # Paywall display styles
│   ├── js/
│   │   ├── dist/                       # Minified production scripts
│   │   │   ├── check-payment-status.min.js
│   │   │   ├── initiate-payment.min.js
│   │   │   └── phone-number-modal.min.js
│   │   └── temp/                       # Development scripts
│   │       ├── check-payment-status.js
│   │       ├── initiate-payment.js
│   │       └── phone-number-modal.js
│   └── partials/                       # Frontend templates
│       ├── paywall-display.php
│       └── phone-number-modal.php
├── languages/                          # Translation files
│   ├── mpesapaywallpro.pot
│   ├── mpesapaywallpro-sw_KE.mo
│   └── mpesapaywallpro-sw_KE.po
├── vendor/                             # Composer dependencies
├── mpesapaywallpro.php                 # Main plugin file
├── uninstall.php                       # Cleanup on uninstall
├── index.php                           # Security index
├── composer.json                       # Dependency configuration
├── phpstan.neon.dist                   # PHPStan configuration
├── LICENSE.txt                         # GPL v2 license
└── README.txt                          # WordPress readme
```

## Author & Contact

**Surge Technologies**

- **Email**: [admin@surgetech.co.ke](mailto:admin@surgetech.co.ke)
- **LinkedIn**: [Surge Technologies](https://surgetech.co.ke/)
- **Website**: [MpesaPaywallPro](https://surgetech.co.ke/mpesapaywallpro)
- **Gumroad**: [GumroadMpesaPaywallPro](https://festuswp.gumroad.com/l/BPMpesaGateway)

## Contributing

We welcome contributions! Here's how you can help:

### Development Setup

1. Fork the repository on GitHub
2. Clone your fork locally:

   ```bash
   git clone git@github.com:peanutsx50/MpesaPaywallPro.git
   ```

3. Create a feature branch:

   ```bash
   git checkout -b feature/your-feature-name
   ```

4. Make your changes and commit:

   ```bash
   git commit -am 'Add your feature description'
   ```

5. Push to your fork:

   ```bash
   git push origin feature/your-feature-name
   ```

6. Submit a Pull Request on the main repository

### Installing Composer Dependencies for Production

After installation, install the required Composer dependencies optimized for production:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

This command:
- `--no-dev`: Excludes development dependencies
- `--optimize-autoloader`: Optimizes the autoloader for production performance
- `--no-interaction`: Runs without prompting for user input

### Using PHPStan

Run PHPStan to analyze your code for potential errors and type issues:

```bash
./vendor/bin/phpstan analyse
```

This will perform static analysis on the plugin code according to the configuration in `phpstan.neon.dist`.

### Code Standards

- Follow WordPress coding standards
- Use PSR-4 namespacing
- Add appropriate PHPDoc comments
- Ensure backward compatibility

### Reporting Issues

Found a bug? Please report it on our [GitHub Issues](https://github.com/peanutsx50/MpesaPaywallPro/issues) with:

- Detailed description of the issue
- Steps to reproduce
- Expected vs. actual behavior
- WordPress and PHP versions
- Any relevant error logs

## License

MpesaPaywallPro is licensed under the **GNU General Public License v2 or later**.

For full license details, see [LICENSE.txt](LICENSE.txt)

You are free to:

- ✅ Use the plugin on as many sites as you wish
- ✅ Modify the code to fit your needs
- ✅ Distribute the plugin (with proper attribution)

## Support

### Documentation

- Check the [GitHub Wiki](https://github.com/peanutsx50/MpesaPaywallPro/wiki) for detailed guides
- Review [Frequently Asked Questions](#frequently-asked-questions)

### Getting Help

1. **Search existing issues**: Check if your question has been answered
2. **Create a new issue**: If not, describe your problem in detail
3. **Contact**: Reach out via email or LinkedIn

## Frequently Asked Questions

### Q: Can I test the plugin in sandbox mode?

**A**: Yes! Configure the plugin to use M-Pesa's sandbox environment during testing.

### Q: What payment methods does this support?

**A**: Currently, the plugin supports M-Pesa payments. Additional payment gateways may be added in future releases.

### Q: Can I customize the paywall appearance?

**A**: Yes, you can modify the templates in the `public/partials/` directory or use WordPress hooks and filters.

### Q: How do I get reports of transactions?

**A**: Navigate to the Reports section in the plugin admin panel to view detailed transaction history.

### Q: What happens if a user already paid?

**A**: The plugin automatically grants access to users who have completed payment. Repeat attempts to access will not charge again.

## Version History

### v1.0.0 (Initial Release)

**Core Features:**
- M-Pesa payment gateway integration (STK Push)
- Content paywall functionality for posts and pages
- Admin dashboard with tabbed settings interface
- Plugin update checker for GitHub releases
- Error logging system with daily rotation

**Security Implementations:**
- Callback URL authentication with `mpp_auth` token
- Pending transaction tracking via transients to prevent MITM attacks
- SSL verification and enforcement for all payment requests
- Safaricom IP validation for callback requests
- Content Security Policy headers (X-Content-Type-Options, X-Frame-Options, Referrer-Policy)
- Proper nonce verification with `wp_unslash()` on all AJAX handlers
- Direct access prevention on all index.php files

**WordPress.org Compliance Fixes:**
- Output escaping with `esc_html_e()` for all translatable strings
- Replaced `date()` with `gmdate()` and `wp_date()` for timezone safety
- Added `wp_unslash()` before all `$_SERVER`, `$_POST`, `$_GET`, `$_COOKIE` access
- Replaced `unlink()` with `wp_delete_file()` in uninstall.php
- Replaced `print_r()` with `wp_json_encode()` in logger
- Fixed text domain parameter in all translation functions
- Added proper `isset()` checks for cookie access
- Corrected "Tested up to" version format in README.txt

**JavaScript Improvements:**
- Exponential backoff for payment status polling (reduces API calls)
- Removed console logging from production builds
- Strengthened phone number validation regex
- Minified production builds in `/dist` folders

**Bug Fixes:**
- Fixed test connection handler mismatch (AJAX vs REST API)
- Fixed cookie value parsing with proper unslashing
- Fixed double semicolon syntax error in logger
- Fixed IP validation using `filter_var()` with FILTER_VALIDATE_IP

## Roadmap

### v1.2.0 (Security & Performance Enhancements)

Future enhancements planned:

- [ ] Rate limiting on payment endpoints (prevent API abuse)
- [ ] M-Pesa credentials encryption (AES-256-CBC)
- [ ] GDPR-compliant IP anonymization in logs
- [ ] Prefix all global variables with `mpp_`
- [ ] Prefix all functions with `mpp_`
- [ ] Add nonce verification to admin tab navigation
- [ ] JSON decode error handling for M-Pesa responses
- [ ] Shortcode numeric-only validation
- [ ] Convert inline CSS to CSS custom properties
- [ ] Options caching singleton to reduce database queries
- [ ] Replace filesystem operations with WP_Filesystem API
- [ ] Add PHPCS ignore comments for intentional direct database calls
- [ ] Optimize script/style loading (only on plugin pages)

### v1.3.0 (Feature Expansion)

- [ ] Advanced analytics and settings dashboard
- [ ] Email notifications and payment reminders
- [ ] Additional payment gateways
- [ ] Multiple payment plans (daily, monthly, yearly)
- [ ] Subscription management interface

## Security & Privacy

- **SSL Enforcement**: All payment requests require HTTPS
- **Callback Authentication**: Secure `mpp_auth` token prevents callback spoofing
- **IP Validation**: Safaricom IP whitelist for callback verification
- **Transaction Tracking**: Transient-based pending transaction verification
- **Security Headers**: X-Content-Type-Options, X-Frame-Options, Referrer-Policy
- **Input Sanitization**: All user input sanitized and validated
- **Output Escaping**: WordPress escaping functions on all output
- **CSRF Protection**: Nonce verification on all forms and AJAX handlers
- **Error Logging**: Secure logging with IP anonymization options
- **No Sensitive Storage**: M-Pesa credentials use WordPress options API

## Acknowledgments

- [Yahnis Elsts Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) for update functionality
- WordPress community for guidelines and best practices
- M-Pesa for payment processing

---

**Need help?** Create an issue on GitHub or contact the me directly via email.

**Enjoying the plugin?** Please give it a ⭐ on GitHub!
