<?php

namespace Anymarket\WordPress;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Register and enqueues assets.
 */
class FieldsServiceProvider implements ServiceProviderInterface
{
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
		add_action( 'after_setup_theme', [$this, 'loadCarbonFields'] );

		add_action( 'carbon_fields_register_fields', [$this, 'productsMeta'] );
		add_action( 'carbon_fields_register_fields', [$this, 'ordersMeta'] );

		add_action( 'carbon_fields_register_fields', [$this, 'productCategoriesMeta'] );

		//custom field on product variation - barcode
		add_action( 'woocommerce_variation_options_pricing', [$this, 'addCustomFieldToVariations'], 10, 3 );
		add_action( 'woocommerce_save_product_variation', [$this, 'saveCustomFieldVariations'], 10, 2 );

	}

	/**
	 * Load carbon fields
	 *
	 * @return void
	 */

	public function loadCarbonFields(){
		//Carbon Fields already checks if it has been loaded previously to avoid conflict whith existing versions
		\Carbon_Fields\Carbon_Fields::boot();
	}

	/**
	 * Generates custom fields on admin product page
	 *
	 * @return void
	 */
	public function productsMeta(){
		Container::make( 'post_meta', 'Anymarket' )
			->where( 'post_type', '=', 'product' )
			->set_context( 'side' )
			->add_fields( [

				//condition
				Field::make( 'radio', 'anymarket_should_export', __( 'Integrar com o Anymarket?', 'anymarket' ) )
				->set_options( [
					'true' => __('Sim', 'anymarket'),
					'false' => __('Não', 'anymarket'),
				 ] ),

				//hidden fields - will only use internally
				Field::make( 'hidden', 'exported_to_anymarket'),
				Field::make( 'hidden', 'anymarket_id'),

				Field::make( 'text', 'anymarket_warranty_time', __('Garantia (meses)', 'anymarket') )
					->set_attribute('type', 'number')
					->set_help_text(__('Campo obrigatório para o Anymarket', 'anymarket'))
					->set_conditional_logic( [[
						'field' => 'anymarket_should_export',
            			'value' => 'true'
					]] ),
			]);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function ordersMeta(){
		Container::make( 'post_meta', 'Anymarket' )
			->where( 'post_type', '=', 'shop_order' )
			->set_context( 'side' )
			->add_fields( [
				Field::make( 'text', 'anymarket_order_marketplace', 'Marketplace' )
					->set_attribute('readOnly', 'readonly'),

				//hidden fields - will only use internally
				Field::make( 'hidden', 'anymarket_id'),
				Field::make( 'hidden', 'is_anymarket_order'),
			] );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function productCategoriesMeta(){
		Container::make( 'term_meta', __( 'Anymarket' ) )
			->where( 'term_taxonomy', '=', 'product_cat' )
			->add_fields( [
				Field::make( 'hidden', 'anymarket_id'),
		] );
	}

	public function addCustomFieldToVariations( $loop, $variation_data, $variation ) {
		woocommerce_wp_text_input( [
			'id' => 'anymarket_barcode[' . $loop . ']',
			'class' => 'short',
			'wrapper_class' => 'form-row',
			'label' => __( 'Código de barras', 'anymarket' ),
			'description' => __('Campo obrigatório para o Anymarket', 'anymarket'),
			'value' => get_post_meta( $variation->ID, 'anymarket_barcode', true )
			]
		);
	}

	public function saveCustomFieldVariations( $variation_id, $i ) {
		$anymarket_barcode = $_POST['anymarket_barcode'][$i];
		if ( isset( $anymarket_barcode ) ) update_post_meta( $variation_id, 'anymarket_barcode', esc_attr( $anymarket_barcode ) );
	}
}
