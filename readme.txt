# Smart Form Builder

Contributors: dragwyb  
Tags: contact form, form builder, drag and drop form, custom form, ajax form  
Plugin URI: https://dragwyb.com/form-builder/
Author URI: https://dragwyb.com/
Requires at least: 5.8  
Tested up to: 7.0  
Requires PHP: 7.4  
Stable tag: 1.0.3 
License: GPL2  
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The fastest, zero-bloat drag & drop WordPress form builder. Create secure AJAX forms with a React visual editor and custom database tables.

## Description

Looking for the fastest **drag and drop form builder** for WordPress? **Smart Form Builder** is a modern, React-powered **custom form plugin** designed for maximum speed, database performance, and extreme customization.

Whether you need to build a simple **contact form**, a multi-column lead generation form, or a detailed user survey, Dragwyb makes it incredibly easy. Unlike legacy **WordPress form plugins** that slow down your website by flooding the `wp_postmeta` table with individual entry data, Dragwyb uses a highly optimized **custom database table** (`dragwyb_submissions`) with JSON payloads. This guarantees your WordPress database remains lightning-fast, even with thousands of form submissions.

With an intuitive **drag-and-drop React interface**, 21 distinct form fields, deep visual styling controls, and built-in **AJAX form submissions**, Dragwyb is the ultimate tool for developers and beginners alike.

## Live Demo

Explore live examples of the **Smart Form Builder** in action:

