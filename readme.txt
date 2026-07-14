=== Smart Form Builder ===
Contributors: dragwyb  
Tags: contact form, form builder, drag and drop form, custom form, ajax form
Plugin URI: https://dragwyb.com/form-builder/
Author URI: https://dragwyb.com/
Requires at least: 5.8  
Tested up to: 7.0  
Requires PHP: 7.4  
Stable tag: 1.1.0
License: GPL2  
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Fast, zero-bloat drag & drop contact form builder. Build secure AJAX forms with a visual React editor and optimized custom database tables.
== Description ==

Looking for the fastest **drag and drop form builder** for WordPress? **Smart Form Builder** by Dragwyb is a cutting-edge, React-powered **custom form plugin** engineered for blistering speed, zero-bloat database performance, and unparalleled styling customization. 

Whether you need to create a simple **WordPress contact form**, a high-converting lead generation form, a multi-step user survey, or an inquiry form for Elementor or Gutenberg layouts, Dragwyb makes it effortless.

### ⚡ Say Goodbye to Database Bloat
Legacy **WordPress form plugins** slow down your website by flooding the `wp_postmeta` table with separate database rows for every single field submission. Dragwyb solves this with an optimized **custom database table** (`dragwyb_submissions`) using structured JSON payloads. This ensures your WordPress database remains lightning-fast, even with tens of thousands of entry submissions.

### 🎨 Fully Visual React Editor
With an intuitive, lag-free **drag-and-drop React interface**, 21 distinct dynamic form fields, deep visual styling controls, and seamless **AJAX form submissions**, Dragwyb bridges the gap between developer flexibility and beginner-friendly ease of use.

## 🔗 Useful Links

