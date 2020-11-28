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
		add_action( 'anymarket_cron_export_products_on_save', [$this, 'exportProd'] );
		add_action( 'anymarket_cron_bulk_export_products', [$this, 'bulkExportProd'] );

		add_action( 'anymarket_cron_export_orders_on_save', [$this, 'exportOrder'], 10 , 2 );
	}

  /**
   * Undocumented function
   *
   * @param integer $time
   * @return void
   */
 	public static function setCronExportProd( int $time, array $args ){
  	  	wp_schedule_single_event( time() + $time, 'anymarket_cron_export_products_on_save', $args );
  	}

  /**
   * Undocumented function
   *
   * @return void
   */
	public function exportProd( $post_id ){
		$exportProducts = new ExportProducts();
		$response = $exportProducts->export( [$post_id] );

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
		wp_schedule_single_event( time() + $time, 'anymarket_cron_bulk_export_products', $args );
	}

  /**
   * Undocumented function
   *
   * @param [type] $object_ids
   * @return void
   */
  	public function bulkExportProd( $object_ids ){
		$exportProducts = new ExportProducts;
			$response = $exportProducts->export( $object_ids );

		if( is_wp_error($response) ){
			set_transient( 'anymarket_export_product_fail', $response->get_error_message(), MINUTE_IN_SECONDS );
		} else{
			set_transient( 'anymarket_product_export_result', $response, MINUTE_IN_SECONDS );
		}
  	}

	public static function setCronExportOrder( int $time, array $args ){
		wp_schedule_single_event( time() + $time, 'anymarket_cron_export_orders_on_save', $args );
	}

	public function exportOrder( $order_id, $new_status ){
		$order = new AnymarketOrder;
		$order->updateStatus( $order_id, $new_status );
	}
}
