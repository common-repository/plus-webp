<?php
/**
 * Plus WebP or AVIF
 *
 * @package    Plus WebP or AVIF
 * @subpackage PlusWebpAdmin Management screen
	Copyright (c) 2019- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$pluswebpadmin = new PlusWebpAdmin();

/** ==================================================
 * Management screen
 */
class PlusWebpAdmin {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
		add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 10, 1 );
		add_action( 'rest_api_init', array( $this, 'register_rest' ) );
	}

	/** ==================================================
	 * Add a "Settings" link to the plugins page
	 *
	 * @param array  $links  links array.
	 * @param string $file  file.
	 * @return array $links  links array.
	 * @since 1.00
	 */
	public function settings_link( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = 'plus-webp/pluswebp.php';
		}
		if ( $file === $this_plugin ) {
			$links[] = '<a href="' . admin_url( 'upload.php?page=plus-webp' ) . '">' . esc_html__( 'Settings' ) . ' & ' . esc_html__( 'Bulk Generate', 'plus-webp' ) . '</a>';
		}
			return $links;
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function plugin_menu() {

		add_media_page(
			'Plus WebP or AVIF',
			'Plus WebP or AVIF',
			'manage_options',
			'plus-webp',
			array( $this, 'plus_webp_generate_setteings' )
		);
	}

	/** ==================================================
	 * Generate and Settings page
	 *
	 * @since 4.00
	 */
	public function plus_webp_generate_setteings() {

		echo '<div id="plus-webp-page"></div>';
	}

	/** ==================================================
	 * Load script
	 *
	 * @param string $hook_suffix  hook_suffix.
	 * @since 4.00
	 */
	public function admin_scripts( $hook_suffix ) {

		if ( 'media_page_plus-webp' !== $hook_suffix ) {
			return;
		}

		$asset_file = include plugin_dir_path( __DIR__ ) . 'guten/dist/plus-webp.asset.php';

		wp_enqueue_style(
			'plus-webp-style',
			plugin_dir_url( __DIR__ ) . 'guten/dist/plus-webp.css',
			array( 'wp-components' ),
			'1.0.0',
		);

		wp_enqueue_script(
			'plus-webp',
			plugin_dir_url( __DIR__ ) . 'guten/dist/plus-webp.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		$pluswebp_settings = get_option( 'pluswebp' );

		list( $post_ids, $no_file_ids ) = apply_filters( 'pluswebp_get_allimages', $pluswebp_settings['types'], $pluswebp_settings['output_mime'] );

		$non_generate_description = null;
		if ( ! empty( $no_file_ids ) ) {
			/* translators: Count of no file Media */
			$non_generate_description = sprintf( __( 'Found %1$d media(id: %2$s), exists in the database, but the file does not, so the conversion is not performed.', 'plus-webp' ), count( $no_file_ids ), implode( ',', $no_file_ids ) );
		}

		wp_localize_script(
			'plus-webp',
			'pluswebpgenerate_data',
			array(
				'bulk_label' => __( 'Bulk Generate', 'plus-webp' ),
				'description_label' => __( 'To perform "Bulk Generate" after changing the following settings, press "Check" and then press "Generate".', 'plus-webp' ),
				'wp_cli_label' => __( 'WP-CLI commands are available. If you have a large number of files, WP-CLI commands is more reliable. Command line option allows the user to specify whether to send e-mail, the media ID of the sender and the settings.', 'plus-webp' ),
				'generate_label' => __( 'Generate', 'plus-webp' ),
				'generate_description_label' => __( 'Notified by email with details of the generate results.', 'plus-webp' ),
				'generate_end_label' => __( 'Generation has been completed.', 'plus-webp' ),
				'stop_label' => __( 'Stop', 'plus-webp' ),
				'check_label' => __( 'Check', 'plus-webp' ),
				/* translators: Count of Media */
				'generate_description' => sprintf( __( 'Found %1$d media that can be generated.', 'plus-webp' ), count( $post_ids ) ),
				'non_description' => __( 'Cannot find any media that can be generated.', 'plus-webp' ),
				'non_generate_description' => $non_generate_description,
				'title_label' => __( 'Title' ) . ': ',
				'filename_label' => __( 'File name:' ) . ' ',
				'datetime_label' => __( 'Date/Time' ) . ': ',
				'filesize_label' => __( 'File size:' ) . ' ',
				'post_ids' => wp_json_encode( $post_ids, JSON_UNESCAPED_SLASHES ),
			)
		);

		wp_localize_script(
			'plus-webp',
			'pluswebpsettings_data',
			array(
				'settings_label' => __( 'Settings' ),
				'output_mime_label' => __( 'Generated images', 'plus-webp' ),
				'output_mime_description_label' => __( 'Specifies the file type to be output after conversion.', 'plus-webp' ),
				'quality_label' => __( 'Quality', 'plus-webp' ),
				'lowreso_label' => __( 'low resolution', 'plus-webp' ),
				'highreso_label' => __( 'high resolution', 'plus-webp' ),
				'quality_description_label' => __( 'Specifies the quality of generated images. The higher the number, the better the quality and the larger the file size.', 'plus-webp' ),
				'type_label' => __( 'Type' ),
				'type_description_label' => __( 'Check the type of source image to be converted.', 'plus-webp' ),
				'append_label' => __( 'Append the generated images extension(webp,avif) to the original filename', 'plus-webp' ),
				'apply_label' => __( 'Apply' ),
				'append_description_label' => __( 'Checking this setting, the generated images extension(webp,avif) will be appended to the name of the file, including the extension. Not checking, only the extension is changed.', 'plus-webp' ),
				'replace_label' => __( 'WebP or AVIF replacement of images and contents', 'plus-webp' ),
				'replace_description_label' => __( 'Checking this setting will replace image files with WebP or AVIF when adding new media, and delete the original image file. Also, when generating all images, the original image file ID will be overwritten as WebP or AVIF and the original image file will be deleted. All URLs in the content are also replaced.', 'plus-webp' ),
				'replace_advanced_description_label' => __( 'If you want to replace other databases besides content, use the "plus_webp_advanced_change_db" filter hook.', 'plus-webp' ),
				'replace_advanced_link' => __( 'https://wordpress.org/plugins/plus-webp/', 'plus-webp' ),
				'replace_advanced_text' => __( '"plus_webp_advanced_change_db" filter hook', 'plus-webp' ),
				'settings' => wp_json_encode( $pluswebp_settings, JSON_UNESCAPED_SLASHES ),
			)
		);

		$this->credit_gutenberg( 'plus-webp' );
	}

	/** ==================================================
	 * Register Rest API
	 *
	 * @since 4.00
	 */
	public function register_rest() {

		register_rest_route(
			'rf/plus-webp-generate_api',
			'/token',
			array(
				'methods' => 'POST',
				'callback' => array( $this, 'generate_api_save' ),
				'permission_callback' => array( $this, 'rest_permission' ),
			),
		);

		register_rest_route(
			'rf/plus-webp-settings_api',
			'/token',
			array(
				'methods' => 'POST',
				'callback' => array( $this, 'settings_api_save' ),
				'permission_callback' => array( $this, 'rest_permission' ),
			),
		);
	}

	/** ==================================================
	 * Rest Permission
	 *
	 * @since 4.00
	 */
	public function rest_permission() {

		return current_user_can( 'manage_options' );
	}

	/** ==================================================
	 * Rest API save for Generate
	 *
	 * @param object $request  changed data.
	 * @since 4.00
	 */
	public function generate_api_save( $request ) {

		$args = json_decode( $request->get_body(), true );

		$count = absint( $args['count'] );
		$max_count = absint( $args['max_count'] );
		$attach_id = absint( $args['post_id'] );
		$messages = get_option( 'pluswebp_messages', array() );

		if ( $args['generate'] ) {
			$metadata_org = wp_get_attachment_metadata( $attach_id );

			delete_option( 'pluswebp_generate' );
			do_action( 'wp_generate_attachment_metadata', $metadata_org, $attach_id );
			$webp_id = get_option( 'pluswebp_generate' );
			delete_option( 'pluswebp_generate' );

			if ( $webp_id ) {
				list( $tmp, $messages ) = apply_filters( 'pluswebp_mail_messages', $messages, $webp_id );
				update_option( 'pluswebp_messages', $messages );
			}
			if ( $count === $max_count ) {
				do_action( 'pluswebp_mail_generate_message', $messages, $max_count );
				delete_option( 'pluswebp_messages' );
			}
		} else {
			do_action( 'pluswebp_mail_generate_message', $messages, count( $messages ) );
			delete_option( 'pluswebp_messages' );
		}

		return new WP_REST_Response( $args, 200 );
	}

	/** ==================================================
	 * Rest API save for Settings
	 *
	 * @param object $request  changed data.
	 * @since 4.00
	 */
	public function settings_api_save( $request ) {

		$args = json_decode( $request->get_body(), true );

		$options = array();
		$options['output_mime'] = sanitize_text_field( wp_unslash( $args['output_mime'] ) );
		$options['quality'] = intval( $args['quality'] );
		$options['types'] = filter_var(
			wp_unslash( $args['types'] ),
			FILTER_CALLBACK,
			array(
				'options' => function ( $value ) {
					return sanitize_text_field( $value );
				},
			)
		);
		$options['addext'] = boolval( $args['addext'] );
		$options['replace'] = boolval( $args['replace'] );

		update_option( 'pluswebp', $options );

		return new WP_REST_Response( $args, 200 );
	}

	/** ==================================================
	 * Credit for Gutenberg
	 *
	 * @param string $handle  handle.
	 * @since 4.00
	 */
	private function credit_gutenberg( $handle ) {

		$plugin_name    = null;
		$plugin_ver_num = null;
		$plugin_path    = plugin_dir_path( __DIR__ );
		$plugin_dir     = untrailingslashit( wp_normalize_path( $plugin_path ) );
		$slugs          = explode( '/', $plugin_dir );
		$slug           = end( $slugs );
		$files          = scandir( $plugin_dir );
		foreach ( $files as $file ) {
			if ( '.' === $file || '..' === $file || is_dir( $plugin_path . $file ) ) {
				continue;
			} else {
				$exts = explode( '.', $file );
				$ext  = strtolower( end( $exts ) );
				if ( 'php' === $ext ) {
					$plugin_datas = get_file_data(
						$plugin_path . $file,
						array(
							'name'    => 'Plugin Name',
							'version' => 'Version',
						)
					);
					if ( array_key_exists( 'name', $plugin_datas ) && ! empty( $plugin_datas['name'] ) && array_key_exists( 'version', $plugin_datas ) && ! empty( $plugin_datas['version'] ) ) {
						$plugin_name    = $plugin_datas['name'];
						$plugin_ver_num = $plugin_datas['version'];
						break;
					}
				}
			}
		}

		wp_localize_script(
			$handle,
			'credit',
			array(
				'links'          => __( 'Various links of this plugin', 'plus-webp' ),
				'plugin_version' => __( 'Version:' ) . ' ' . $plugin_ver_num,
				/* translators: FAQ Link & Slug */
				'faq'            => sprintf( __( 'https://wordpress.org/plugins/%1$s/faq', 'plus-webp' ), $slug ),
				'support'        => 'https://wordpress.org/support/plugin/' . $slug,
				'review'         => 'https://wordpress.org/support/view/plugin-reviews/' . $slug,
				'translate'      => 'https://translate.wordpress.org/projects/wp-plugins/' . $slug,
				/* translators: Plugin translation link */
				'translate_text' => sprintf( __( 'Translations for %s' ), $plugin_name ),
				'facebook'       => 'https://www.facebook.com/katsushikawamori/',
				'twitter'        => 'https://twitter.com/dodesyo312',
				'youtube'        => 'https://www.youtube.com/channel/UC5zTLeyROkvZm86OgNRcb_w',
				'donate'         => __( 'https://shop.riverforest-wp.info/donate/', 'plus-webp' ),
				'donate_text'    => __( 'Please make a donation if you like my work or would like to further the development of this plugin.', 'plus-webp' ),
				'donate_button'  => __( 'Donate to this plugin &#187;' ),
			)
		);
	}

	/** ==================================================
	 * Settings register
	 *
	 * @since 1.00
	 */
	public function register_settings() {

		if ( get_option( 'pluswebp' ) ) {
			$pluswebp_settings = get_option( 'pluswebp' );
			/* 'types' from ver 1.08 */
			if ( ! array_key_exists( 'types', $pluswebp_settings ) ) {
				$pluswebp_settings['types'] = array(
					'image/jpeg',
					'image/png',
					'image/bmp',
					'image/gif',
				);
				update_option( 'pluswebp', $pluswebp_settings );
			}
			/* 'addext' from ver 1.13 */
			if ( ! array_key_exists( 'addext', $pluswebp_settings ) ) {
				$pluswebp_settings['addext'] = false;
				update_option( 'pluswebp', $pluswebp_settings );
			}
			/* 'mime' from ver 5.00 */
			if ( ! array_key_exists( 'output_mime', $pluswebp_settings ) ) {
				$pluswebp_settings['output_mime'] = 'image/webp';
				update_option( 'pluswebp', $pluswebp_settings );
			}
		} else {
			$pswp_tbl = array(
				'output_mime' => 'image/webp',
				'quality' => 80,
				'replace' => false,
				'addext' => false,
				'types' => array(
					'image/jpeg',
					'image/png',
					'image/bmp',
					'image/gif',
				),
			);
			update_option( 'pluswebp', $pswp_tbl );
		}
	}
}
