# Live Editor for WPBakery

**Version:** 1.09
**Author:** DesignStack
**Requires:** WordPress 5.0+, WPBakery Page Builder
**License:** GPL v2 or later

## Description

Live Editor for WPBakery enhances the WPBakery Page Builder plugin by adding a live frontend editor interface. This plugin provides real-time visual editing capabilities similar to the Impreza theme's us-builder functionality, but as a standalone plugin that works with any WordPress theme.

## Features

- **Live Frontend Editing**: Edit your WPBakery pages with a real-time preview
- **Responsive Preview Modes**: View your pages in desktop, tablet, and mobile viewports
- **Intuitive Interface**: Clean and modern admin interface with panel and preview areas
- **Element Highlighting**: Hover over elements to see controls for copy, paste, duplicate, and delete
- **AJAX-Based Saving**: Save your changes without page reloads
- **Post Locking Support**: Prevents multiple users from editing the same post simultaneously
- **Admin Bar Integration**: Quick access to Live Editor from the WordPress admin bar
- **Standalone Plugin**: Works with any WordPress theme that uses WPBakery

## Installation

1. Upload the `live-editor-for-wpbakery` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure WPBakery Page Builder is installed and activated
4. Navigate to any post or page and click "Edit Live" to start using the live editor

## Usage

### Accessing the Live Editor

**From Posts/Pages List:**
- Hover over any post or page
- Click the "Edit Live" link that appears

**From Post Editor:**
- Look for the "Edit Live" button above the content area
- Click to open the live editor

**From Frontend (when logged in):**
- Navigate to any page on your site
- Click "Edit Live" in the WordPress admin bar

### Using the Live Editor

The Live Editor interface consists of two main areas:

**Left Panel:**
- Add Elements: Browse and add WPBakery elements to your page
- Navigator: View and navigate your page structure
- Settings: Configure page-specific settings
- Save Changes: Save your edits to the database

**Preview Area:**
- Responsive Mode Switcher: Toggle between desktop, tablet, and mobile views
- Live Preview: See your changes in real-time
- Element Highlighting: Hover over elements to see editing controls

### Editing Elements

1. Hover over any element in the preview to highlight it
2. Use the hover panel controls:
   - **Copy**: Copy the element to clipboard
   - **Paste**: Paste previously copied element
   - **Duplicate**: Create a copy of the element
   - **Delete**: Remove the element from the page

## Technical Details

### Plugin Structure

```
live-editor-for-wpbakery/
├── assets/
│   ├── css/
│   │   ├── builder.min.css
│   │   └── builder-preview.min.css
│   └── js/
│       ├── builder.min.js
│       └── builder-preview.min.js
├── includes/
│   ├── class-lew-ajax.php
│   ├── class-lew-assets.php
│   ├── class-lew-builder.php
│   ├── class-lew-panel.php
│   ├── class-lew-preview.php
│   ├── class-lew-shortcode.php
│   └── helpers.php
├── templates/
│   └── main.php
├── live-editor-for-wpbakery.php
└── README.md
```

### Key Components

**LEW_Builder**: Main builder class that handles initialization and admin actions
**LEW_Ajax**: Manages AJAX requests for rendering shortcodes and saving posts
**LEW_Preview**: Sets up the frontend preview mode
**LEW_Shortcode**: Handles shortcode management and rendering
**LEW_Panel**: Manages the builder panel UI
**LEW_Assets**: Handles asset management and loading

### Action Hooks

The plugin registers the following WordPress action:
- `admin_action_lew-builder` - Triggers the live builder interface

### AJAX Actions

- `lew_render_shortcode` - Renders a shortcode in real-time
- `lew_save_post` - Saves post content and custom CSS

### URL Structure

Live Editor URLs follow this pattern:
```
/wp-admin/post.php?post=123&action=lew-builder
```

Where `123` is the post ID you want to edit.

## Developer Notes

### Extending the Plugin

The plugin provides several filters for customization:

```php
// Modify allowed post types
add_filter('lew_allowed_edit_post_types', function($post_types) {
    $post_types[] = 'custom_post_type';
    return $post_types;
});

// Modify responsive breakpoints
add_filter('lew_responsive_breakpoints', function($breakpoints) {
    $breakpoints['custom'] = array(
        'label' => 'Custom',
        'max_width' => 1440,
        'media' => '@media (max-width: 1440px)',
    );
    return $breakpoints;
});

// Modify builder page detection
add_filter('lew_is_builder_page', function($is_builder_page) {
    // Your custom logic
    return $is_builder_page;
});
```

### Custom CSS Storage

Custom CSS is stored in two post meta fields:
- `lew_post_custom_css` - Live Editor custom CSS
- `_wpb_post_custom_css` - WPBakery custom CSS (for compatibility)

Both are updated simultaneously to ensure compatibility with WPBakery's native editor.

## Changelog

### 1.09 (CRITICAL FIX - Missing Admin Function)
- **COMPREHENSIVE FIX**: Fixed "Call to undefined function us_admin_print_styles()" in USBuilder.class.php:208
- **Root Cause**: Builder tried to call us_admin_print_styles() which wasn't defined in standalone plugin
- **Proactive Analysis**: Scanned ALL function calls in USBuilder.class.php to find missing functions:
  * Checked 14 us_ functions: us_admin_print_styles, us_arr_path, us_array_merge, us_config, us_get_img_placeholder, us_get_jsoncss_options, us_get_option, us_get_public_post_types, us_get_responsive_states, us_get_shortcode_full_name, us_get_shortcode_name, us_grid_available_post_types_for_import, us_load_template, us_translate
  * All functions existed EXCEPT us_admin_print_styles
