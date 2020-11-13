<?php

namespace Anymarket\WordPress;

class AnymarketShippingMethod extends \WC_Shipping_Method {

	public $anymarketMethodName;

	public $anymarketMethodCost;

	/**
	 * Constructor for shipping class
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->id                 = 'anyshipping';
		$this->method_title       = __( 'Frete Anymarket', 'anymarket' );
		$this->method_description = __( 'Cálculo de frete para pedidos do Anymarket', 'anymarket' );

		$this->init();

		$this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
		$this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Frete Anymarket', 'anymarket' );
	}

	/**
	 * Init settings
	 *
	 * @access public
	 * @return void
	 */
	public function init() {
		// Load the settings API
		$this->init_form_fields();
		$this->init_settings();

		// Save settings in admin if have any defined
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Define settings field for this shipping
	 * @return void
	 */
	public function init_form_fields() {

		// No fields here

	}

	public function setMethodName( $name ){
		$this->anymarketMethodName = $name;
	}

	public function setMethodCost( $value ){
		$this->anymarketMethodCost = $value;
	}

	/**
	 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
	 *
	 * @access public
	 * @param mixed $package
	 * @return void
	 */
	public function calculate_shipping( $package = [] ) {

		$rate = array(
			'id' => $this->id,
			'label' => $this->anymarketMethodName,
			'cost' => $this->anymarketMethodCost
		);

		$this->add_rate( $rate );

	}
}
