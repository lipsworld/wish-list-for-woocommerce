<?php

/**
 * Wish List for WooCommerce - Custom Actions
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Wish_List_Actions' ) ) {

	class Alg_WC_Wish_List_Actions {

		/**
		 * Before wish list table
		 *
		 * @since   1.0.0
		 */
		const WISH_LIST_TABLE_BEFORE='alg_wc_wl_table_before';

		/**
		 * After wish list table
		 *
		 * @since   1.0.0
		 */
		const WISH_LIST_TABLE_AFTER='alg_wc_wl_table_after';
	}
}