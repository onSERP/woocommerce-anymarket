<?php

namespace Anymarket\Anymarket;

/**
 * Export products
 */
class ExportStock extends ExportService implements ExportInterface
{
	public function export( array $post_ids ){
		echo $order_id;
	}

	public function testVariation( $variation ){
		$this->logger->debug( json_encode($variation, JSON_PRETTY_PRINT), ['source' => 'woocommerce-anymarket']);
	}
}
