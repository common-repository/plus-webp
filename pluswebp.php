<?php
/**
 * Plugin Name: Plus WebP or AVIF
 * Plugin URI:  https://wordpress.org/plugins/plus-webp/
 * Description: Generate WebP or AVIF.
 * Version:     5.00
 * Author:      Katsushi Kawamori
 * Author URI:  https://riverforest-wp.info/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: plus-webp
 *
 * @package Plus WebP or AVIF
 */

/*
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

if ( ! class_exists( 'PlusWebp' ) ) {
	require_once __DIR__ . '/lib/class-pluswebp.php';
}
if ( ! class_exists( 'PlusWebpAdmin' ) ) {
	require_once __DIR__ . '/lib/class-pluswebpadmin.php';
}
if ( class_exists( 'WP_CLI' ) ) {
	require_once __DIR__ . '/lib/class-pluswebpavifcli.php';
}
