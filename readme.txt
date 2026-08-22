=== Dragwyb Forms – Contact Forms, Conditional Form, MultiStep Form, Analytics, SMTP ===
Contributors: dragwyb  
Tags: contact form, forms, custom form, smtp, analytics
Plugin URI: https://dragwyb.com/product/form/?utm_source=wpplugin&utm_medium=plugin_uri&utm_campaign=form_builder_demo
Author URI: https://dragwyb.com/?utm_source=wpplugin&utm_medium=author_uri&utm_campaign=form_builder_demo
Requires at least: 5.9
Tested up to: 7.1 
Requires PHP: 7.4  
Stable tag: 1.3.1
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create unlimited contact forms, save entries, conditional fields, multi-step form, lead generation forms, built-in SMTP, and GDPR compliance.

== Description ==

Welcome to **Dragwyb Forms**—the fastest, most intuitive **WordPress contact form** plugin. Engineered specifically for speed and performance, our lightweight React-powered editor allows you to build everything from a simple **contact form** to complex, high-converting **custom forms**—without writing a single line of code and without slowing down your website.

Whether you are capturing leads, collecting user surveys, or designing inquiry forms for Gutenberg and Elementor, Dragwyb Forms provides enterprise-level features with absolute ease of use. 

*   **[Live Demo](https://dragwyb.com/demo/form/?utm_source=wpplugin&utm_medium=readme&utm_campaign=dragwyb_form)** — Test the fastest WordPress forms today!

### ⚡ Zero Database Bloat (A Faster WordPress)
Traditional form plugins choke your database by creating dozens of `wp_postmeta` rows for a single form submission. Dragwyb Forms eliminates this performance bottleneck. We use an optimized **custom database table** (`dragwyb_submissions`) with structured JSON payloads, guaranteeing your website stays lightning-fast even with tens of thousands of entries.

### 📈 Built for Marketing & Conversions
Stop guessing how your forms are performing. With our newly integrated **Analytics Dashboard**, you can track form views, submission rates, and conversions in real-time. Automatically capture **UTM parameters and device data** with every entry to see exactly where your leads are coming from. 

---

### 🚀 Core Features & SEO Benefits

*   **⚡ Visual React Editor:** Experience a zero-lag, drag-and-drop form editor. Add, reorder, and style fields instantly without waiting for page reloads.
*   **📧 Built-in SMTP Delivery:** Never miss a lead to the spam folder. Our native SMTP integration ensures your notification emails and auto-responders land directly in the inbox.
*   **🛡️ Complete GDPR Compliance:** Stay legally protected globally. Utilize built-in GDPR fields, mandatory consent checkboxes, and seamless privacy policy integrations.
*   **🔀 Smart Conditional Logic:** Increase conversion rates by dynamically showing or hiding specific fields based on user input, creating a personalized experience.
*   **📑 Multi-Step Forms:** Break intimidating, long forms into engaging, bite-sized steps complete with progress bars and customizable navigation buttons.
*   **📚 Professionally Designed Templates:** Skip the setup phase. Choose from a rich Template Library to launch contact forms, surveys, and lead magnets in seconds.
*   **🛡️ Invisible Anti-Spam:** Keep your inbox clean with native support for **Google reCAPTCHA v2, reCAPTCHA v3, hCaptcha**, and our built-in invisible Honeypot.
*   **📱 100% Mobile Responsive Grid:** Build complex, multi-column layouts using a native grid system that looks flawless on any desktop, tablet, or smartphone.

---

## ⚙️ Advanced Design & Form Controls

Take absolute control over your form’s functionality and aesthetics directly within the WordPress dashboard:

### 1. Codeless Global Styling
*   **1-Click Presets:** Instantly apply 8 premium styles: Default, Modern Outlined, Elegant, Classic Underline, Neon Glow, Dark Neon, and Glassmorphism (Light & Dark).
*   **Deep Visual Customization:** Customize container backgrounds, floating labels, input focus borders, button hover states, and Google Fonts without touching CSS.
*   **Advanced Developer Tools:** Inject CSS variables and custom CSS directly into color controls for pixel-perfect brand matching.

### 2. Intelligent Form Actions
*   **Submission Routing:** Set up instant URL redirects, send targeted SMTP emails, or trigger dynamic success/error messages upon submission.
*   **Flexible Feedback UI:** Display success or error messages exactly how you want—inline after the form, replacing the form entirely, or inside a clean modal popup.
*   **Dynamic Data Tags:** Map user-submitted fields natively into your email notifications and confirmation messages.

### 3. High-Performance Form Fields
Construct highly optimized forms using a comprehensive suite of fields:

*   **Input Masking (New!):** Enforce strict data formatting for zip codes, phone numbers, and custom ID formats to ensure pristine data collection.
*   **Phone Field:** Features country code.
*   **Advanced Date & Time:** Powered by Flatpickr for flawless calendar UI, Captcha.
*   **Standard Fields:** Text, Email, WYSIWYG, Number, Dropdowns, Checkboxes, Radio Buttons, File Uploads, Range Sliders, GDPR, Hidden Fields, Custom HTML, and more.

## 👉 Check out our other plugin:

* **[Flipbox Addon for Elementor](https://wordpress.org/plugins/ultimate-flipbox-addon-for-elementor/)** — Stunning 3D flip boxes for your site.
* **[Click To Chat](https://wordpress.org/plugins/dragwyb-click-to-chat/)** — AI chatbot & floating social chat widgets.

== Installation ==

### Method 1: Via WordPress Admin (Recommended)
1. Go to **Plugins** → **Add New** in your WordPress dashboard.
2. Search for `Dragwyb Forms`.
3. Click **Install Now**, then **Activate**. 
4. You will be redirected to our interactive Onboarding Dashboard to configure your first form in seconds.

### Method 2: Manual Upload
1. Download the plugin ZIP file from the WordPress repository.
2. Navigate to **Plugins** → **Add New** → **Upload Plugin**.
3. Select the ZIP file, install, and click **Activate**.

---

== External Services ==

This plugin utilizes the following libraries:

*   **Pickr:** A high-performance color picker library. Used for design customization features.
    *   **Source:** [https://github.com/Simonwep/pickr](https://github.com/Simonwep/pickr)
    *   **Local Paths:** `assets/lib/pickr/css/index.css`, `assets/lib/pickr/js/index.js`
*   **Flatpickr:** Lightweight and powerful datetime picker.
    *   **Source:** [https://github.com/flatpickr/flatpickr](https://github.com/flatpickr/flatpickr)
    *   **Local Paths:** `assets/lib/flatpickr/js/flatpickr.min.js`,`assets/lib/flatpickr/css/flatpickr.min.css`
*   **Font Awesome (Free):** Used for providing iconography within the editor and frontend forms.
    *   **Source/License:** [https://fontawesome.com/license/free](https://fontawesome.com/license/free)
    *   **Local Path:** `assets/font-awesome/v5/all.min.css`
*   **Chart.js:** A simple yet flexible JavaScript charting library for creating data visualizations.
    *   **Source:** [https://www.chartjs.org/](https://www.chartjs.org/)
    *   **Local Paths:** `assets/lib/chartjs/chart.umd.min.js`
* **Dragwyb Feedback API:** Used to submit optional user feedback and diagnostic reports directly to the developers from the WordPress admin dashboard (https://feedback.dragwyb.com). Data is only sent when the site administrator explicitly submits the feedback form.
    *  **Privacy Policy:** https://dragwyb.com/privacy-policy/

---

== Frequently Asked Questions ==

#### Is this WordPress contact form plugin completely free?
Yes! The core features—including the visual editor, unlimited forms, and unlimited submissions—are 100% free.

#### Can I create multi-step forms?
Absolutely. You can easily break long, complex forms into highly engaging multi-step forms. Just drag and drop the "Step" field to add progress bars and custom navigation buttons, which is proven to boost conversion rates.

#### Can I show or hide fields based on user answers?
Yes! Our smart conditional logic allows you to dynamically show or hide specific conditional fields based on a user's previous inputs, creating a clean, clutter-free, and personalized form experience.

#### Do I need a separate plugin to send emails?
No! Dragwyb Forms includes built-in SMTP features. You can configure reliable email routing directly within our settings, eliminating the need for bulky third-party SMTP plugins.

#### How do I track where my leads are coming from?
Our built-in Analytics and UTM Tracking natively captures user device details and UTM campaign parameters, saving them directly to the user's entry in your dashboard.

#### Can I create GDPR-compliant forms?
Absolutely. We include a dedicated GDPR field and global Privacy Policy settings to ensure you meet all consent requirements.

#### Will this plugin slow down my website?
No. Performance is our top priority. By generating per-form cached CSS, offering global script toggles, optimizing database storage via custom tables, and utilizing AJAX via the WP REST API, your PageSpeed scores remain untouched.

#### Can I export my form submission data?
Yes. From the robust Entries Dashboard, you can filter, view, bulk-delete, and export your form entries and error logs for external CRM analysis.

---

== Screenshots ==

1. Dashboard: A comprehensive overview featuring unread entries, total submissions, and quick analytics.
2. Form Management: Manage your workflow—easily edit, delete, or view entries count for each of your forms in one place.
3. Template Library: Browse and select from a variety of ready-to-use form templates directly within the editor.
4. Drag & Drop Editor: Intuitively build forms with a drag-and-drop interface and real-time live preview.
5. Advanced Analytics: Gain deep insights with live tracking, complete visitor journeys, and detailed UTM data.
6. Entry Management: View, filter, and manage user submissions alongside individual user tracking data.
7. Plugin Settings: Customize your experience with advanced settings for APIs, SMTP, GDPR compliance, and performance optimization.

---

== Changelog ==

= 1.3.1 =
* Fixed: Form not created with the business template during onboarding setup.
* Fixed: Radio field validation error message issue.
* Fixed: Export form dropdown modal closing issue.
* Improved: Editor UI to show field settings in the right sidebar.
* Improved: Editor field drag & drop experience.
* Tweak: Update plugin name to Dragwyb Forms.
* Tweak: Update incorrect form demos link.
* Tweak: Mention external libraries in the readme.

= 1.3.0 =
* Added: "Input Mask" field to automatically format user input (like phone numbers or zip codes).
* Added: GDPR field and Privacy Policy settings to help keep your forms legally compliant.
* Added: Built-in SMTP email settings so your form notifications are delivered reliably to your inbox.
* Added: Analytics Dashboard to easily see how many views, submissions, and conversions your forms get.
* Added: A main dashboard page showing a quick overview of your stats and the most recent form entries.
* Added: A separate, detailed page just for viewing full analytics and filtering your data.
* Added: New choices for where to show Success and Error messages (below the form, replacing the form entirely, or in a popup).
* Added: Automatically save the user's device type and link tracking info (UTMs) when they submit a form.
* Added: A quick setup screen that opens automatically when you activate the plugin to help you get started.
* Added: Handy shortcut links to the dashboard and settings right from the main WordPress "Plugins" list.
* Fixed: Form submissions were not saving correctly if the form was created using a pre-built template.
* Fixed: Form builder editor issues to make dragging and editing much smoother.
* Fixed: A bug where a form's status wouldn't update to "Published" after saving it for the first time.
* Fixed: The search bar wasn't working on the form list page.
* Tweak: Cleaner, easier-to-read design for the Template Library, Settings, and Form List pages.
* Tweak: Updated the default look of submit buttons so they look great right out of the box.
* Tweak: Removed the manual "Clean Cache" button; the plugin now does this automatically in the background when you delete a form.
* Tweak: Hide distracting admin notices on the plugin's menu pages to keep plugin submenu pages clean.
* Tweak: Renamed plugin name to Dragwyb Forms – Contact Forms, Conditional Form, MultiStep Form, Analytics, SMTP.
* Tested Up To: WordPress 7.1.

= 1.2.1 =
* Tweak: Update plugin URL in plugin header.

= 1.2.0 =
* Added: History Tab with revert changes, redo, and undo settings (supports Ctrl+Z & Ctrl+Y shortcuts).
* Added: Template library with 12 pre-built form templates.
* Added: Preset Style settings in the Style toolbar with 8 visual options (Default, Modern/Outlined Label, Elegant/Inside Label, Classic/Underline, Bold Neon Glow, Dark Neon, Morphism Light, Morphism Dark).
* Added: Country code feature & option in the phone field.
* Added: Flatpickr integration in the Date & Time field for improved styling and customization.
* Added: CSS variable & Custom CSS options in the color control to load dynamic styles.
* Added: Radio field style options directly within field settings.
* Added: Radio Field Style settings globally in the toolbar style section.
* Added: Default value option in select, radio and checkbox field.
* Added: Background color setting in html field.
* Fixed: HTML field rendering issues by migrating the textarea control to a WYSIWYG control.
* Fixed: Step & Html field style issue with label left style.
* Fixed: White space issue with conditional hidden field.
* Fixed: Resolved minor PHP warnings and errors.
* Fixed: Trash and draft form display issue now only display publish forms.
* Tweak: Form status is now set to publish on initial load.
* Tweak: Improved the editor's top header layout and UI.

= 1.1.1 =
* Added: Entry and error logs export feature.
* Added: Bulk delete action button in entries and error logs.
* Added: Form editor quick-access button directly in the form preview admin bar.
* Tweak: Improved step field preview accuracy in the visual editor.

= 1.1.0 =
* Added: Conditional logic field settings.
* Added: Dynamic tags option natively in controls.
* Added: Step field element for multi-step forms.
* Added: Granular style settings for step indicators.
* Added: Progress bar styling controls.
* Added: Button style settings specifically for previous and next buttons.
* Tweak: Optimized frontend asset loading.
* Tweak: Improved dropdown control styling and layout.

= 1.0.6 =
* Fixed: Form Editor styling issue where dashboard menus were overlapping/showing incorrectly.
* Fixed: Feedback notice functionality not firing correctly.

= 1.0.5 =
* Added: Expanded compatibility for older PHP versions.
* Fixed: Form editor preview issue where Captcha field HTML was rendering incorrectly.
* Tweak: Improved plugin short description for clarity.
* Tweak: Updated form menu label for better UX.

= 1.0.4 =
* Public release.

= 1.0.3 =
* Updated distribution files to production optimized builds.
* Documented external asset libraries in readme.txt.
* Appended source code repositories to the codebase notes.

= 1.0.2 =
* Hardened internal sanitization, validation, and security check processes.

= 1.0.1 =
* Refactored internal plugin naming conventions and plugin slug.

= 1.0.0 =
* Initial core release.

== Upgrade Notice ==

= 1.3.0 =
*Important:* Version 1.3.0 is a major update adding an Analytics Dashboard, GDPR settings, built-in SMTP, and an Input Mask field.

== Admin Notice ==

= 1.3.0 =
*Important:* This plugin releases a major update featuring a new Analytics Dashboard, built-in SMTP, GDPR compliance settings.