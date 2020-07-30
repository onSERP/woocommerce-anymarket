<?php

namespace Anymarket\WordPress;

use WPEmerge\ServiceProviders\ServiceProviderInterface;
use Anymarket\Anymarket\ExportProducts;
use Anymarket\Anymarket\ExportCategories;
use Anymarket\Anymarket\ExportStock;

/**
 * Register admin-related entities, like admin menu pages.
 */
class AdminServiceProvider implements ServiceProviderInterface {
	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		// Nothing to register.

		/* add_action( 'admin_init', function(){
			$order = wc_get_order( 84 );
			print_r($order);

		} ); */
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		add_action( 'admin_menu', [$this, 'registerAdminPages'] );

		// order statuses
		add_action( 'init', [$this, 'registerPostStatus'] );
		add_filter( 'wc_order_statuses', [$this, 'addStatusToWoocommerce']);

		// custom columns on orders
		add_filter( 'manage_shop_order_posts_columns', [$this, 'addColumnsToOrder'], 20);
		add_filter( 'manage_edit-shop_order_sortable_columns', [$this, 'makeOrderColumnsSortable']);
		add_action( 'manage_shop_order_posts_custom_column', [$this, 'populateOrderColumns'], 10, 2);
		add_action( 'pre_get_posts', [$this, 'orderColumnsOrderby'] );

		// bulk action on product
		add_filter( 'bulk_actions-edit-product', [$this, 'bulkExportProducts'] );
		add_filter( 'handle_bulk_actions-edit-product', [$this, 'handleBulkExportProducts'], 10, 3 );

		// bulk action on product categories
		add_filter( 'bulk_actions-edit-product_cat', [$this, 'bulkExportProductCategories'] );
		add_filter( 'handle_bulk_actions-edit-product_cat', [$this, 'handleBulkExportProductCategories'], 10, 3 );

		// admin notices
		add_action( 'admin_notices', [$this, 'bulkExportNotices'] );
		add_action( 'admin_notices', [$this, 'productNotices'] );

		// edit/save action on product categories
		add_action( 'edited_product_cat', [$this, 'saveProductCategories'] );

		// edit/save products
		add_action( 'save_post', [$this, 'saveProduct'], 10, 3 );

