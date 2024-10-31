<?php
/**
 * REST API extender
 *
 * @wordpress-plugin
 * Plugin Name:          REST API extender
 * Plugin URI:           https://seoneo.io/
 * Description:          Extends the WP REST API to allow the changing of permalink options, as well as the installation and switching of themes
 * Version: 			 2.2
 * Author:               SEO Neo
 * Author URI:           https://seoneo.io/
 * License:              GPL-3.0-or-later
 * License URI:          https://www.gnu.org/licenses/gpl-3.0.html
 */
 
/**
 * Register the custom REST API route.
 */

if (!defined('ABSPATH')) exit;

add_action('rest_api_init', function () {
    register_rest_route('raext/theme-manager/v1', '/install', array(
        'methods' => 'POST',
        'callback' => 'raext_custom_theme_install',
        'permission_callback' => 'raext_permissions_check', 
    ));
	register_rest_route( 'raext/permalink-options/v1', '/settings', array(
        'methods'  => 'POST',
        'callback' => 'raext_permalink_options_api_update_settings',
        'permission_callback' => 'raext_permissions_check',
    ) );
});

/**
 * Update the permalink settings.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response
 */
function raext_permalink_options_api_update_settings( $request ) {
    $params = $request->get_params();

    if ( isset( $params['permalink_structure'] ) ) {
        $permalink_structure = $params['permalink_structure'];
        update_option( 'permalink_structure', $permalink_structure );
    }

    if ( isset( $params['category_base'] ) ) {
        $category_base = $params['category_base'];
        update_option( 'category_base', $category_base );
    }

    if ( isset( $params['tag_base'] ) ) {
        $tag_base = $params['tag_base'];
        update_option( 'tag_base', $tag_base );
    }

    flush_rewrite_rules();

    wp_send_json_success('Permalink settings updated.');
}

/**
 * Install/Activate a theme from a remote URL.
 *
 * @param WP_REST_Request $request The REST API request.
 * @return WP_REST_Response The response data.
 */
function raext_custom_theme_install($request)
{
    $theme_url = $request->get_param('theme_url');
    $theme_stylesheet = $request->get_param('theme_stylesheet');
    $theme_slug = $request->get_param('theme_slug');
    $response = wp_remote_get($theme_url);

    if (!is_wp_error($response) && $response['response']['code'] === 200) {
        $theme_data = wp_remote_retrieve_body($response);

		global $wp_filesystem;
        // If the function is not available, require it.
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        // Initialize the WP_Filesystem
        WP_Filesystem();

        // Check if WP_Filesystem initialization was successful
        if (WP_Filesystem()) {
			 error_log("Filesystem passed");

            // Specify the directory where the theme should be extracted
            $theme_dir = get_theme_root() . '/' . $theme_slug;

            if (wp_mkdir_p($theme_dir)) {
                $zip_path = $theme_dir . '/' . $theme_slug . 'zip';
                // Use WP_Filesystem method to write the theme data to a file
				$successDataTransfer = $wp_filesystem->put_contents( $zip_path, $theme_data, FS_CHMOD_FILE );


                if ( $successDataTransfer ) {
                    $zip = new ZipArchive();
                    if ($zip->open($zip_path) === true) {
                        $zip->extractTo($theme_dir);
                        $zip->close();

                        switch_theme($theme_stylesheet);
                        wp_send_json_success('Theme installed successfully.');
                    } else {
                        wp_send_json_error('Error extracting the theme zip file.');
                    }

                    wp_delete_file($zip_path); // Remove the zip file after extraction
                } else {
                    wp_send_json_error('Error writing theme data to file.');
                }
            } else {
                wp_send_json_error('Error creating the theme directory.');
            }
        } else {
            wp_send_json_error('Error initializing WP_Filesystem.');
        }
    } else {
        wp_send_json_error('Error fetching the theme.');
    }
}


/**
 * Check if the current user has the necessary permissions (administrator).
 *
 * @return bool
 */
function raext_permissions_check() {
    return current_user_can( 'manage_options' );
}