- **Solution**: Added us_admin_print_styles() function to includes/us-helpers.php
  * Function outputs admin styles via action hook
  * Wrapped with function_exists() check for safety
  * Provides compatibility for builder's admin style requirements
- **COMPREHENSIVE**: Verified ALL other builder functions exist - no other missing functions found
- This prevents fatal error when builder loads and tries to output admin styles

### 1.08 (CRITICAL FIX - Missing Widget Files)
- **COMPREHENSIVE FIX**: Fixed ValueError "Path cannot be empty" in widgets.php:194
- **Root Cause**: Widget files were missing from plugin causing empty file paths
- **Solution 1**: Added proper error checking before including widget files
  * Check if filepath is not FALSE
  * Check if filepath is not empty
  * Check if file actually exists before including
- **Solution 2**: Copied complete widgets directory from us-core (7 widget files):
  * us_blog.php - Blog widget
  * us_contacts.php - Contacts widget
  * us_gallery.php - Gallery widget (the one causing the error)
  * us_login.php - Login widget
  * us_portfolio.php - Portfolio widget
  * us_socials.php - Social links widget
  * index.php - Directory protection
- **COMPREHENSIVE**: Fixed both the immediate error AND added all missing widget files to prevent future errors
- This prevents ValueError on widget initialization

### 1.07 (CRITICAL FIX - Duplicate File Loading)
- **COMPREHENSIVE FIX**: Removed duplicate file loading causing function redeclaration errors
- Fixed fatal error: "Cannot redeclare us_sort_by_weight()" in functions/helpers.php:316
- **Root Cause Analysis**: functions/helpers.php and includes/us-helpers.php are IDENTICAL files (both 5170 lines, same MD5 hash: 9dead5f28c0ebd90ee214acf30b89830)
- **Solution**: Removed loading of functions/helpers.php since includes/us-helpers.php is already loaded earlier
- **Verification**: Checked builder/helpers.php vs includes/builder-helpers.php - also identical but only loaded once (no issue)
- Added comprehensive comments explaining why certain files are NOT loaded to prevent future confusion
- **CRITICAL**: This eliminates ALL function redeclaration errors from duplicate helper file loading
- **COMPREHENSIVE**: This fix prevents the reactive pattern - identified and fixed the systemic issue

### 1.06 (CRITICAL FIX)
- **EMERGENCY FIX**: Added missing US_THEMEVERSION constant
- Fixed fatal error: "Undefined constant US_THEMEVERSION" in migration.php:119
- Migration system requires US_THEMEVERSION to determine database version
- Added constant definition: `define( 'US_THEMEVERSION', LEW_VERSION )`
- This allows the migration system to work properly in standalone plugin mode
- **CRITICAL**: This fix allows the plugin to fully activate without crashing

### 1.05 (CRITICAL FIX)
- **EMERGENCY FIX**: Added function_exists() checks to ALL functions in usof.php
- Fixed fatal error: "Cannot redeclare usof_get_option()" that was blocking site launch
- Protected 6 functions with function_exists() checks:
  * usof_get_option() - Get theme option values
  * usof_get_default() - Get default field values
  * usof_defaults() - Get all default values
  * usof_load_options_once() - Load options from database
  * usof_save_options() - Save options to database
  * usof_execute_show_if() - Check conditional display logic
- This is defensive programming to prevent ANY possibility of function redeclaration
- Even if usof.php is somehow loaded multiple times, functions won't redeclare
- **CRITICAL**: This fix ensures the plugin can activate and run without fatal errors

### 1.04
- Fixed fatal error: "Cannot redeclare usof_get_option()" function redeclaration error
- Changed `require` to `require_once` in theme-options.php for usof.php loading
- Removed init.php from loading sequence to prevent duplicate file includes
- init.php was trying to load all function files that were already loaded in main plugin file
- This fixes all function redeclaration errors caused by duplicate requires

### 1.03
- Added all remaining function files from us-core for complete compatibility
- Fixed fatal error: us_get_nav_menus() undefined in additional_menu.php
- Added header.php (navigation menu functions)
- Added widget_areas.php, widgets.php (widget management)
- Added theme-options.php (theme options support)
- Added breadcrumbs.php (breadcrumb functions)
- Added cookie-notice.php (cookie notice functions)
- Added enqueue.php (asset loading functions)
- Added meta-tags.php (meta tag functions)
- Added post-types.php (custom post type functions)
- Added migration.php (version migration functions)
- Added init.php (initialization functions)
- Added helpers.php (utility functions)

### 1.02
- Fixed fatal error: us_grid_available_post_types() undefined
- Added grid.php (grid element functions)
- Added list.php (list element functions)
- Added media.php (media handling functions)
- Added post.php (post element functions)
- Added layout.php (layout functions)

### 1.01
- Complete rewrite with full us-core integration
- Copied entire builder system (482 files)
- Added USOF framework for form fields
- Added all element configurations
- Added all templates and assets
- Full compatibility with Impreza's us-builder interface

### 1.0 (Initial Release)
- Live frontend editing interface
- Responsive preview modes (desktop, tablet, mobile)
- Element highlighting and hover controls
- AJAX-based saving
- Post locking support
- Admin bar integration
- Standalone plugin architecture

## Credits

This plugin is inspired by and adapted from the us-builder functionality found in the Impreza theme by UpSolution. The core concept and architecture have been extracted and reimagined as a standalone plugin to work with any WordPress theme that uses WPBakery Page Builder.

## Support

For support, feature requests, or bug reports, please visit:
https://designstack.co.uk

## License

This plugin is licensed under the GPL v2 or later.
```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```
