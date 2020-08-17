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

		if ( count($skusFromWP) > 1 ){
			//get skus
			$skusFromAnymarket;
			$this->curl->get($this->baseUrl . 'products/' . $anymarket_id . '/skus');
			if( $this->curl->error ){
				$report[] = [
					'name' => $product->get_name(),
					'product_id' => $product->get_id(),
					'type' => 'Get skus',
					'url' => $this->curl->url,
					'errorCode' => $this->curl->errorCode,
					'errorMessage' => $this->curl->response->message,
				];

				$this->logger->error( print_r($report, true), ['source' => 'woocommerce-anymarket']);
				return false;

			} else {
				$report[] = [
					'name' => $product->get_name(),
					'product_id' => $product->get_id(),
					'type' => 'Get skus',
					'url' => $this->curl->url,
					'response' => json_encode($this->curl->response, JSON_UNESCAPED_UNICODE),
					'responseCode' => $this->curl->httpStatusCode
				];

				$skusFromAnymarket = $this->curl->response;
			}

			$this->multiCurl->error(function ($instance) use (&$report, $product){
				$report[] = [
					'name' => $product->get_name(),
					'product_id' => $product->get_id(),
					'sku_id' => $instance->data['internalId'],
					'type' => $instance->type,
					'url' => $instance->url,
					'errorCode' => $instance->errorCode,
					'errorMessage' => $instance->response->message,
					'data' => json_encode($instance->data, JSON_UNESCAPED_UNICODE)
				];
			});

			$this->multiCurl->success(function ($instance) use (&$report, $product){
				$report[] = [
					'name' => $product->get_name(),
					'product_id' => $product->get_id(),
					'sku_id' => $instance->data['internalId'],
					'type' => $instance->type,
					'data' => json_encode($instance->data, JSON_UNESCAPED_UNICODE),
					'url' => $instance->url,
					'response' => json_encode($instance->response, JSON_UNESCAPED_UNICODE),
					'responseCode' => $instance->httpStatusCode
				];
			});

			//update existing skus
			foreach( $skusFromAnymarket as $skuFromAnymarket){
				$skuData;
				foreach( $skusFromWP as $skuFromWP){
					$anymarketSkuId = get_post_meta( $skuFromWP['internalId'], 'anymarket_variation_id', true);

					if( $anymarketSkuId == $skuFromAnymarket->id ){
						$skuData = $skuFromWP;
						unset($skuData['amount']);
						break;
					}
				}

				//update skus
				$instance = $this->multiCurl->addPut($this->baseUrl . 'products/' . $anymarket_id . '/skus' . '/' . $skuFromAnymarket->id, $skuData );
				$instance->data = $skuData;
				$instance->type = 'Update skus';
			}


			//create new skus
			$unexistentSkusOnAnymarket = [];

			foreach( $skusFromWP as $skuFromWP ){
				$thisSkuId = get_post_meta( $skuFromWP['internalId'], 'anymarket_variation_id', true);
				if ( empty($thisSkuId) ){
					$unexistentSkusOnAnymarket[] = $skuFromWP;
				}
			}

			if ( !empty($unexistentSkusOnAnymarket) ){
				foreach( $unexistentSkusOnAnymarket as $unexistentSkuOnAnymarket ){
					$instance = $this->multiCurl->addPost($this->baseUrl . 'products/' . $anymarket_id . '/skus', $unexistentSkuOnAnymarket );
					$instance->data = $unexistentSkuOnAnymarket;
					$instance->type = 'Create sku';
				}
			}


			$this->multiCurl->start();

		} else {

			$currentSKU = $skusFromWP[0];
			unset($currentSKU['amount']);

			//update current sku
			$skuInAnymarket = carbon_get_post_meta( $currentSKU['internalId'], 'anymarket_variation_id');

			$this->curl->put($this->baseUrl . 'products/' . $anymarket_id . '/skus' . '/' . $skuInAnymarket, $currentSKU);

			if( $this->curl->error ){
				$report[] = [
					'name' => $product->get_name(),
					'product_id' => $product->get_id(),
					'type' => 'Update sku',
					'url' => $this->curl->url,
					'data' => json_encode($currentSKU, JSON_UNESCAPED_UNICODE),
					'errorCode' => $this->curl->errorCode,
					'errorMessage' => $this->curl->response->message,
				];

				$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket']);
				return false;

			} else {
				$report[] = [
					'name' => $product->get_name(),
					'product_id' => $product->get_id(),
					'type' => 'Update sku',
					'url' => $this->curl->url,
					'data' => json_encode($currentSKU, JSON_UNESCAPED_UNICODE),
					'response' => json_encode($this->curl->response, JSON_UNESCAPED_UNICODE),
					'responseCode' => $this->curl->httpStatusCode
				];
			}
		}

		if( get_option('anymarket_is_dev_env') == 'true' ){
			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket'] );
		}

		return $report;
	}
}
