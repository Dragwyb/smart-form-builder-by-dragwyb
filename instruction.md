Smart Form Builder by Dragwyb Plugin Development Roadmap
This document outlines the steps and folder structure to develop the Smart Form Builder by Dragwyb Plugin. The plugin allows users to create quizzes, polls, surveys, and contact forms using a custom post type (CPT). It also features drag-and-drop functionality for adding fields to the form and provides live preview functionality via an iframe.

Folder Structure
Below is the folder structure for the Smart Form Builder by Dragwyb Plugin:

python
Copy
dragwyb-form-builder/
│
├── assets/
│ ├── css/
│ │ └── dragwyb-form-builder.css # Plugin's CSS styles
│ ├── js/
│ │ ├── dragwyb-form-builder.js # JavaScript for form behavior
│ │ └── dragwyb-form-builder-dragdrop.js # JavaScript for drag-and-drop functionality
│ ├── images/ # Icons/images for form elements
│
├── includes/
│ ├── class-dragwyb-form-builder-admin.php # Admin panel functionality
│ ├── class-dragwyb-form-builder-frontend.php # Frontend form display
│ ├── class-dragwyb-form-builder-post-type.php # Custom post type registration
│ ├── class-dragwyb-form-builder-ajax.php # AJAX functionality for form submissions
│ ├── class-dragwyb-form-builder-base-field.php # Base class for registering fields, widgets, tabs, and controls
│
├── templates/
│ ├── dragwyb-admin-form-editor.php # Admin panel for form creation (drag-and-drop editor)
│ ├── dragwyb-frontend-form-display.php # Template for rendering the form on frontend
│ └── dragwyb-iframe-preview.php # Live iframe preview of form during editing
│
├── dragwyb-form-builder.php # Main plugin file
├── readme.md # Plugin documentation
├── uninstall.php # Clean up on plugin uninstall
└── autoload.php # Autoloader for dynamic class loading
Development Instructions

1. Plugin Initialization (dragwyb-form-builder.php)
   Purpose: The main file for initializing the plugin, registering the custom post type (CPT) for forms, and including other necessary files.

Tasks:

Register the custom post type for form creation (quiz, poll, survey, contact form).
Include required files for functionality in the admin panel and frontend.
Register plugin actions and shortcodes for frontend integration. 2. Custom Post Type (CPT) for Forms (class-dragwyb-form-builder-post-type.php)
Purpose: Register a custom post type (CPT) to handle form data. Each form will be a post in the CPT.

Tasks:

Define post type labels and arguments.
Allow forms to be saved as posts with editable metadata (e.g., form title, description, and structure). 3. Admin Panel for Form Creation (class-dragwyb-form-builder-admin.php)
Purpose: Create a custom editor in the WordPress admin panel for building forms using drag-and-drop functionality.

Tasks:

Develop a user interface for dragging and dropping form fields like text fields, checkboxes, radio buttons, etc.
Allow users to configure field properties like labels, required status, and default values.
Provide a live preview of the form inside an iframe.
File: dragwyb-admin-form-editor.php

Create the editor interface using HTML, CSS, and JavaScript for drag-and-drop functionality.
Use libraries such as jQuery UI or SortableJS for implementing the drag-and-drop feature.
Update the live preview of the form as the user interacts with the editor. 4. Frontend Display of Forms (class-dragwyb-form-builder-frontend.php)
Purpose: Display forms created in the admin panel on the frontend of the website via a shortcode.

Tasks:

Implement a shortcode (e.g., [dragwyb_form_builder id="123"]) to embed forms in pages or posts.
Dynamically render form fields based on saved form data from the custom post type.
Handle form validation and submission on the frontend.
File: dragwyb-frontend-form-display.php

Render the form fields based on the stored form data.
Add form validation and submission functionality. 5. AJAX Handling for Form Submissions (class-dragwyb-form-builder-ajax.php)
Purpose: Handle form submissions asynchronously using AJAX.

Tasks:

Capture form submissions via AJAX to prevent page reloads.
Store form data in the database or send it via email.
Return a success or failure message after form submission. 6. Live Form Preview in Admin Panel
Purpose: Allow users to see a live preview of their form as they create it.

Tasks:

Create an iframe that dynamically updates to show the form in real-time.
Ensure that changes made in the form builder interface are reflected in the preview iframe.
File: dragwyb-iframe-preview.php

Create an iframe within the admin panel that renders the form with the changes in real-time. 7. Styling and JavaScript
CSS (dragwyb-form-builder.css): Define styles for both the form builder interface and the frontend form display. Ensure that the form builder is responsive and user-friendly.

JavaScript:

dragwyb-form-builder.js: Handle dynamic form behavior such as field validation, form submission, and interactivity.
dragwyb-form-builder-dragdrop.js: Implement drag-and-drop functionality for adding and reordering form fields.
Libraries to Consider:

Use jQuery UI or SortableJS for drag-and-drop functionality.
Ensure smooth UX/UI for the form builder. 8. Shortcode Support
Purpose: Allow users to insert forms into pages/posts using a WordPress shortcode.

Tasks:

