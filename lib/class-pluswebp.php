<?php
/**
 * Plus WebP or AVIF
 *
 * @package    Plus WebP or AVIF
 * @subpackage PlusWebp Main function
/*  Copyright (c) 2019- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
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

$pluswebp = new PlusWebp();

/** ==================================================
 * Class Main function
 *
 * @since 1.00
 */
class PlusWebp {

	/** ==================================================
	 * Dir
	 *
	 * @var $upload_dir DIR.
	 */
	private $upload_dir;

	/** ==================================================
	 * URL
	 *
	 * @var $upload_url URL.
	 */
	private $upload_url;

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		$wp_uploads = wp_upload_dir();

		$upload_dir = wp_normalize_path( $wp_uploads['basedir'] );
		$upload_url = $wp_uploads['baseurl'];
		if ( is_ssl() ) {
			$upload_url = str_replace( 'http:', 'https:', $upload_url );
		}
		$this->upload_dir = untrailingslashit( $upload_dir );
		$this->upload_url = untrailingslashit( $upload_url );

		/* Generate metadata */
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'generate_webp' ), 10, 2 );
		/* Original hook */
		add_filter( 'pluswebp_get_allimages', array( $this, 'get_allimages' ), 10, 2 );
		add_filter( 'pluswebp_mail_messages', array( $this, 'mail_messages' ), 10, 2 );
		add_action( 'pluswebp_mail_generate_message', array( $this, 'mail_generate_message' ), 10, 2 );

		/* Raise memory */
		add_filter( 'pluswebp_memory_limit', array( $this, 'raise_memory_limit' ) );
		/* webp generation control */
		add_filter( 'wp_upload_image_mime_transforms', array( $this, 'control_mime_type' ), 10, 2 );
	}

	/** ==================================================
	 * Webp of AVIF generate
	 *
	 * @param array $metadata  metadata.
	 * @param int   $attachment_id  ID.
	 * @return array $metadata  metadata.
	 * @since 1.00
	 */
	public function generate_webp( $metadata, $attachment_id ) {

		$pluswebp_settings = get_option( 'pluswebp' );

		switch ( $pluswebp_settings['output_mime'] ) {
			case 'image/webp':
				$ext = 'webp';
				break;
			case 'image/avif':
				$ext = 'avif';
				break;
			default:
				$ext = 'webp';
		}

		$mime_type = get_post_mime_type( $attachment_id );
		if ( in_array( $mime_type, $pluswebp_settings['types'] ) ) {
			$metadata_webp         = $metadata;
			$file_webp             = $this->change_ext( $metadata['file'], $ext, $pluswebp_settings['addext'] );
			$metadata_webp['file'] = $file_webp;
			if ( '.' === dirname( $file_webp ) ) {
				$dir_name_url  = '/';
				$dir_name_path = wp_normalize_path( '/' );
			} else {
				$dir_name_url = '/' . dirname( $file_webp ) . '/';
				$dir_name_path = wp_normalize_path( $dir_name_url );
			}
			$url  = $this->upload_url . $dir_name_url;
			$path = $this->upload_dir . $dir_name_path;

			foreach ( (array) $metadata['sizes'] as $key => $value ) {
				$file_thumb      = $value['file'];
				$file_thumb_webp = $this->change_ext( $file_thumb, $ext, $pluswebp_settings['addext'] );
				$ret  = $this->create_webp( $path . $file_thumb, $mime_type, $path . $file_thumb_webp, $pluswebp_settings['quality'], $pluswebp_settings['output_mime'] );
				if ( $ret ) {
					$metadata_webp['sizes'][ $key ]['file']      = $file_thumb_webp;
					$metadata_webp['sizes'][ $key ]['mime-type'] = $pluswebp_settings['output_mime'];
					$webp_size = filesize( $path . wp_basename( $file_thumb_webp ) );
					$metadata_webp['sizes'][ $key ]['filesize'] = $webp_size;
					if ( $pluswebp_settings['replace'] ) {
						wp_delete_file( $path . $file_thumb );
						$this->change_db( $url . $file_thumb, $url . $file_thumb_webp );
					}
				}
			}

			$org_img_file = null;
			if ( array_key_exists( 'original_image', $metadata ) && ! empty( $metadata['original_image'] ) ) {
				$org_img_file  = wp_normalize_path( wp_get_original_image_path( $attachment_id, false ) );
				$org_webp_file = $this->change_ext( $org_img_file, $ext, $pluswebp_settings['addext'] );
				$ret = $this->create_webp( $org_img_file, $mime_type, $org_webp_file, $pluswebp_settings['quality'], $pluswebp_settings['output_mime'] );
				if ( $ret ) {
					$metadata_webp['original_image'] = wp_basename( $org_webp_file );
				}
			}

			$ret = $this->create_webp( $this->upload_dir . '/' . $metadata['file'], $mime_type, $path . wp_basename( $file_webp ), $pluswebp_settings['quality'], $pluswebp_settings['output_mime'] );
			$webp_size = filesize( $path . wp_basename( $file_webp ) );
			$metadata_webp['filesize'] = $webp_size;
			if ( $ret ) {
				if ( $pluswebp_settings['replace'] ) {
					$up_post = array(
						'ID'             => $attachment_id,
						'guid'           => $this->upload_url . '/' . $file_webp,
						'post_mime_type' => $pluswebp_settings['output_mime'],
					);
					wp_update_post( $up_post );
					update_post_meta( $attachment_id, '_wp_attached_file', $file_webp );
					/* for bulk generate */
					update_post_meta( $attachment_id, '_wp_attachment_metadata', $metadata_webp );
					/* delete org file */
					wp_delete_file( $this->upload_dir . '/' . $metadata['file'] );
					if ( $org_img_file ) {
						wp_delete_file( $org_img_file );
					}
					/* Replace */
					$this->change_db( $this->upload_url . '/' . $metadata['file'], $this->upload_url . '/' . $file_webp );
					/* for hook */
					$metadata = $metadata_webp;
					/* for mail */
					$attach_id  = $attachment_id;
				} else {
					$post       = get_post( $attachment_id );
					$title      = $post->post_title;
					$attachment = array(
						'guid'           => $this->upload_url . '/' . $file_webp,
						'post_mime_type' => $pluswebp_settings['output_mime'],
						'post_title'     => $title,
						'post_content'   => '',
						'post_status'    => 'inherit',
					);
					$file = $this->upload_dir . '/' . $file_webp;
					$attach_id  = wp_insert_attachment( $attachment, $file );

					/* for XAMPP [ get_attached_file( $attach_id ): Unable to get correct value ] */
					$metapath_name = str_replace( $this->upload_dir . '/', '', $file );
					update_post_meta( $attach_id, '_wp_attached_file', $metapath_name );

					wp_update_attachment_metadata( $attach_id, $metadata_webp );

					$author      = get_userdata( $post->post_author );
					$userid      = $author->ID;
					$postdate    = get_the_date( 'Y-m-d H:i:s', $attachment_id );
					$postdategmt = get_gmt_from_date( $postdate );
					$up_post     = array(
						'ID'                => $attach_id,
						'post_author'       => $userid,
						'post_date'         => $postdate,
						'post_date_gmt'     => $postdategmt,
						'post_modified'     => $postdate,
						'post_modified_gmt' => $postdategmt,
					);
					wp_update_post( $up_post );
				}

				update_option( 'pluswebp_generate', $attach_id );

				/* for Media Library folders term by Organize Media Folder */
				do_action( 'omf_folders_term_update', $metadata_webp, $attach_id );

				/* for Term filter update by Organize Media Folder */
				do_action( 'omf_term_filter_update' );

			}
		}

		return $metadata;
	}

	/** ==================================================
	 * Thumbnail urls
	 *
	 * @param int    $attach_id  attach_id.
	 * @param array  $metadata  metadata.
	 * @param string $upload_url  upload_url.
	 * @return array $image_thumbnail(string), $imagethumburls(array)
	 * @since 2.00
	 */
	private function thumbnail_urls( $attach_id, $metadata, $upload_url ) {

		$image_attr_thumbnail = wp_get_attachment_image_src( $attach_id, 'thumbnail', true );
		$image_thumbnail = $image_attr_thumbnail[0];

		$imagethumburls = array();
		if ( ! empty( $metadata ) && array_key_exists( 'sizes', $metadata ) ) {
			$thumbnails  = $metadata['sizes'];
			$path_file  = get_post_meta( $attach_id, '_wp_attached_file', true );
			$filename   = wp_basename( $path_file );
			$media_path = str_replace( $filename, '', $path_file );
			$media_url  = $upload_url . '/' . $media_path;
			foreach ( $thumbnails as $key => $key2 ) {
				$imagethumburls[ $key ] = $media_url . $key2['file'];
			}
		}

		return array( $image_thumbnail, $imagethumburls );
	}

	/** ==================================================
	 * Output datas
	 *
	 * @param int   $attach_id  attach_id.
	 * @param array $metadata  metadata.
	 * @return array (string) $attachment_link, $attachment_url, $filename, $original_image_url, $original_filename, $mime_type(string), $stamptime, $file_size
	 * @since 2.00
	 */
	private function output_datas( $attach_id, $metadata ) {

		$attachment_link = get_attachment_link( $attach_id );
		$attachment_url = wp_get_attachment_url( $attach_id );
		$filename = wp_basename( $attachment_url );
		if ( ! empty( $metadata ) && array_key_exists( 'original_image', $metadata ) && ! empty( $metadata['original_image'] ) ) {
			$original_image_url = wp_get_original_image_url( $attach_id );
			$original_filename = wp_basename( $original_image_url );
		} else {
			$original_image_url = null;
			$original_filename = null;
		}
		$mime_type = get_post_mime_type( $attach_id );

		$stamptime = get_the_time( 'Y-n-j ', $attach_id ) . get_the_time( 'G:i:s', $attach_id );
		if ( ! empty( $metadata ) && array_key_exists( 'filesize', $metadata ) && ! empty( $metadata['filesize'] ) ) {
			$file_size = $metadata['filesize'];
		} else {
			$file_size = @filesize( get_attached_file( $attach_id ) );
		}
		if ( ! $file_size ) {
			$file_size = __( 'Could not retrieve.', 'plus-webp' );
		} else {
			$file_size = size_format( $file_size );
		}

		return array( $attachment_link, $attachment_url, $filename, $original_image_url, $original_filename, $mime_type, $stamptime, $file_size );
	}

	/** ==================================================
	 * Get All Images
	 *
	 * @param array  $types  mime types.
	 * @param string $output_mime  output mime type.
	 * @since 2.00
	 */
	public function get_allimages( $types, $output_mime ) {

		if ( empty( $types ) ) {
			return array( array(), array() );
		}

		$args = array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => $types,
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		$posts = get_posts( $args );

		global $wpdb;
		$webp_titles = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT	post_title
				FROM	{$wpdb->prefix}posts
				WHERE	post_type = 'attachment'
						AND post_mime_type IN ( %s )
						AND post_status = 'inherit'
				",
				$output_mime,
			)
		);

		$post_ids = array();
		$no_file_ids = array();
		$count = 0;
		foreach ( $posts as $post ) {
			if ( ! in_array( $post->post_title, $webp_titles ) ) {
				if ( file_exists( get_attached_file( $post->ID ) ) ) {
					$post_ids[] = $post->ID;
				} else {
					$no_file_ids[] = $post->ID;
				}
			}
		}

		return array( $post_ids, $no_file_ids );
	}

	/** ==================================================
	 * Mail messages hook
	 *
	 * @param array $messages  messages.
	 * @param int   $webp_id  webp ID or avif ID.
	 *
	 * @since 4.00
	 */
	public function mail_messages( $messages, $webp_id ) {

		$metadata = wp_get_attachment_metadata( $webp_id );

		$message = null;
		if ( $metadata ) {
			/* Thumbnail urls */
			list( $image_thumbnail, $imagethumburls ) = $this->thumbnail_urls( $webp_id, $metadata, $this->upload_url );
			/* Output datas*/
			list( $attachment_link, $attachment_url, $filename, $original_image_url, $original_filename, $mime_type, $stamptime, $file_size ) = $this->output_datas( $webp_id, $metadata );

			$message = 'ID: ' . $webp_id . "\n";
			$message .= __( 'Title' ) . ': ' . get_the_title( $webp_id ) . "\n";
			$message .= __( 'Permalink:' ) . ' ' . $attachment_link . "\n";
			$message .= 'URL: ' . $attachment_url . "\n";
			$message .= __( 'File name:' ) . ' ' . $filename . "\n";
			if ( ! empty( $original_image_url ) ) {
				$message .= __( 'Original URL:', 'plus-webp' ) . ' ' . $original_image_url . "\n";
				$message .= __( 'Original File name:', 'plus-webp' ) . ' ' . $original_filename . "\n";
			}
			$message .= __( 'Date/Time' ) . ': ' . $stamptime . "\n";
			$message .= __( 'File size:' ) . ' ' . $file_size . "\n";
			if ( ! empty( $imagethumburls ) ) {
				foreach ( $imagethumburls as $thumbsize => $imagethumburl ) {
					$message .= $thumbsize . ': ' . $imagethumburl . "\n";
				}
			}
			$message .= "\n";
			$messages[] = $message;
		}

		return array( $message, $messages );
	}

	/** ==================================================
	 * Mail sent for Generate Messages
	 *
	 * @param array $messages  mail messages.
	 * @param int   $count  convert count.
	 * @since 4.02
	 */
	public function mail_generate_message( $messages, $count ) {

		if ( function_exists( 'wp_date' ) ) {
			$now_date_time = wp_date( 'Y-m-d H:i:s' );
		} else {
			$now_date_time = date_i18n( 'Y-m-d H:i:s' );
		}
		/* translators: Date and Time */
		$message_head = sprintf( __( 'Plus WebP or AVIF : %s', 'plus-webp' ), $now_date_time ) . "\r\n\r\n";
		/* translators: Generated count */
		$message_head .= sprintf( __( 'Webp or AVIF generated %d.', 'plus-webp' ), $count ) . "\r\n\r\n";

		$to = get_option( 'admin_email' );
		/* translators: blogname for subject */
		$subject = sprintf( __( '[%s] WebP or AVIF generate', 'plus-webp' ), get_option( 'blogname' ) );
		wp_mail( $to, $subject, $message_head . implode( $messages ) );
	}

	/** ==================================================
	 * WebP or AVIF create
	 *
	 * @param string $filename  input filename for pictures.
	 * @param string $mime_type  mimetype.
	 * @param string $filename_webp  output filename for webp or avif.
	 * @param int    $quality  quality of webp.
	 * @param string $output_mime  output mime type.
	 * @return bool $ret create bool.
	 * @since 1.00
	 */
	private function create_webp( $filename, $mime_type, $filename_webp, $quality, $output_mime ) {

		if ( ! file_exists( $filename ) ) {
			return false;
		}
		if ( file_exists( $filename_webp ) ) {
			return false;
		}

		@set_time_limit( 60 );
		wp_raise_memory_limit( 'pluswebp' );

		$ret = false;
		switch ( $mime_type ) {
			case 'image/jpeg':
				$src = imagecreatefromjpeg( $filename );
				$img = imagecreatetruecolor( imagesx( $src ), imagesy( $src ) );
				$bgcolor = imagecolorallocate( $img, 255, 255, 255 );
				imagefill( $img, 0, 0, $bgcolor );
				imagealphablending( $img, true );
				break;
			case 'image/png':
				$src = imagecreatefrompng( $filename );
				$img = imagecreatetruecolor( imagesx( $src ), imagesy( $src ) );
				imagealphablending( $img, false );
				imagesavealpha( $img, true );
				$bgcolor = imagecolorallocatealpha( $img, 0, 0, 0, 127 );
				imagefill( $img, 0, 0, $bgcolor );
				imagecolortransparent( $img, $bgcolor );
				break;
			case 'image/bmp':
				$src = imagecreatefrombmp( $filename );
				$img = imagecreatetruecolor( imagesx( $src ), imagesy( $src ) );
				break;
			case 'image/gif':
				$src = imagecreatefromgif( $filename );
				$img = imagecreatetruecolor( imagesx( $src ), imagesy( $src ) );
				$bgcolor = imagecolorallocatealpha( $img, 0, 0, 0, 127 );
				imagefill( $img, 0, 0, $bgcolor );
				imagecolortransparent( $img, $bgcolor );
				break;
		}

		imagecopy( $img, $src, 0, 0, 0, 0, imagesx( $src ), imagesy( $src ) );
		imagedestroy( $src );

		switch ( $output_mime ) {
			case 'image/webp':
				$ret = imagewebp( $img, $filename_webp, $quality );
				break;
			case 'image/avif':
				$ret = imageavif( $img, $filename_webp, $quality );
				break;
			default:
				$ret = imagewebp( $img, $filename_webp, $quality );
		}

		imagedestroy( $img );

		return $ret;
	}

	/** ==================================================
	 * Change ext
	 *
	 * @param string $before_file_name  before_file_name.
	 * @param string $ext  ext.
	 * @param bool   $addext  addext.
	 * @return array $after_file_name  after_file_name.
	 * @since 1.00
	 */
	private function change_ext( $before_file_name, $ext, $addext ) {

		if ( $addext ) {
			$after_file_name = $before_file_name . '.' . $ext;
		} else {
			$exts            = explode( '.', $before_file_name );
			$before_ext      = '.' . end( $exts );
			$after_ext       = '.' . $ext;
			$after_file_name = str_replace( $before_ext, $after_ext, $before_file_name );
		}

		return $after_file_name;
	}

	/** ==================================================
	 * Change DB
	 *
	 * @param string $before_url  before_url.
	 * @param string $after_url  after_url.
	 * @since 1.00
	 */
	private function change_db( $before_url, $after_url ) {

		global $wpdb;

		/* Replace */
		$wpdb->query(
			$wpdb->prepare(
				"
				UPDATE {$wpdb->prefix}posts
				SET post_content = replace( post_content, %s, %s )
				",
				$before_url,
				$after_url
			)
		);

		/* Advanced change database */
		list( $before_url, $after_url ) = apply_filters( 'plus_webp_advanced_change_db', $before_url, $after_url );
	}

	/** ==================================================
	 * Filter Raise Memory limit
	 *
	 * @since 3.00
	 *
	 * @param string $filtered_limit  Memory limit.
	 */
	public function raise_memory_limit( $filtered_limit ) {

		return '256M';
	}

	/** ==================================================
	 * Filter the output mime types for a given input mime type and image size.
	 *
	 * @since 3.00
	 *
	 * @param array $image_mime_transforms  A map with the valid mime transforms where the key is the source file mime type
	 *                                      and the value is one or more mime file types to generate.
	 * @param int   $attachment_id  The ID of the attachment where the hook was dispatched.
	 */
	public function control_mime_type( $image_mime_transforms, $attachment_id ) {

		$image_mime_transforms = array();

		return $image_mime_transforms;
	}
}
