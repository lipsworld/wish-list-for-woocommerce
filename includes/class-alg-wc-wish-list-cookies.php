<?php
/**
 * Wish List for WooCommerce - Cookies
 *
 * @version 1.1.5
 * @since   1.1.5
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Wish_List_Cookies' ) ) {

	class Alg_WC_Wish_List_Cookies {

		/**
		 * Cookie var responsible for saving the unregistered user wishlist
		 *
		 * @since   1.1.5
		 */
		const VAR_UNLOGGED_USER_ID = 'alg-wc-wl-user-id';

		/**
		 * Gets the user id from unlogged user
		 *
		 * @version 1.1.5
		 * @since   1.1.5
		 * @return array|mixed|object
		 */
		public static function get_unlogged_user_id( $create_if_empty = true ) {
			$user_id = isset( $_COOKIE[ self::VAR_UNLOGGED_USER_ID ] ) ? $_COOKIE[ self::VAR_UNLOGGED_USER_ID ] : '';
			if ( $create_if_empty ) {
				if ( empty( $user_id ) ) {
					$user_id = md5( current_time( 'timestamp' ) );
					self::set_user_id( $user_id );
				}
			}

			return $user_id;
		}

		/**
		 * Sets the user id from unlogged user
		 *
		 * @version  1.1.5
		 * @since    1.1.5
		 *
		 * @param     $user_id
		 * @param int $timeout
		 */
		public static function set_user_id( $user_id, $timeout = 1 ) {
			setcookie( self::VAR_UNLOGGED_USER_ID, $user_id, time() + ( $timeout * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN );
		}

	}

}