<?php

namespace Anymarket\WordPress;

use WPEmerge\ServiceProviders\ServiceProviderInterface;
use Anymarket\Anymarket\ExportCategories;
use Anymarket\Anymarket\ExportBrands;
use Anymarket\Anymarket\ExportStock;
use Anymarket\Anymarket\ExportVariations;

use Anymarket\Anymarket\ExportProducts;
use Anymarket\Anymarket\AnymarketOrder;

/**
 * Register admin-related entities, like admin menu pages.
 */
class AdminServiceProvider implements ServiceProviderInterface {
	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		$this->cron = new CronEvents();
		$this->cron->init();
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
		if( get_option( 'anymarket_use_order' ) == 'true' ) {
			add_filter( 'manage_shop_order_posts_columns', [$this, 'addColumnsToOrder'], 20);
			add_filter( 'manage_edit-shop_order_sortable_columns', [$this, 'makeOrderColumnsSortable']);
			add_action( 'manage_shop_order_posts_custom_column', [$this, 'populateOrderColumns'], 10, 2);
			add_action( 'pre_get_posts', [$this, 'orderColumnsOrderby'] );
		}

		// bulk action on product
		add_filter( 'bulk_actions-edit-product', [$this, 'bulkExportProducts'] );
		add_filter( 'handle_bulk_actions-edit-product', [$this, 'handleBulkExportProducts'], 10, 3 );
		add_filter( 'handle_bulk_actions-edit-product', [$this, 'handleRemoveIntegrationProducts'], 10, 3 );

		// bulk action on product categories
		add_filter( 'bulk_actions-edit-product_cat', [$this, 'bulkExportProductCategories'] );
		add_filter( 'handle_bulk_actions-edit-product_cat', [$this, 'handleBulkExportProductCategories'], 10, 3 );

		if ( defined('ANYMARKET_BRAND_CPT') ){
			// bulk action on product brands
			add_filter( 'bulk_actions-edit-' . ANYMARKET_BRAND_CPT, [$this, 'bulkExportProductBrands'] );
			add_filter( 'handle_bulk_actions-edit-' . ANYMARKET_BRAND_CPT, [$this, 'handleBulkExportProductBrands'], 10, 3 );
		}

		// edit/save action on product categories
		add_action( 'edited_term', [$this, 'saveProductCategories'], 10, 3 );

		// edit/save products
		add_action( 'save_post', [$this, 'saveProduct'], 10, 3 );

		// delete category on anymarket
		add_action( 'admin_init', [$this, 'deleteCategoryOnAnymarket'] );

		//bulk export variations
		add_action( 'admin_init', [$this, 'bulkExportVariations'] );
		add_action('admin_footer', [$this, 'exportVariationsButton']);

		//initiate shipping class
		add_filter( 'woocommerce_shipping_methods', [$this, 'addAnymarketShippingMethod'] );

		// on order change status
		if( get_option( 'anymarket_use_order' ) == 'true' ) {
			add_action( 'woocommerce_order_status_changed', [$this, 'updateStatus'], 10, 3);
		}
		// discount stock on new order
		add_action( 'woocommerce_thankyou', [$this, 'discountStock'], 10, 1);
		add_action( 'woocommerce_order_status_changed', [$this, 'discountStockonStatusChanged'], 10, 3);

		// allow xml uploads
		add_filter( 'upload_mimes', [$this, 'uploadXML'] );

		// filter anymarket id field
		if( get_option( 'anymarket_edit_mode' ) == 'true' ) {
			add_filter( 'update_post_metadata', [$this, 'updateAnymarketId'], 10, 5 );
		}

