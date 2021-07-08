<?php

namespace Anymarket\WordPress;

use Anymarket\Anymarket\ExportProducts;
use Anymarket\Anymarket\AnymarketOrder;

/**
 * Register cron events
 */
class CronEvents
{

	public function init(){
		add_action( 'anymarket_cron_export_products_on_save', [$this, 'exportProd'], 10, 1 );
		add_action( 'anymarket_cron_bulk_export_products', [$this, 'bulkExportProd'], 10, 3 );

		add_action( 'anymarket_cron_export_orders_on_save', [$this, 'exportOrder'], 10 , 2 );
	}

  /**
   * Undocumented function
   *
   * @param integer $time
   * @return void
   */
 	public static function setCronExportProd( int $time, array $args ){
		$log = wc_get_logger();

  	  	wp_schedule_single_event( time() + $time, 'anymarket_cron_export_products_on_save', $args );

		$log->debug( print_r(['cron' => '`setCronExportProd` agendado', 'argumentos' => $args], true), ['source' => 'woocommerce-anymarket-cron']);
  	}

  /**
   * Undocumented function
   *
   * @return void
   */
	public function exportProd( $post_id ){
		$exportProducts = new ExportProducts();
		$log = wc_get_logger();
		$log->debug( print_r([
			'cron' => '`exportProd` executando',
			'argumentos' => ['post id(s)' => $post_id]],
		true), ['source' => 'woocommerce-anymarket-cron']);

		$response = is_array($post_id) ? $exportProducts->export( $post_id ) : $exportProducts->export( [$post_id] );

		if( is_wp_error($response) ){
			set_transient( 'anymarket_product_export_fail', $response->get_error_message(), MINUTE_IN_SECONDS );
		} else{
			set_transient( 'anymarket_product_export_result', $response, MINUTE_IN_SECONDS );
		}
	}

   /**
   * Undocumented function
   *
   * @param integer $time
   * @return void
   */
	public static function setCronBulkExportProd( int $time, array $args ){
		$log = wc_get_logger();

		wp_schedule_single_event( time() + $time, 'anymarket_cron_bulk_export_products', $args );

		$log->debug( print_r(['cron' => '`setCronBulkExportProd` agendado', 'argumentos' => $args], true), ['source' => 'woocommerce-anymarket-cron']);
	}

  /**
   * Undocumented function
   *
   * @param [type] $object_ids
   * @return void
   */
  	public function bulkExportProd( $object_ids, $update = false, $update_args = [] ){
		$exportProducts = new ExportProducts();
		$log = wc_get_logger();
		$log->debug( print_r([
			'cron' => '`bulkExportProd` executando',
			'argumentos' => ['ids' => $object_ids, 'update' => $update, 'update args' => $update_args]],
			true), ['source' => 'woocommerce-anymarket-cron']);


		$response = $exportProducts->export( $object_ids, $update, $update_args );

		if( is_wp_error($response) ){
			set_transient( 'anymarket_export_product_fail', $response->get_error_message(), MINUTE_IN_SECONDS );
		} else{
			set_transient( 'anymarket_product_export_result', $response, MINUTE_IN_SECONDS );
		}
  	}

	public static function setCronExportOrder( int $time, array $args ){
		$log = wc_get_logger();

		wp_schedule_single_event( time() + $time, 'anymarket_cron_export_orders_on_save', $args );

		$log->debug( print_r(['cron' => '`setCronExportOrder` agendado', 'argumentos' => $args], true), ['source' => 'woocommerce-anymarket-cron']);
	}

	public function exportOrder( $order_id, $new_status ){
		$order = new AnymarketOrder();
		$log = wc_get_logger();
		$log->debug( print_r([
			'cron' => '`exportOrder` executando',
			'argumentos' => ['order id' => $order_id, 'new status' => $new_status]],
			true), ['source' => 'woocommerce-anymarket-cron']);

		$order->updateStatus( $order_id, $new_status );
	}
}