		// new order
		add_action( 'woocommerce_thankyou', [$this, 'discountStock'] );
	}

	/**
	 * Register admin pages.
	 *
	 * @return void
	 */
	public function registerAdminPages() {

		global $submenu;

		$capability = 'manage_options';
		$slug       = 'anymarket';

		add_menu_page( __( 'Painel', 'anymarket' ), 'Anymarket', $capability, $slug, [$this, 'adminIndexPage'], 'dashicons-anymarket', 56 );

		if ( current_user_can( $capability ) ) {
            $submenu[ $slug ][] = [ __( 'Painel', 'anymarket' ), $capability, 'admin.php?page=' . $slug . '#/' ];
            $submenu[ $slug ][] = [ __( 'Exportar', 'anymarket' ), $capability, 'admin.php?page=' . $slug . '#/export' ];
            $submenu[ $slug ][] = [ __( 'Sobre', 'anymarket' ), $capability, 'admin.php?page=' . $slug . '#/about' ];
        }
	}

	/**
	 * Renders main admin page and loads its assets
	 *
	 * @return void
	 */
	public function adminIndexPage(){
		\Anymarket::render('app');

		// enqueue vue assets only to this page
		AssetsServiceProvider::enqueueAdminVueAssets();
	}

	/**
	 * Register new post statuses
	 * will add them to woocommerce later
	 *
	 * @return void
	 */
	public function registerPostStatus(){
		//PAID
		register_post_status( 'anymarket-paid', [
			'label' => _x('Pago', 'WooCommerce Order status', 'anymarket'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop( 'Pago <span class="count">(%s)</span>', 'Pago <span class="count">(%s)</span>' )
		]);

		//BILLED
		register_post_status( 'anymarket-billed', [
			'label' => _x('Faturado', 'WooCommerce Order status','anymarket'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop( 'Faturado <span class="count">(%s)</span>', 'Faturado <span class="count">(%s)</span>' )
		]);

		//SHIPPED
		register_post_status( 'anymarket-shipped', [
			'label' => _x('Enviado', 'WooCommerce Order status','anymarket'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop( 'Enviado <span class="count">(%s)</span>', 'Enviado <span class="count">(%s)</span>' )
		]);

	}


	/**
	 * Add post statuses created above on woocommerce
	 * as order statuses
	 *
	 * @param array $order_statuses
	 * @return array
	 */
	public function addStatusToWoocommerce( $order_statuses ){
		$new_order_statuses = [];

		foreach ( $order_statuses as $key => $status ) {
			$new_order_statuses[$key] = $status;

			//insert them after wc-on-hold
			if ( 'wc-on-hold' === $key ) {
				$new_order_statuses['anymarket-paid'] = _x( 'Pago', 'WooCommerce Order status', 'anymarket' );
				$new_order_statuses['anymarket-billed'] = _x( 'Faturado', 'WooCommerce Order status', 'anymarket' );
				$new_order_statuses['anymarket-shipped'] = _x( 'Enviado', 'WooCommerce Order status', 'anymarket' );
			}
		}


		return $new_order_statuses;
	}

	/**
	 * Adds new columns to order
	 *
	 * @param array $columns
	 * @return array
	 */
	public function addColumnsToOrder( $columns ){
		$new_columns = [];

		foreach ( $columns as $key => $column ) {
			$new_columns[$key] = $column;

			//after order_number column
			if( 'order_number' === $key ){
				$new_columns['marketplace'] = 'Marketplace';
			}

			//after order_number column
			if( 'order_status' === $key) {
				$new_columns['see_order_in_anymarket'] = __('Ver no Anymarket', 'anymarket');
			}
		}

		return $new_columns;
	}

	/**
	 * Get post meta value and shows it tho the user inside
	 * the column
	 *
	 * @param array $column
	 * @param integer|string $post_id
	 * @return void
	 */
	public function populateOrderColumns( $column, $post_id ){
		if( 'marketplace' === $column ){
			echo carbon_get_post_meta($post_id, 'anymarket_order_marketplace');
		}

		if( 'see_order_in_anymarket' === $column ){
			$is_dev = get_option( 'anymarket_is_dev_env' );
			$env = $is_dev === 'true' ? 'sandbox' : 'app';

			if ( carbon_get_post_meta($post_id, 'is_anymarket_order') === 'true' ){
				$id = carbon_get_post_meta($post_id, 'anymarket_id');
				echo "<a href=\"http://${env}.anymarket.com.br/#/orders/edit/${id}\" target=\"_blank\"><b>Ver no Anymarket <span class=\"dashicons dashicons-external\"></span></b></a>";
			}
		}
	}

	/**
	 * Make our custom columns sortable
	 *
	 * @param array $columns
	 * @return $columns
	 */
	public function makeOrderColumnsSortable( $columns ){
		//carbon fields stores the key of the field whith an underscore before it
		$columns['marketplace'] = '_anymarket_order_marketplace';
		return $columns;
	}

	/**
	 * Changes the query to apply the sorting parameters
	 *
	 * @param WP_Query $query
	 * @return void
	 */
	public function orderColumnsOrderby( $query ) {
		if( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( '_anymarket_order_marketplace' === $query->get( 'orderby') ) {
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', '_anymarket_order_marketplace' );
		  }
	}

	/**
	 * Add "export to anymarket" option on bulk
	 * select menu on wp in products
	 *
	 * @param array $bulk_array
	 * @return array $bulk_array
	 */
	public function bulkExportProducts( $bulk_array ){

		$bulk_array = ['anymarket_bulk_export_products' => 'Exportar para o Anymarket'] + $bulk_array;

		return $bulk_array;
	}

	/**
	 * Add "export to anymarket" option on bulk
	 * select menu on wp in product categories
	 *
	 * @param array $bulk_array
	 * @return array $bulk_array
	 */
	public function bulkExportProductCategories( $bulk_array ){

		$bulk_array = ['anymarket_bulk_export_product_categories' => 'Exportar para o Anymarket'] + $bulk_array;

		return $bulk_array;
	}

	/**
	 * Handles bulk action to export products to
	 * Anymarket
	 *
	 * @param string 	$redirect
	 * @param string 	$doaction
	 * @param array 	$object_ids
	 * @return string 	$redirect
	 */
	public function handleBulkExportProducts( $redirect, $doaction, $object_ids ){

		if( 'anymarket_bulk_export_products' === $doaction ){

			$exportProducts = new ExportProducts;
			$response = $exportProducts->export( $object_ids );

			if( is_wp_error($response) ){
				set_transient( 'anymarket_export_product_fail', $response->get_error_message(), 3 );
			} else{
				set_transient( 'anymarket_product_export_result', $response, 3 );
			}

		}

		return $redirect;

	}

	/**
	 * Handles bulk action to export categories to
	 * Anymarket
	 *
	 * @param string 	$redirect
	 * @param string 	$doaction
	 * @param array 	$object_ids
	 * @return string 	$redirect
	 */
	public function handleBulkExportProductCategories( $redirect, $doaction, $object_ids ){

		if( 'anymarket_bulk_export_product_categories' === $doaction ){

			$exportCategories = new ExportCategories;
			$response = $exportCategories->export( $object_ids );

			if( is_wp_error($response) ){
				set_transient( 'anymarket_category_export_fail', $response->get_error_message(), 3 );
			} else{
				set_transient( 'anymarket_category_export_result', $response, 3 );
			}

		}

		return $redirect;

	}

	/**
	 * Add admin notices for anymarket bulk actions
	 *
	 * @return void
	 */
	public function bulkExportNotices(){

		$report = get_transient( 'anymarket_product_export_result' );

		if( !empty($report) ):

			echo '<div class="updated notice is-dismissible"><p>' ;

			foreach ($report as $item) {
				if( empty($item['errorCode']) ){
					printf( __('Produto <b>%s</b> exportado com sucesso.', 'anymarket'), $item['name'] );
					print("<br/>");
				} else{
					printf( __('O produto <b>%1$s</b> falhou na exportação. Código do erro: %2$s.', 'anymarket'), $item['name'], $item['errorCode'] );

					if ($item['errorCode'] === 404){
						echo ' ';
						_e('Se você excluiu este produto no Anymarket, você deverá desativar e reativar a integração do produto novamente', 'anymarket');
					}

					print("<br/>");
				}
			}

			echo '</p></div>';

		endif;

		$report = get_transient( 'anymarket_category_export_result' );

		if( !empty($report) ):

			echo '<div class="updated notice is-dismissible"><p>' ;

			foreach ($report as $item) {
				if( empty($item['errorCode']) ){
					printf( __('Categoria <b>%s</b> exportada com sucesso.', 'anymarket'), $item['name'] );
					print("<br/>");
				} else{
					printf( __('A categoria <b>%1$s</b> falhou na exportação. Código do erro: %2$s.', 'anymarket'), $item['name'], $item['errorCode'] );

					if ($item['errorCode'] === 404){
						echo ' ';
						_e('Se você excluiu esta categoria no Anymarket, você deverá recriá-la no Woocommerce para refazer a integração', 'anymarket');
					}

					print("<br/>");
				}
			}

			echo '</p></div>';

		endif;
	}

	/**
	 * Export term to anymarket when edited
	 *
	 * @param int $term_id
	 * @return void
	 */
	public function saveProductCategories( $term_id ){
		$is_on_anymarket = carbon_get_term_meta($term_id, 'anymarket_id');

		if( !empty($is_on_anymarket) ) {
			$exportCategories = new ExportCategories();
			$exportCategories->export( [$term_id] );
		}
	}

	/**
	 * Export product to anymarket when edited
	 *
	 * @param [type] $post_id
	 * @param [type] $post
	 * @param [type] $updated
	 * @return void
	 */
	public function saveProduct( $post_id, $post, $updated ){

		if ( wp_is_post_revision( $post_id ) ) return;
		if ( wp_is_post_autosave( $post_id ) ) return;

		if ( 'product' === get_post_type( $post_id ) ) {

			// kinda hacky, tho ಠ_ಠ
			add_action( 'updated_postmeta', [$this, 'saveProductPostMeta'], 10, 4);

		}

	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $meta_id
	 * @param [type] $post_id
	 * @param [type] $meta_key
	 * @param [type] $meta_value
	 * @return void
	 */
	public function saveProductPostMeta($meta_id, $post_id, $meta_key, $meta_value){
		//avoid loop
		remove_action( 'save_post',  [$this, 'saveProduct'] );
		remove_action( 'updated_postmeta', [$this, 'saveProductPostMeta'] );

		$response;
		$should_export = carbon_get_post_meta( $post_id, 'anymarket_should_export' );

		if( 'true' === $should_export ) {
			$exportProducts = new ExportProducts();
			$response = $exportProducts->export( [$post_id] );

			if( is_wp_error($response) ){
				set_transient( 'anymarket_product_export_fail', $response->get_error_message(), 3 );
			} else{
				set_transient( 'anymarket_product_export_result', $response, 3 );
			}

		} else {
			carbon_set_post_meta( $post_id, 'anymarket_id', '' );

			$product = wc_get_product( $post_id );
			if ($product instanceof \WC_Product_Variable || $product->get_type() === 'variable'){

				$children = $product->get_children();
				foreach ($children as $child) {
					update_post_meta( $child, 'anymarket_variation_id', '' );
				}
			}
		}

		//avoid loop
		add_action( 'updated_postmeta', [$this, 'saveProductPostMeta'] );
		add_action( 'save_post', [$this, 'saveProduct'] );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function productNotices(){
		$is_dev = get_option( 'anymarket_is_dev_env' );
		$env = $is_dev === 'true' ? 'sandbox' : 'app';

		$screen = get_current_screen();
		if( 'post' === $screen->base && 'product' === $screen->post_type ){
			echo '<div class="notice is-dismissible notice-warning"><p>';
			echo __('<b>Importante:</b> Antes de adicionar variações você deve criá-las no anymarket.', 'anymarket');
			echo " <a href=\"http://${env}.anymarket.com.br/#/variations/list\" target=\"_blank\"><b>Para criar clique aqui <span class=\"dashicons dashicons-external\"></span></b></a>";
			echo '</p></div>';
		}
	}

	/**
	 * remove stock from anymarket on new order
	 *
	 * @param [type] $order
	 * @return void
	 */
	public function discountStock( $order_id ){

		$exportStock = new exportStock;
		$exportStock->export( [$order_id] );

	}
}
