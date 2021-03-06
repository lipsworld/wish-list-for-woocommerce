<?php

/**
 * Wish List for WooCommerce - Ajax
 *
 * @version 1.3.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */
if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Wish_List_Ajax' ) ) {

	class Alg_WC_Wish_List_Ajax {

		const ACTION_TOGGLE_WISH_LIST_ITEM = 'alg_wc_wl_toggle_item';
		const ACTION_GET_WISH_LIST         = 'alg_wc_wl_get_wish_list';

		/**
		 * Ajax method for toggling items to user wishlist
		 *
		 * @version 1.2.6
		 * @since   1.0.0
		 */
		public static function toggle_wish_list_item() {
			if ( ! isset( $_POST['alg_wc_wl_item_id'] ) ) {
				die();
			}

			$item_id          = intval( sanitize_text_field( $_POST['alg_wc_wl_item_id'] ) );
			$unlogged_user_id = sanitize_text_field( $_POST['unlogged_user_id'] );
			$product          = wc_get_product( $item_id );
			$all_ok           = true;
			$action           = 'added'; // 'added' | 'removed' | error

			$params = apply_filters( 'alg_wc_wl_toggle_item_texts', array(
				'added'         => __( '%s was successfully added to wish list.', 'wish-list-for-woocommerce' ),
				'removed'       => __( '%s was successfully removed from wish list', 'wish-list-for-woocommerce' ),
				'see_wish_list' => __( 'See your wish list', 'wish-list-for-woocommerce' ),
				'error'         => __( 'Sorry, Some error occurred. Please, try again later.', 'wish-list-for-woocommerce' )
			) );

			if ( ! is_user_logged_in() ) {
				$response = Alg_WC_Wish_List_Item::toggle_item_from_wish_list( $item_id, $unlogged_user_id, true );
			} else {
				$user = wp_get_current_user();
				$response = Alg_WC_Wish_List_Item::toggle_item_from_wish_list( $item_id, $user->ID );
			}

			if ( $response === false ) {
				$message = $params['error'];
				$all_ok  = false;
				$action  = 'error';
			} elseif ( $response === true ) {
				$message = sprintf(
					$params['removed'],
					'<b>' . $product->get_title() . '</b>'
				);
				$action  = 'removed';
			} elseif ( is_numeric( $response ) ) {
				$wish_list_page_id         = Alg_WC_Wish_List_Page::get_wish_list_page_id();
				$wish_list_permalink       = get_permalink( $wish_list_page_id );
				$see_your_wishlist_message = $params['see_wish_list'];
				$added_message             = sprintf(
					$params['added'],
					'<b>' . $product->get_title() . '</b>'
				);

				$message = "{$added_message}<br /> <a class='alg-wc-wl-notification-link' href='{$wish_list_permalink}'>{$see_your_wishlist_message}</a>";

				$show_wish_list_link = filter_var( get_option( Alg_WC_Wish_List_Settings_Notification::OPTION_SHOW_WISH_LIST_LINK, true ), FILTER_VALIDATE_BOOLEAN );
				if ( $show_wish_list_link && ! empty( $wish_list_page_id ) ) {
					$message = "{$added_message}<br /> <a class='alg-wc-wl-notification-link' href='{$wish_list_permalink}'>{$see_your_wishlist_message}</a>";
				} else {
					$message = "{$added_message}";
				}

				$action = 'added';
			}

			$ajax_response = array( 'message' => $message, 'action' => $action, 'toggle_item_response' => $response );
			$ajax_response = apply_filters( 'alg_wc_wl_toggle_item_ajax_response', $ajax_response );
			do_action( 'alg_wc_wl_toggle_wish_list_item', $ajax_response );

			if ( $all_ok ) {
				wp_send_json_success( $ajax_response );
			} else {
				wp_send_json_error( $ajax_response );
			}
		}

		/**
		 * Ajax method for get wish list
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 */
		public static function get_wish_list() {
			$args = wp_parse_args( $_POST, array(
				'ignore_excluded_items' => false,
			) );

			$use_id_from_unlogged_user = false;
			if ( is_user_logged_in() ) {
				$user    = wp_get_current_user();
				$user_id = $user->ID;
			} else {
				$use_id_from_unlogged_user = true;
				$user_id                   = Alg_WC_Wish_List_Cookies::get_unlogged_user_id();
			}

			$wishlisted_items = Alg_WC_Wish_List::get_wish_list( $user_id, $use_id_from_unlogged_user );
			$only_valid_items = array();

			if ( $args['ignore_excluded_items'] && is_array( $wishlisted_items ) && count( $wishlisted_items ) > 0 ) {
				$posts = get_posts( array(
					'post_type'      => 'product',
					'posts_per_page' => - 1,
					'post__in'       => $wishlisted_items,
					'orderby'        => 'post__in',
					'order'          => 'asc',
				) );
				if ( is_array( $posts ) ) {
					foreach ($posts as $post){
						$only_valid_items[] = $post->ID;
					}
					$response = array( 'wishlist' => $only_valid_items );
				}else{
					$response = array( 'wishlist' => array() );
				}
			}else{
				if ( !is_array( $wishlisted_items ) ) {
					$response = array( 'wishlist' => array() );
				}else{
					$response = array( 'wishlist' => $wishlisted_items );
				}
			}

			//$response = array( 'wishlist' => $wishlisted_items );
			wp_send_json_success( $response );
		}

		/**
		 * Load ajax actions on javascript
		 *
		 * @version 1.3.0
		 * @since   1.0.0
		 * @param type $script
		 */
		public static function localize_script( $script ) {
			wp_localize_script( $script, 'alg_wc_wl_ajax', array( 'action_toggle_item' => self::ACTION_TOGGLE_WISH_LIST_ITEM ) );
			wp_localize_script( $script, 'alg_wc_wl_get_wl_ajax_action', self::ACTION_GET_WISH_LIST );
		}

		/**
		 * Returns class name
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return type
		 */
		public static function get_class_name() {
			return get_called_class();
		}

	}

}