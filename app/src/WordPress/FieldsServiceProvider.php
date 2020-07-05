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
		add_action('carbon_fields_register_fields', [$this, 'productsMeta']);
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		add_action( 'after_setup_theme', [$this, 'loadCarbonFields'] );
	}

	/**
	 * Load carbon fields
	 *
	 * @return void
	 */

	public function loadCarbonFields(){
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

				//anymarket fields
				Field::make( 'text', 'anymarket_barcode', __('Código de barras', 'anymarket') )
					->set_help_text(__('Campo obrigatório para o Anymarket', 'anymarket'))
					->set_conditional_logic( [[
						'field' => 'anymarket_should_export',
            			'value' => 'true'
					]] ),
				Field::make( 'text', 'anymarket_warranty_time', __('Garantia (meses)', 'anymarket') )
					->set_attribute('type', 'number')
					->set_conditional_logic( [[
						'field' => 'anymarket_should_export',
            			'value' => 'true'
					]] ),
				Field::make( 'html', 'anymarket_export_button')
					->set_html('<a class="button button-primary right">Exportar para o Anymarket</a>')
					->set_conditional_logic( [[
						'field' => 'anymarket_should_export',
            			'value' => 'true'
					]] )
			]);
	}
}
