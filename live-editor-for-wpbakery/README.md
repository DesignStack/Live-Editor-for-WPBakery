# Live Editor for WPBakery

**Version:** 1.0
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