🔗 [Dragwyb Form Builder Demo](https://dragwyb.com/form-builder/?utm_source=wpplugin&utm_medium=readme&utm_campaign=dragwyb_form)

👉 **Check out our other plugins:**
* [Click To Chat](https://wordpress.org/plugins/dragwyb-click-to-chat/) – Let your customers connect with you via WhatsApp.
* [Flipbox Addon for Elementor](https://wordpress.org/plugins/flipbox-addon-for-elementor/) – Animated 3D flip boxes.

### 🚀 Why Choose Dragwyb Form Builder? (Main Features)

* **⚡ React & Redux Drag-and-Drop Editor:** Experience a snappy, zero-lag **visual form builder**. Add, duplicate, delete, and reorder fields instantly without page reloads.
* **🗄️ Zero Database Bloat (Custom Tables):** Submissions are saved as highly optimized JSON payloads in a dedicated custom table (`dragwyb_submissions`). Say goodbye to slow queries and massive `wp_postmeta` tables!
* **🛡️ Bulletproof Anti-Spam Protection:** Keep spam out of your inbox with native support for **Google reCAPTCHA v2, reCAPTCHA v3, hCaptcha**, and an invisible built-in **Honeypot form** validation.
* **📱 Responsive Multi-Column Layouts:** Build complex side-by-side grid layouts easily using the native "Row" structure system. Fully responsive for desktop, tablet, and mobile.
* **🚀 AJAX & REST API Submissions:** Forms submit smoothly in the background without reloading the page, utilizing native HTML5 and customized WP REST endpoint validation.
* **⚡ Smart Asset Caching:** Generates and caches a dedicated CSS file per form for blazing-fast frontend loading. Toggle Font Awesome and SVG icons on/off to boost your PageSpeed scores.
* **📊 Built-in Entries Dashboard:** View, search, sort, edit, and delete user submissions directly inside your WordPress admin dashboard.

## ⚙️ Comprehensive Form Settings & Controls

Dragwyb gives you granular control over every pixel and function of your **contact form**:

### 1. Form-Level Settings
* **Limit Entries:** Cap the maximum number of form submissions allowed.
* **After-Submission Actions:** Trigger redirects, send custom Admin and User emails, show styled success/error messages, and save entries to the database.
* **Email Shortcodes:** Dynamically map submitted field data directly into your email subjects and body content.

### 2. Global Form Styling
* **Container Styling:** Customize form backgrounds, CSS filters, borders, box shadows, and alignment.
* **Label & Input Styling:** Visually adjust label colors, floating label styles, required asterisks, input background colors, focus borders, and typography (integrated with Google Fonts).
* **Button Styling:** Customize submit button width, alignment, typography, hover effects, and background colors.

### 3. Field-Level Settings
* **21 Form Fields:** Text, Email, Textarea, Number, Select Dropdown, Checkbox Group, Radio Button, Date, Time, URL, Phone, Name, Address, File Upload, Range Slider, Captcha, Hidden, HTML, Section Break, Row, and Button.
* **Advanced Field Controls:** Set responsive grid column spans, minimum/maximum lengths, custom CSS classes, placeholder text, and default values.
* **File Upload Limits:** Define allowed file extensions (jpg, png, pdf, doc, etc.) and file sizes.

== Upcoming Features ==

We are constantly working to improve Smart Form Builder by Dragwyb. Here are some of the features we plan to add in upcoming releases:

*   Conditional field
*   Form Submission
*   Country Code
*   Country Field
*   Radio Image
*   Step Field
*   And much more!

## Key Features

* **100% Mobile Responsive Forms:** Built-in mobile and tablet breakpoints ensure your forms look perfect on any device.
* **Developer Friendly Hooks:** Highly extensible architecture with PHP hooks for registering custom fields, after-submission actions, and custom validation filtering.
* **Client-Side & Server-Side Validation:** Inline field error rendering that instantly focuses on the first invalid field upon submission.
* **Dynamic Settings Manager:** Globally enable or disable specific form fields from loading in the builder.

👉 [View Live Demo](https://dragwyb.com/form-builder/?utm_source=wpplugin&utm_medium=readme&utm_campaign=dragwyb_form)

## Installation

### From WordPress Plugin Directory
1. Go to your WordPress dashboard → **Plugins** → **Add New**
2. Search for **Dragwyb Form Builder**
3. Click **Install Now**
4. Activate the plugin after installation

### Manual Upload
1. Download the latest ZIP file
2. Go to **Plugins** → **Add New** → **Upload Plugin**
3. Upload the ZIP file and click **Install Now**
4. Activate the plugin

## Screenshots

1. **React Drag & Drop Builder:** The modern, fast interface for building custom forms.
2. **Global & Field Styling:** Visual controls for typography, colors, and borders.
3. **Form Entries Database:** Clean and searchable backend dashboard of all your form submissions.
4. **Row & Layout Settings:** Creating multi-column responsive grid forms.
5. **Anti-Spam Integration:** Global settings for reCAPTCHA and hCaptcha integration.

## Frequently Asked Questions

#### Is this WordPress contact form plugin free?
Yes, the core **Dragwyb Form Builder** is completely free to use to build unlimited forms.

#### Will this plugin slow down my website's loading speed?
No! Dragwyb is engineered specifically for performance. We generate and cache per-form CSS files in your uploads folder, allow you to selectively disable heavy assets like Font Awesome, and process all form submissions efficiently via the REST API.

#### Does it bloat the WordPress database like other form builders?
Absolutely not. Unlike legacy plugins that save every single field as a new, separate row in the `wp_postmeta` table (which severely degrades database performance), Dragwyb uses a highly optimized custom database table and stores form submission data as a clean JSON payload.

#### How do I stop spam form submissions?
We offer multiple robust layers of defense. You can enable the invisible **Honeypot** anti-spam feature, or integrate **Google reCAPTCHA (v2 or v3)** or **hCaptcha** directly from the plugin settings.

#### Can I put form fields side-by-side?
Yes! Using the advanced "Row" structure field, you can easily create multi-column layouts and control the grid column span and gaps for complex, responsive designs.

#### Where do I see my contact form submissions?
All entries are saved securely to your local database (if the "Save Submissions" action is enabled) and can be viewed, edited, searched, or deleted from the **Entries** dashboard in the WordPress admin menu.

#### Can I customize the autoresponder email sent after submission?
Yes. You can configure both User Emails and Admin Emails, complete with shortcode replacements for dynamic content mapping, custom subjects, and fully personalized message bodies.

#### Can I change the colors and typography of my forms?
Yes, the builder features a comprehensive **Global Style Settings** and **Field-Level Settings** toolbar. You can easily modify background colors, border styles, label spacing, focus states, button hover effects, and Google Fonts typography without writing any CSS.

## Recommended Plugins

If you find this form builder helpful, you might also like our other optimization and marketing tools:
* 💬 **[Click To Chat](https://wordpress.org/plugins/dragwyb-click-to-chat/)** – Connect with your website visitors instantly through WhatsApp, Telegram, and other chat platforms.
* 🔄 **[Flipbox Addon for Elementor](https://wordpress.org/plugins/flipbox-addon-for-elementor/)** – Create interactive, 3D animated flip boxes and product showcases.

## Acknowledgements

Built with ❤️ by Dragwyb. Thanks to the WordPress developer community for continually pushing the boundaries of what modern form plugins can achieve.

## License

This plugin is licensed under the GPL2 license.

## Screenshots
1. Drag & Drop Visual Form Builder.
2. Clean, Responsive Frontend Forms
3. Advanced Visual Styling & Customization.
4. Powerful Submission Actions & Redirects.
5. Built-in Entry Management.
6. Easy Form Management & Shortcodes.
7. Spam Protection & Advanced Security.
8. Performance Optimizer: Toggle Unused Fields.
9. Seamless Global API Integrations.

== Changelog ==

= 1.0.3 16 April 2026 =

* Update dist file to production build instead of development build
* Mention External libraries in readme.txt
* Add source code link in readme.txt

= 1.0.2 15 April 2026 =

* Improved validation and security checks.

= 1.0.1 13 April 2026 =

*   Update plugin name and slug

= 1.0.0 30 March 2026 =

*   Initial release