*   **[Live Demo](https://dragwyb.com/form-builder/?utm_source=wpplugin&utm_medium=readme&utm_campaign=dragwyb_form)** — Test the builder live!
*   **[Click To Chat](https://wordpress.org/plugins/dragwyb-click-to-chat/)** — Connect with your customers via WhatsApp.
*   **[Flipbox Addon for Elementor](https://wordpress.org/plugins/flipbox-addon-for-elementor/)** — Stunning 3D flip boxes for your site.

---

### 🚀 Key Features & Benefits

*   **⚡ React & Redux Drag-and-Drop Editor:** Experience a snappy, fluid, and zero-lag **visual form builder**. Add, duplicate, delete, and reorder fields instantly with zero page reloads.
*   **🗄️ Zero Database Bloat:** Submissions are saved securely in a dedicated custom table (`dragwyb_submissions`). Say goodbye to sluggish slow-queries and database fragmentation!
*   **🛡️ Bulletproof Anti-Spam Protection:** Keep spam completely out of your inbox with native integrations for **Google reCAPTCHA v2, reCAPTCHA v3, hCaptcha**, and an invisible built-in **Honeypot form validation**.
*   **📱 Responsive Multi-Column Layouts:** Design complex side-by-side grid layouts using the native "Row" structure system. 100% responsive across desktop, tablet, and mobile.
*   **🚀 Clean AJAX & REST API Submissions:** Forms submit seamlessly in the background without reloading the page, utilizing native HTML5 features and secure WP REST endpoint validation.
*   **⚡ Smart Asset Caching & Optimization:** Generates and caches a dedicated, minimized CSS file per form for blazing-fast frontend rendering. Toggle Font Awesome and SVG icons on/off to boost your Google PageSpeed Insights scores.
*   **📊 Comprehensive Entries Dashboard:** View, search, sort, filter, and manage user form submissions directly inside your WordPress admin area.

---

## ⚙️ Advanced Form Settings & Controls

Dragwyb gives you granular control over every pixel, layout constraint, and server function:

### 1. Form-Level Intelligence

*   **After-Submission Actions:** Set up instant URL redirects, send customized Admin/User emails, or trigger tailored success and error messages.
*   **Dynamic Email Shortcodes:** Seamlessly map user-submitted data directly into your email subjects and body notifications.

### 2. Global Codeless Styling Engine

*   **Form Container Styling:** Customize canvas background colors, gradients, CSS filters, borders, padding, box shadows, and global alignments.
*   **Label & Input Aesthetics:** Visually customize floating labels, label typography, mandatory asterisks, background states, focus borders, and Google Fonts integration.
*   **CTA Button Styling:** Tailor submit buttons with dynamic width parameters, alignment, hover states, transitions, and text stylings.

### 3. Versatile Form Fields (21 Available)
Build any custom form layout using: *Text, Email, Textarea, Number, Select Dropdown, Checkbox Group, Radio Button, Date, Time, URL, Phone, Name, Address, File Upload, Range Slider, Captcha, Hidden Fields, Custom HTML, Section Breaks, Layout Rows, and Buttons.*

---

## == Upcoming Features ==

We are continually optimizing Smart Form Builder. Our development roadmap includes:

*   🔀 Advanced Conditional Logic (Show/Hide fields dynamically)
*   🌍 Country Field & Smart Country Code Selector
*   🖼️ Radio Image Selectors (Visual selections)
*   🪜 Multi-step Form Wizard Fields

---

## == Installation ==

### Method 1: Via WordPress Admin
1. Navigate to **Plugins** → **Add New** within your dashboard.
2. Search for `Dragwyb Form Builder`.
3. Click **Install Now** and then **Activate**.

### Method 2: Manual Upload
1. Download the plugin ZIP file from the directory.
2. Go to **Plugins** → **Add New** → **Upload Plugin**.
3. Choose the ZIP file, upload it, and click **Activate**.

---


== External Services ==

This plugin utilizes the following libraries:

* **Pickr:** A high-performance color picker library. Used for design customization features.
    * **Source:** [https://github.com/Simonwep/pickr](https://github.com/Simonwep/pickr)
    * **Local Paths:** `assets/lib/pickr/css/index.css`, `assets/lib/pickr/js/index.js`
* **Font Awesome (Free):** Used for providing iconography within the editor and frontend forms.
    * **Source/License:** [https://fontawesome.com/license/free](https://fontawesome.com/license/free)
    * **Local Path:** `assets/font-awesome/v5/all.min.css`

---

## == Frequently Asked Questions ==

#### Is this WordPress contact form plugin free?
Yes! The core features of the **Dragwyb Form Builder** are completely free, allowing you to create unlimited forms and collect unlimited form submissions.

#### Will this plugin slow down my PageSpeed scores?
No, quite the opposite. Dragwyb is built strictly for performance optimization. By generating per-form cached CSS files, offering global script management toggles, and handling background execution via the WP REST API, your pages stay light.

#### How does it protect against database clutter?
Unlike standard form plugins that generate dozens of metadata rows in `wp_postmeta` for a single entry, Dragwyb uses a single line entry containing optimized JSON strings inside a dedicated SQL table. Your queries stay lightning-fast.

#### Can I create multi-column forms?
Yes! Our responsive grid "Row" element lets you place inputs side-by-side easily and choose exactly how many columns they span on mobile vs. desktop devices.

#### Where are the form submissions stored?
If the "Save Submissions" action is enabled on your form, all data points are saved securely on your local server. You can view, search, export, or manage entries under the plugin's **Entries** dashboard.

---

## == Screenshots ==

1. **React Drag & Drop Builder:** The modern, fast interface for building custom forms.
2. **Form Preview:** Instant feedback on your form design.
3. **Global & Field Styling:** Visual controls for typography, colors, and borders.
4. **Submission Settings:** Form actions after submission.
5. **Form Entries Database:** Clean and searchable backend dashboard of all your form submissions.
6. **Easy Form Management:** Shortcodes and rapid status configurations.
7. **Anti-Spam Integration:** Global settings for reCAPTCHA and hCaptcha integration.
8. **Performance Optimizer:** Globally toggle unused fields to save asset execution times.
9. **API Integrations:** API integrations with other platforms.

---

## == Changelog ==

= 1.1.0 (2026-07-15) =
* Added Conditional field settings.
* Added dynamic tags option in controls.
* Added step field for multi-step forms.
* Added style settings for step indicator.
* Added progress bar styling.
* Added button style settings for previous and next buttons.
* Added bulk delete action button in entries and error logs.
* Added entrie and error logs export feature.
* Added form editor button in form preview adminbar.
* Optimize assets loading.
* Improved drodown control stylings.
* Improved step field preview on editor.

= 1.0.6 (20 June 2026) =
* Fixed Form Editor styling issue dashboard menus showing.
* Fixed feedback notice not working.

= 1.0.5 (19 June 2026) =
* Added PHP older version compabitility.
* Fixed form editor preview Captcha field HTML rendering.
* Improved plugin short description.
* Update form menu label.

= 1.0.4 (19 June 2026) =
* Public release.

= 1.0.3 (16 April 2026) =
* Updated distribution files to production optimized builds.
* Documented external asset libraries in readme.txt.
* Appended source code repositories to the codebase notes.

= 1.0.2 (15 April 2026) =
* Hardened internal sanitization, validation, and security check processes.

= 1.0.1 (13 April 2026) =
* Refactored internal plugin naming conventions and plugin slug.

= 1.0.0 (30 March 2026) =
* Initial core release.