Develop the [dragwyb_form_builder id="123"] shortcode to display forms created with the plugin.
Ensure the shortcode renders the correct form with all the necessary fields and functionality. 9. Email Notifications (Optional)
Purpose: Send email notifications upon form submission (especially useful for contact forms or survey results).

Tasks:

Send a confirmation email to the admin or user upon form submission.
Format the email with the form’s submitted data (e.g., name, email, message, etc.). 10. Uninstall Script (uninstall.php)
Purpose: Clean up any plugin-related data when the plugin is uninstalled.

Tasks:

Delete custom post types, form data, and any other plugin-related content from the database.
Optionally, remove plugin settings or options saved in the WordPress options table. 11. Autoloading PHP Classes (autoload.php)
Purpose: Dynamically load PHP class files based on class names and namespaces, eliminating the need for manual require or include statements.

Tasks:

Create an autoload.php file that implements an autoloader for loading all PHP files based on class names and namespaces.
Use PHP's spl_autoload_register function to automatically load class files from the includes/ folder.
Base Class for Registering Widgets and Fields (class-dragwyb-form-builder-base-field.php)
Purpose: A base class to handle the registration of form widgets, input fields, tabs, and controls for the drag-and-drop editor.

Tasks:

Implement a base class for registering widgets and input fields.
The class should define common methods for adding tabs, controls, and form fields (text, radio, checkbox, etc.) to the editor interface.
This will ensure consistency in the form-building process and make adding new fields or controls easier in the future.
Base Field Class Structure:

Class methods will be used to register, configure, and render the input fields (text fields, radio buttons, checkboxes, etc.).
Tabs: The base class should allow developers to define tabs that group similar field types together in the drag-and-drop editor.
Controls: Each form element will have its own set of controls for customizations like labels, default values, required field status, etc.

## Form Field Types for Initial Release

Basic Fields:
Text Field: A simple input field for short responses.
Textarea: For longer, multi-line text inputs.
Email: For email input with basic validation.
Number: Numeric input field with optional min/max value constraints.
Choice Fields:
Radio Buttons: Allow selection of one option from a predefined set of options.
Checkboxes: Allow users to select multiple options from a list.
Dropdown (Select Box): For selecting a single option from a dropdown list.
Advanced Fields:
File Upload: Allow users to upload files (e.g., images, documents).
Date Picker: A calendar-based field for selecting dates.
Quiz-Specific Fields:
Multiple Choice with Correct Answer: For quizzes, allow users to define correct answers for multiple-choice questions.
Other Considerations:

Conditional logic: Displaying or hiding fields based on user selection or input.
Default values: Pre-setting certain values for the fields.

## Security Considerations

Standard WordPress Security: Always follow WordPress security practices, including:
Nonce Verification: Implement nonces for form submissions to prevent CSRF (Cross-Site Request Forgery).
Input Sanitization & Validation: Sanitize and validate all user inputs (e.g., email, number) to prevent XSS (Cross-Site Scripting) and other vulnerabilities.
Escaping Output: Properly escape output on the frontend and admin panels.

## File Upload Restrictions:

Limit file upload size and restrict file types to specific extensions (e.g., .jpg, .png, .pdf) to avoid security risks.
Check the file's MIME type and extension upon upload and use WordPress' built-in wp_handle_upload() function for proper file handling.

## Form Submission Handling

Successful Submissions:
Store in Database: Store submission data in the database as a custom post type or custom table as discussed above.
Email Notifications: Send email notifications upon submission to the site admin and/or the user who submitted the form.
Admin notification for form submissions.
User notification for successful submission (if applicable).
Customize email templates per form type (quiz, poll, survey, contact form).
Submission Limits:
Implement submission limits per user or IP (e.g., one submission per user or IP address per day) to prevent spam or abuse. You could track submissions in a custom table or use wp_count_posts().

## Database Structure for Storing Form Data

Form Structure Storage:
Form Structure/Field Data: Store the field structure (i.e., the form layout, field types, options, etc.) as JSON in the post meta of the form CPT. This ensures easy storage and retrieval while keeping things scalable.
Use wp_postmeta to store the JSON structure, which will include field types, options, labels, and validations.
Form Submissions:
Submission Data: For storing form submissions, postmeta should be sufficient. Each form submission could be stored as a wp_post in a custom post type (e.g., dragwyb_form_submission), with the individual responses stored as post metadata.
Alternatively, for more complex form submissions or scalability, you could create a custom table using dbDelta() to store submission data. This is ideal if you expect large-scale form submissions or need advanced query functionality.
Structure:
Form Fields: Store as a serialized or JSON array within the post meta (depending on the complexity of the form).
Submissions: Either as a custom post type (CPT) or as custom database tables for better querying capabilities.

## Preview Functionality in Admin Panel

Live Preview: The form builder should offer a live preview inside the WordPress admin panel. It should update in real-time as users add and configure fields.
Device Views:
Responsive Preview: Ensure that the live preview supports various device views (desktop, tablet, mobile). This will help users see how their form appears across different devices.
WordPress Theme Compatibility:
Preview in Themes: The live preview should ideally use a generic WordPress theme structure or allow users to choose a theme to see how the form will look on the frontend. However, since the form will be rendered using a shortcode, it will generally follow the theme’s styles unless custom CSS is added to override them.
