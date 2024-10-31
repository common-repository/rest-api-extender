=== REST API Extender ===
Contributors: stealthcode
Tags: REST API, permalink, options, theme, appearance
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 2.2
License: GPLv3 or later

The REST API Extender is a WordPress plugin that extends the functionality of the WordPress REST API. 

== Description ==

The REST API Extender is a WordPress plugin that extends the functionality of the WordPress REST API.

It provides two main features:

1. **Permalink Options Management**
  This plugin allows you to update the permalink settings of your WordPress site via the REST API. You can change the permalink structure, category base, and tag base using a simple POST request.

To update the permalink settings, send a POST request to the following endpoint:
/wp-json/raext/permalink-options/v1/settings

The request body should include the following parameters:

- `permalink_structure` (string): The new permalink structure.
- `category_base` (string, optional): The new category base.
- `tag_base` (string, optional): The new tag base.

Example request:
POST /wp-json/raext/permalink-options/v1/settings
Content-Type: application/json

{
"permalink_structure": "/%year%/%postname%/",
"category_base": "categories",
"tag_base": "tags"
}

2. **Theme Installation and Activation**
  The plugin also enables you to install and activate themes from a remote URL using the REST API. You have to provide the theme URL, stylesheet, and slug, and the plugin will handle the installation and activation process.

To install and activate a theme from a remote URL, send a POST request to the following endpoint:
/wp-json/raext/theme-manager/v1/install

The request body should include the following parameters:

- `theme_url` (string): The URL of the theme ZIP file.
- `theme_stylesheet` (string): The stylesheet of the theme (e.g., `twentytwenty`).
- `theme_slug` (string): The slug of the theme (e.g., `twentytwenty`).

Example request:
POST /wp-json/raext/theme-manager/v1/install
Content-Type: application/json

{
"theme_url": "https://example.com/themes/twentytwenty.zip",
"theme_stylesheet": "twentytwenty",
"theme_slug": "twentytwenty"
}

Developed by the creators of <a href="https://seoneo.io/">SEO Neo</a>

== Installation ==

1. Upload the `rest-api-extender` directory to the `/wp-content/plugins/` directory on your WordPress site.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Permissions  ==

Both the permalink options management and theme installation/activation features require the user to have the `manage_options` capability (an administrator role).
