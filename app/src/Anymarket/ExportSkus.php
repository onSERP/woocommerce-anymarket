<?php

namespace Anymarket\Anymarket;

/**
 * Export skus
 */
class ExportSkus extends ExportService
{
	public function export( \WC_Product $product, string $anymarket_id ){
		$report = [];

		$skusFromWP = $this->formatProductVariations( $product );

		//get skus
		$skusFromAnymarket;
		$this->curl->get($this->baseUrl . 'products/' . $anymarket_id . '/skus');
		if( $this->curl->error ){
			$report[] = [
				'name' => $product->get_name(),
				'product_id' => $product->get_id(),
				'type' => 'Get skus',
				'errorCode' => $this->curl->errorCode,
				'errorMessage' => $this->curl->errorMessage,
			];

			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket']);
			return false;

		} else {
			$report[] = [
				'name' => $product->get_name(),
				'product_id' => $product->get_id(),
				'type' => 'Get skus',
				'response' => json_encode($this->curl->response, JSON_UNESCAPED_UNICODE)
			];

			$skusFromAnymarket = $this->curl->response;
		}

		$this->multiCurl->error(function ($instance) use (&$report, $product){
			$report[] = [
				'name' => $product->get_name(),
				'product_id' => $product->get_id(),
				'sku_id' => $instance->data->internalId,
				'type' => 'Update skus',
				'errorCode' => $this->multiCurl->errorCode,
				'errorMessage' => $this->multiCurl->errorMessage,
				'data' => json_encode($instance->data, JSON_UNESCAPED_UNICODE)
			];
		});

		$this->multiCurl->success(function ($instance) use (&$report, $product){
			$report[] = [
				'name' => $product->get_name(),
				'product_id' => $product->get_id(),
				'sku_id' => $instance->data->internalId,
				'type' => 'Update skus',
				'data' => json_encode($instance->data, JSON_UNESCAPED_UNICODE),
				'response' => json_encode($this->multiCurl->response, JSON_UNESCAPED_UNICODE)
			];
		});

		foreach( $skusFromAnymarket as $skuFromAnymarket){
			$skuData;
			//check skus
			foreach( $skusFromWP as $skuFromWP){

				$anymarketSkuId = get_post_meta( $skuFromWP['internalId'], 'anymarket_variation_id', true);

				if( $anymarketSkuId == $skuFromAnymarket->id ){
					unset($skuFromWP['partnerId']);
					$skuData = $skuFromWP;
					break;
				}
			}

			//update skus
			$instance = $this->multiCurl->addPut($this->baseUrl . 'products/' . $anymarket_id . '/skus' . '/' . $skuFromAnymarket->id, $skuData );
			$instance->data = $skuData;
		}

		$this->multiCurl->start();

		if( get_option('anymarket_is_dev_env') == 'true' ){
			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket'] );
		}

		return $report;
	}
}
