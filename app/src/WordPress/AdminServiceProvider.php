<?php

namespace Anymarket\WordPress;

use WPEmerge\ServiceProviders\ServiceProviderInterface;
use Anymarket\Anymarket\ExportService;

/**
 * Register admin-related entities, like admin menu pages.
 */
class AdminServiceProvider implements ServiceProviderInterface {
	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		// Nothing to register.
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		add_action( 'admin_menu', [$this, 'registerAdminPages'] );

		//order statuses
		add_action( 'init', [$this, 'registerPostStatus'] );
		add_filter( 'wc_order_statuses', [$this, 'addStatusToWoocommerce']);

		//custom columns on orders
		add_filter( 'manage_shop_order_posts_columns', [$this, 'addColumnsToOrder'], 20);
		add_filter( 'manage_edit-shop_order_sortable_columns', [$this, 'makeOrderColumnsSortable']);
		add_action( 'manage_shop_order_posts_custom_column', [$this, 'populateOrderColumns'], 10, 2);
		add_action( 'pre_get_posts', [$this, 'orderColumnsOrderby'] );

		//bulk action on product
		add_filter( 'bulk_actions-edit-product', [$this, 'bulkExportProducts'] );
		add_filter( 'handle_bulk_actions-edit-product', [$this, 'handleBulkExportProducts'], 10, 3 );

		//bulk action on product categories
		add_filter( 'bulk_actions-edit-product_cat', [$this, 'bulkExportProductCategories'] );
		add_filter( 'handle_bulk_actions-edit-product_cat', [$this, 'handleBulkExportProductCategories'], 10, 3 );

		//admin notices
		add_action( 'admin_notices', [$this, 'bulkExportNotices'] );

		//edit/save action on product categories
		add_action( 'edited_product_cat', [$this, 'saveProductCategories'] );

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

		$redirect = remove_query_arg( [ 'anymarket_export_product_done', 'anymarket_export_product_fail' ], $redirect );

		if( 'anymarket_bulk_export_products' === $doaction ){

			$exportService = new ExportService;
			$response = $exportService->exportProducts( $object_ids );

			if( is_wp_error($response) ){
				$redirect = add_query_arg( 'anymarket_export_product_fail', $response->get_error_message(), $redirect );
			} else{
				$redirect = add_query_arg( 'anymarket_export_product_done', count( $object_ids ), $redirect );
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

		$redirect = remove_query_arg( [ 'anymarket_export_category_done', 'anymarket_export_category_fail' ], $redirect );

		if( 'anymarket_bulk_export_product_categories' === $doaction ){

			$exportService = new ExportService;
			$response = $exportService->exportCategories( $object_ids );

			if( is_wp_error($response) ){
				$redirect = add_query_arg( [ 'anymarket_export_category_fail', $response->get_error_message() ], $redirect );
			} else{
				$redirect = add_query_arg( 'anymarket_export_category_done', true, $redirect );
				set_transient( 'anymarket_category_export_result', $response, 60 );
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

		if ( !empty( $_REQUEST['anymarket_export_product_done'] ) ) {

			printf( '<div id="message" class="updated notice is-dismissible"><p>' .
				_n( '%s produto foi exportado com sucesso.',
					'%s produtos foram exportados com sucesso.',
					intval( $_REQUEST['anymarket_export_product_done'] ),
					'anymarket'
				) .
				'</p></div>', intval( $_REQUEST['anymarket_export_product_done'] )
			);

		}

		if ( !empty( $_REQUEST['anymarket_export_category_done'] ) ) {

			$report = get_transient( 'anymarket_category_export_result' );

			if( !empty($report) ):

				echo '<div id="message" class="updated notice is-dismissible"><p>' ;

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
	}

	/**
	 * Export term to anymarket when edited
	 *
	 * @param int $term_id
	 * @param int $tt_id
	 * @return void
	 */
	public function saveProductCategories( $term_id ){
		$is_on_anymarket = carbon_get_term_meta($term_id, 'anymarket_id');

		if( !empty($is_on_anymarket) ) {
			$exportService = new ExportService;
			$exportService->exportCategories( [$term_id] );
		}
	}
}
