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

		if ( defined('ANYMARKET_BRAND_CPT') ) {
			add_action( 'carbon_fields_register_fields', [$this, 'productBrandsMeta'] );
		}
		//custom field on simple product - barcode
		add_action( 'woocommerce_product_options_general_product_data', [$this, 'addCustomFieldToSimpleProduct'] );
		add_action( 'woocommerce_process_product_meta', [$this, 'saveCustomFieldToSimpleProduct'], 10, 2 );

		//custom field on product variation - barcode and anymarket id
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
					'false' => __('Não', 'anymarket'),
					'true' => __('Sim', 'anymarket'),
				 ] ),

				//hidden fields - will only use internally
				Field::make( 'hidden', 'anymarket_id'),
				Field::make( 'hidden', 'anymarket_variation_id'),

				Field::make( 'text', 'anymarket_warranty_time', __('Garantia (meses)', 'anymarket') )
					->set_attribute('type', 'number')
					->set_attribute('min', '0')
					->set_attribute('step', 'any')
					->set_help_text(__('Campo obrigatório para o Anymarket', 'anymarket'))
					->set_conditional_logic( [[
						'field' => 'anymarket_should_export',
            			'value' => 'true'
					]] ),

				Field::make( 'text', 'anymarket_model', __('Modelo', 'anymarket') )
					->set_help_text(__('Campo obrigatório para o Anymarket.', 'anymarket'))
					->set_conditional_logic( [[
						'field' => 'anymarket_should_export',
            			'value' => 'true'
					]] ),

				Field::make( 'text', 'anymarket_markup', __('Markup', 'anymarket') )
					->set_attribute('type', 'number')
					->set_attribute('min', '0')
					->set_attribute('step', 'any')
					->set_help_text(__('Campo obrigatório para o Anymarket. Se não preenchido, será enviado "1"', 'anymarket'))
					->set_conditional_logic( [[
						'field' => 'anymarket_should_export',
            			'value' => 'true'
					]] ),

				Field::make( 'select', 'anymarket_definition_price_scope', __('Cálculo de preço', 'anymarket') )
					->set_options([
						'COST' => __('Automático, pela mudança do custo', 'anymarket'),
						'SKU' => __('Manual, eu controlo o preço pelo SKU', 'anymarket'),
						'SKU_MARKETPLACE' => __('Manual, eu controlo o preço pelo anúncio', 'anymarket'),
					])
					->set_conditional_logic( [[
						'field' => 'anymarket_should_export',
						'value' => 'true'
					]] ),
			]);
	}

	/**
	 * Create fields on Orders
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

				Field::make( 'separator', 'faturado', __( 'Status: Faturado', 'anymarket' ) ),
				Field::make( 'text', 'anymarket_nfe_access_key', __('Chave de acesso da NF', 'anymarket')),
				Field::make( 'text', 'anymarket_nfe_series', __('Número de série', 'anymarket'))
					->set_attribute('type', 'number')
					->set_attribute('min', '0')
					->set_attribute('step', 'any'),
				Field::make( 'text', 'anymarket_nfe_number', __('Número da nota', 'anymarket'))
					->set_attribute('type', 'number')
					->set_attribute('min', '0')
					->set_attribute('step', 'any'),
				Field::make( 'date_time', 'anymarket_nfe_datetime', __('Data de emissão da NF', 'anymarket')),
				Field::make( 'text', 'anymarket_nfe_cfop', __('Código Fiscal de Operações e Prestações', 'anymarket')),
				Field::make( 'text', 'anymarket_nfe_link', __('Link da nota', 'anymarket')),
				Field::make( 'text', 'anymarket_nfe_link_xml', __('Link do PDF ou XML da nota', 'anymarket')),
				Field::make( 'textarea', 'anymarket_nfe_extra_description', __('Observações da nota', 'anymarket')),
				Field::make( 'file', 'anymarket_nfe_xml', __('XML (Arquivo)', 'anymarket'))
					->set_value_type( 'url' )
					->set_type( ['application/xml',
								'text/xml',
								'application/xhtml+xml',
								'application/atom+xml'
					] ),

				Field::make( 'separator', 'enviado', __( 'Status: Enviado', 'anymarket' ) ),
				Field::make( 'text', 'anymarket_tracking_url', __('URL de rastreamento', 'anymarket')),
				Field::make( 'text', 'anymarket_tracking_number', __('Código de rastreamento', 'anymarket')),
				Field::make( 'text', 'anymarket_tracking_carrier', __('Transportadora', 'anymarket')),
				Field::make( 'text', 'anymarket_tracking_carrier_document', __('CNPJ da transportadora', 'anymarket')),
				Field::make( 'date', 'anymarket_tracking_estimate', __('Estimativa de entrega', 'anymarket')),
				Field::make( 'date', 'anymarket_tracking_shipped', __('Data em que foi entregue à transportadora', 'anymarket')),

				Field::make( 'date', 'anymarket_tracking_delivered', __('Data em que foi entregue ao cliente', 'anymarket')),

			] );
	}

	/**
	 * Create fields on product categories
	 *
	 * @return void
	 */
	public function productCategoriesMeta(){
		Container::make( 'term_meta', __( 'Campos do Anymarket', 'anymarket' ) )
			->where( 'term_taxonomy', '=', 'product_cat' )
			->add_fields( [
				Field::make( 'text', 'anymarket_category_markup', 'Markup' )
					->set_attribute('type', 'number')
					->set_attribute('min', '0')
					->set_attribute('step', 'any')
					->set_help_text(__('Obrigatório para o Anymarket. Se não específicado será enviado como "1"', 'anymarket') ),
				Field::make( 'text', 'anymarket_id')
					->set_attribute('readOnly', 'readonly'),
				Field::make( 'html', 'anymarket_delete_category' )
					->set_html('<a id="button-delete-category" class="button anymarket-button-delete" href="'. esc_url( add_query_arg( [
						'anymarket_action' => 'delete_category'
					] ) ) .'">Deletar categoria no Anymarket</a>')
		] );
	}

	/**
	 * Create fields on product brands
	 *
	 * @return void
	 */
	public function productBrandsMeta(){
		Container::make( 'term_meta', __( 'Anymarket', 'anymarket' ) )
			->where( 'term_taxonomy', '=', ANYMARKET_BRAND_CPT )
			->add_fields( [
				Field::make( 'hidden', 'anymarket_id'),
		] );
	}


	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function addCustomFieldToSimpleProduct() {
		woocommerce_wp_text_input( [
			'id' => 'anymarket_simple_barcode',
			'class' => 'short',
			'desc_tip' => true,
			'label' => __( 'Código de barras', 'anymarket' ),
			'description' => __('Campo obrigatório para o Anymarket', 'anymarket'),
		] );
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $id
	 * @param [type] $post
	 * @return void
	 */
	public function saveCustomFieldToSimpleProduct( $id, $post ) {
		$anymarket_barcode = $_POST['anymarket_simple_barcode'];
		if( isset( $anymarket_barcode ) )  update_post_meta( $id, 'anymarket_simple_barcode', esc_attr( $anymarket_barcode ) );
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $loop
	 * @param [type] $variation_data
	 * @param [type] $variation
	 * @return void
	 */
	public function addCustomFieldToVariations( $loop, $variation_data, $variation ) {
		woocommerce_wp_text_input( [
			'id' => 'anymarket_variable_barcode[' . $loop . ']',
			'class' => 'short',
			'wrapper_class' => 'form-row form-row-first',
			'label' => __( 'Código de barras', 'anymarket' ),
			'description' => __('Campo obrigatório para o Anymarket', 'anymarket'),
			'value' => get_post_meta( $variation->ID, 'anymarket_variable_barcode', true )
			]
		);

		woocommerce_wp_text_input( [
			'id' => 'anymarket_variation_id[' . $loop . ']',
			'class' => 'short disabled',
			'custom_attributes' => ['readonly' => 'readonly'],
			'wrapper_class' => 'form-row form-row-last',
			'label' => __( 'ID do SKU no Anymarket', 'anymarket' ),
			'value' => get_post_meta( $variation->ID, 'anymarket_variation_id', true )
			]
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $variation_id
	 * @param [type] $i
	 * @return void
	 */
	public function saveCustomFieldVariations( $variation_id, $i ) {
		$anymarket_barcode = $_POST['anymarket_variable_barcode'][$i];
		if ( isset( $anymarket_barcode ) ) update_post_meta( $variation_id, 'anymarket_variable_barcode', esc_attr( $anymarket_barcode ) );

		$anymarket_variation_id = $_POST['anymarket_variation_id'][$i];
		if ( isset( $anymarket_variation_id ) ) update_post_meta( $variation_id, 'anymarket_variation_id', esc_attr( $anymarket_variation_id ) );
	}
}