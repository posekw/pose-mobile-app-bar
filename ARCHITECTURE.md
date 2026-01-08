# Plugin Architecture

## Overview
The **Pose Mobile App Bar** plugin adds a mobile-optimized bottom navigation bar to WordPress sites. It is designed to emulate a native app experience using a glassmorphism design language.

## Directory Structure
```
pose-mobile-app-bar/
├── assets/                  # Static assets
│   └── css/
│       ├── admin.css        # Styles for the WP Admin settings page
│       └── frontend.css     # Styles for the frontend app bar
├── includes/                # PHP Logic Classes
│   ├── class-pmab-activator.php  # Handles plugin activation (defaults)
│   ├── class-pmab-settings.php   # Admin menu and settings page logic
│   └── class-pmab-display.php    # Frontend rendering and CSS injection
├── pose-mobile-app-bar.php  # Main entry point (Loader)
├── .gitignore
└── README.md
```

## Class Responsibilities

### `Pmab_Activator`
- **Role**: Initialize default options only when the plugin is activated.
- **Key Methods**: `activate()`

### `Pmab_Settings`
- **Role**: Register WordPress settings and render the admin UI.
- **Key Methods**: 
    - `register_settings()`: Defines the `pmab_settings_group` options options.
    - `render_settings_page()`: HTML for the admin page.

### `Pmab_Display`
- **Role**: Render the bar on the frontend and inject dynamic CSS variables.
- **Key Methods**:
    - `enqueue_styles()`: Loads `dashicons` and `frontend.css`. Injects separate CSS variables for user customization (colors, blur, opacity).
    - `render_bar()`: Outputs the HTML structure of the bar if the user is on a mobile device.

## Design System
- **CSS Variables**: The plugin relies on CSS variables (e.g., `--pmab-bg-rgba`, `--pmab-blur`) to allow PHP to control the look while keeping the CSS file static.
- **Glassmorphism**: Achieved via `backdrop-filter: blur()`.

## Native Mode
When "Native Mode" is enabled:
- The plugin injects CSS to hide the theme's default Header and Footer on mobile devices.
- Selectors to hide are configurable in the Admin settings.