		add_action( 'admin_init', [$this, 'updateAllProducts'] );

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
            $submenu[ $slug ][] = [ __( 'Atualizar em massa', 'anymarket' ), $capability, 'admin.php?page=' . $slug . '#/mass_update' ];
            $submenu[ $slug ][] = [ __( 'Instruções', 'anymarket' ), $capability, 'admin.php?page=' . $slug . '#/instructions' ];
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
		//BILLED
		register_post_status( 'wc-anymarket-billed', [
			'label' => _x('Faturado (Anymarket) ', 'WooCommerce Order status','anymarket'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop( 'Faturado (Anymarket) <span class="count">(%s)</span>', 'Faturado (Anymarket) <span class="count">(%s)</span>', 'anymarket' )
		]);

		//SHIPPED
		register_post_status( 'wc-anymarket-shipped', [
			'label' => _x('Enviado (Anymarket)', 'WooCommerce Order status','anymarket'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop( 'Enviado (Anymarket) <span class="count">(%s)</span>', 'Enviado (Anymarket)<span class="count">(%s)</span>', 'anymarket' )
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
				$new_order_statuses['wc-anymarket-billed'] = _x( 'Faturado (Anymarket)', 'WooCommerce Order status', 'anymarket' );
				$new_order_statuses['wc-anymarket-shipped'] = _x( 'Enviado (Anymarket)', 'WooCommerce Order status', 'anymarket' );
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
		$bulk_array = ['anymarket_bulk_remove_integration_products' => 'Remover da Integração com Anymarket'] + $bulk_array;

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
	 * Add "export to anymarket" option on bulk
	 * select menu on wp in product brands
	 *
	 * @param array $bulk_array
	 * @return array $bulk_array
	 */
	public function bulkExportProductBrands( $bulk_array ){

		$bulk_array = ['anymarket_bulk_export_product_brands' => 'Exportar para o Anymarket'] + $bulk_array;

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

			if( get_option( 'anymarket_use_cron' ) == 'true' ){

				count($object_ids) > 1 && $this->cron->setCronBulkExportProd( 5, [$object_ids, false, []] );
				count($object_ids) <= 1 && $this->cron->setCronExportProd( 5, $object_ids );

			} else {
				$exportProducts = new ExportProducts();
				$response = $exportProducts->export( $object_ids );

				if( is_wp_error($response) ){
					set_transient( 'anymarket_product_export_fail', $response->get_error_message(), MINUTE_IN_SECONDS );
				} else{
					set_transient( 'anymarket_product_export_result', $response, MINUTE_IN_SECONDS );
				}

			}
		}

		return $redirect;

	}

	/**
	 * Undocumented function
	 *
	 * @param string 	$redirect
	 * @param string 	$doaction
	 * @param array 	$object_ids
	 * @return string 	$redirect
	 */
	public function handleRemoveIntegrationProducts( $redirect, $doaction, $object_ids ){
		if( 'anymarket_bulk_remove_integration_products' === $doaction ){

			foreach( $object_ids as $object_id ){
				carbon_set_post_meta( $object_id, 'anymarket_id', '' );
				carbon_set_post_meta( $object_id, 'anymarket_variation_id', '' );
				carbon_set_post_meta( $object_id, 'anymarket_should_export', 'false' );

				$product = wc_get_product( $object_id );

				if ($product instanceof \WC_Product_Variable || $product->get_type() === 'variable'){

					$children = $product->get_children();
					foreach ($children as $child) {
						update_post_meta( $child, 'anymarket_variation_id', '' );
					}
				}
			}

			$posts = get_posts([
				'include' => $object_ids
			]);

			set_transient( 'anymarket_remove_integration_product_done', $posts, MINUTE_IN_SECONDS );

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
				set_transient( 'anymarket_category_export_fail', $response->get_error_message(), MINUTE_IN_SECONDS );
			} else{
				set_transient( 'anymarket_category_export_result', $response, MINUTE_IN_SECONDS );
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
	public function handleBulkExportProductBrands( $redirect, $doaction, $object_ids ){

		if( 'anymarket_bulk_export_product_brands' === $doaction ){

			$exportBrands = new ExportBrands;
			$response = $exportBrands->export( $object_ids );

			if( is_wp_error($response) ){
				set_transient( 'anymarket_brand_export_fail', $response->get_error_message(), MINUTE_IN_SECONDS );
			} else{
				set_transient( 'anymarket_brand_export_result', $response, MINUTE_IN_SECONDS );
			}

		}

		return $redirect;

	}

	/**
	 * Export term to anymarket when edited
	 *
	 * @param int $term_id
	 * @param int $tt_id
	 * @param string $taxonomy
	 * @return void
	 */
	public function saveProductCategories( $term_id, $tt_id, $taxonomy ){

		if( $taxonomy = 'product_cat'){

			$this->currentEditingTerm = $term_id;
			add_action('carbon_fields_term_meta_container_saved', [$this, 'saveProductCategoriesTermsMeta'] );

		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function saveProductCategoriesTermsMeta(){
		//avoid loop
		remove_action( 'edited_term', [$this, 'saveProductCategories'] );
		remove_action('carbon_fields_term_meta_container_saved', [$this, 'saveProductCategoriesTermsMeta'] );

		$is_on_anymarket = carbon_get_term_meta($this->currentEditingTerm, 'anymarket_id');

		if( !empty($is_on_anymarket) ) {
			$exportCategories = new ExportCategories();
			$exportCategories->export( [$this->currentEditingTerm] );
		}

		add_action('carbon_fields_term_meta_container_saved', [$this, 'saveProductCategoriesTermsMeta'] );
		add_action( 'edited_term', [$this, 'saveProductCategories'], 10, 3 );
	}




	/**
	 * Export product to anymarket when edited
	 *
	 * @param int $post_id
	 * @param \WP_Post $post
	 * @param boolean $updated
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

	public function saveProductPostMeta($meta_id, $post_id, $meta_key, $meta_value){
		//avoid loop
		remove_action( 'save_post',  [$this, 'saveProduct'] );
		remove_action( 'updated_postmeta', [$this, 'saveProductPostMeta'] );

		$should_export = carbon_get_post_meta( $post_id, 'anymarket_should_export' );

		if( 'true' === $should_export ) {

			if( get_option( 'anymarket_use_cron' ) == 'true' ) {
				$this->cron->setCronExportProd( 5, [$post_id] );

			} else{
				$exportProducts = new ExportProducts();
				$response = $exportProducts->export( [$post_id] );

				if( is_wp_error($response) ){
					set_transient( 'anymarket_product_export_fail', $response->get_error_message(), MINUTE_IN_SECONDS );
				} else{
					set_transient( 'anymarket_product_export_result', $response, MINUTE_IN_SECONDS );
				}
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

			return;
		}

		//avoid loop
		add_action( 'updated_postmeta', [$this, 'saveProductPostMeta'] );
		add_action( 'save_post', [$this, 'saveProduct'] );
	}

	/**
	 * remove stock from anymarket on new order
	 *
	 * @param int $order_id
	 * @return void
	 */
	public function discountStock( $order_id ){
		$exportStock = new ExportStock;
		$exportStock->exportFromOrder( $order_id );
	}


	/**
	 * remove stock from anymarket on order status changed
	 *
	 * @param int $order_id
	 * @return void
	 */
	public function discountStockonStatusChanged( $order_id, $old_status, $new_status ){
		$exportStock = new ExportStock;
		$exportStock->exportFromOrder( $order_id );
	}

	/**
	 * Delete a category only on anymarket
	 *
	 * @return void
	 */
	public function deleteCategoryOnAnymarket(){

		if ( wp_doing_ajax() ) return;

		if ( isset( $_GET['anymarket_action'])
		&& $_GET['anymarket_action'] === 'delete_category'
		&& current_user_can('manage_options') ){

			$deleteCategory = new ExportCategories();
			$deleted = $deleteCategory->delete( $_GET['tag_ID'] );

			if( is_wp_error($deleted) ){
				set_transient( 'anymarket_category_delete_error', $deleted->get_error_message(), MINUTE_IN_SECONDS);
			} else {
				set_transient( 'anymarket_category_delete_success', $deleted, MINUTE_IN_SECONDS);
			}

			wp_safe_redirect( remove_query_arg( 'anymarket_action' ) );
		}

		return;
	}

	/**
	 * Export all variations to anymarket
	 *
	 * @return void
	 */
	public function bulkExportVariations(){

		if ( wp_doing_ajax() ) return;

		if ( isset( $_GET['anymarket_action'])
		&& $_GET['anymarket_action'] === 'export_variations'
		&& current_user_can('manage_options') ){

			$exportVariation = new ExportVariations();

			$types = $exportVariation->variationTypes();

			sleep(1);

			$values = $exportVariation->variationValues();

			if( is_wp_error($types) ){
				set_transient( 'anymarket_variation_type_export_error', $types->get_error_message(), 10);
			} else {
				set_transient( 'anymarket_variation_type_export_success', $types, 10);
			}

			if( is_wp_error($values) ){
				set_transient( 'anymarket_variation_value_export_error', $values->get_error_message(), 10);
			} else {
				set_transient( 'anymarket_variation_value_export_success', $values, 10);
			}

			wp_safe_redirect( remove_query_arg( 'anymarket_action' ) );
		}
	}

	public function exportVariationsButton(){
		?>
			<a id="button-export-variation" class="page-title-action" style="display: inline-block;padding-bottom: 4px;" href="<?php echo esc_url( add_query_arg( [	'anymarket_action' => 'export_variations' ] ) )  ?>">
				<?php _e('Exportar em massa', 'anymarket') ?>
			</a>'

			<script>
				document.addEventListener('DOMContentLoaded',  function() {
					var _wrapElement = document.querySelector('.wrap.woocommerce h1');
					var _buttonExport = document.getElementById('button-export-variation');

					if (_wrapElement) _wrapElement.after(_buttonExport);
				});

			</script>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $order_id
	 * @param [type] $old_status
	 * @param [type] $new_status
	 * @return void
	 */
	public function updateStatus( $order_id, $old_status, $new_status ){

		if(get_option( 'anymarket_use_cron' ) == 'true'){
			$this->cron->setCronExportOrder( 5, [$order_id, $new_status] );
		} else {
			$order = new AnymarketOrder();
			$order->updateStatus( $order_id, $new_status );
		}



	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $mimes
	 * @return void
	 */
	public function uploadXML($mimes) {
		if( !current_user_can( 'edit_posts' )) return $mimes;

		$mimes = array_merge($mimes, ['xml' => 'text/xml']);
		return $mimes;
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $methods
	 * @return void
	 */
	public function addAnymarketShippingMethod( $methods ) {
		$methods[] = AnymarketShippingMethod::class;
		return $methods;
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $meta_id
	 * @param [type] $object_id
	 * @param [type] $meta_key
	 * @param [type] $meta_value
	 * @return void
	 */
	public function updateAnymarketId( $check, $object_id, $meta_key, $meta_value, $prev_value ){

		if ( $meta_key === '_anymarket_id' &&  !empty($meta_value) ){

			$get_ids = new ExportVariations;
			$get_ids->assignVariationIDs($object_id, $meta_value );

		}

		return $check;
	}

	/**
	 * Update all products
	 *
	 * @return void
	 */
	public function updateAllProducts(){

		if ( wp_doing_ajax() ) return;

		if ( isset( $_GET['anymarket_action'])
		&& $_GET['anymarket_action'] === 'update_all'
		&& current_user_can('manage_options') ){

			global $wpdb;

			$exportedProducts = $wpdb->get_results(
				$wpdb->prepare(
					"
						SELECT pm.post_id, pm.meta_value, p.post_type
						FROM $wpdb->postmeta pm
						LEFT JOIN $wpdb->posts p
							ON p.ID = pm.post_id
						WHERE pm.meta_key = '%s'
							AND p.post_status = '%s'
							AND p.post_type = '%s'
							AND pm.meta_value <> ''
					"
					  , '_anymarket_id', 'publish', 'product' )
			);

			$product_ids = array_map( function($value){
				return (int)$value->post_id;
			}, $exportedProducts);

			$update_args = [
				'stock' => isset( $_GET['stock'] ) ? $_GET['stock'] : false,
				'price' => isset( $_GET['price'] ) ? $_GET['price'] : false,
				'images' => isset( $_GET['images'] ) ? $_GET['images'] : false,
			];

			if(get_option( 'anymarket_use_cron' ) == 'true'){

				$this->cron->setCronBulkExportProd( 5, [$product_ids, true, $update_args] );

			} else{
				$exportProducts = new ExportProducts();
				$response = $exportProducts->export( $product_ids, true, $update_args );

				if( is_wp_error($response) ){
					set_transient( 'anymarket_product_export_fail', $response->get_error_message(), MINUTE_IN_SECONDS );
				} else{
					set_transient( 'anymarket_product_export_result', $response, MINUTE_IN_SECONDS );
				}
			}

			wp_safe_redirect( remove_query_arg( 'anymarket_action' ) );
		}

		return;
	}
}
