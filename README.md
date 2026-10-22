# Recipe Cook Mode

A WordPress plugin that adds a Cook Mode toggle to recipes. When a reader switches it on, their screen stays awake until they switch it off or leave the page, so it doesn't go dark while they cook.

![Tested up to WordPress 7.1](https://img.shields.io/badge/WordPress-7.1-blue)
![PHP 7.4 to 8.5](https://img.shields.io/badge/PHP-7.4%E2%80%938.5-777bb4)
![License GPLv2 or later](https://img.shields.io/badge/license-GPLv2%2B-green)

## Features

- **WP Recipe Maker:** one checkbox adds the toggle to every recipe card, at the top or bottom. No template editing needed.
- **Any post type or category:** automatically add the toggle to posts, pages, or custom post types, optionally limited to chosen categories. Works with recipes from any plugin or written straight into the post.
- **Block:** a Cook Mode block for the block editor, with per-block label and description.
- **Shortcode:** `[recipe_cook_mode label="Keep screen on" description="Handy while kneading"]`.
- **Resumes automatically:** browsers release the wake lock when a page is hidden. Cook Mode turns back on when the reader returns.
- **Several recipes per page:** every toggle works and they stay in sync.
- **Accessible:** a labelled switch with keyboard support and screen reader status announcements.
- **Lightweight:** plain JavaScript, no jQuery. Assets load only on pages that show the toggle, and the toggle is hidden when printing.

If a post contains a WP Recipe Maker card, a Cook Mode block, or the shortcode, automatic placement skips it, so a post never shows two toggles.

## How it works

Cook Mode uses the browser's [Screen Wake Lock API](https://developer.mozilla.org/en-US/docs/Web/API/Screen_Wake_Lock_API), supported in current Chrome, Edge, Safari (16.4+), and Firefox. In browsers without support, the toggle is disabled and explains why. Nothing is sent to any server.

## Setup

1. Install and activate the plugin.
2. Go to **Settings → Recipe Cook Mode** to set the label, description, and accent color.
3. With WP Recipe Maker active, the toggle is added to recipe cards automatically. Choose its position.
4. For other recipes, choose post types and categories under **Automatic placement**, or use the block or shortcode.

## Project structure

```
recipe-cook-mode.php                          Plugin header and bootstrap
includes/class-recipe-cook-mode.php           Toggle markup, WP Recipe Maker hook, auto-insert, shortcode, block
includes/class-recipe-cook-mode-settings.php  Settings defaults and validation
includes/class-recipe-cook-mode-admin.php     Settings screen
assets/cook-mode.js                           Wake lock handling
assets/cook-mode.css                          Toggle styles
assets/block.js                               Block editor integration
uninstall.php                                 Removes the plugin's settings
readme.txt                                    WordPress.org readme
```

## Requirements

- WordPress 6.2 or later (tested up to 7.1)
- PHP 7.4 or later (tested up to 8.5)
- WP Recipe Maker is optional

## License

GPLv2 or later. See https://www.gnu.org/licenses/gpl-2.0.html.
