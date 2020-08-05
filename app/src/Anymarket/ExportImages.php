<?php

namespace Anymarket\Anymarket;

/**
 * Export images
 */
class ExportImages extends ExportService
{
	public function export( \WC_Product $product, string $anymarket_id ){
		sleep(1);

		$report = [];

		$imagesFromWP = $this->formatProductImages( $product );

		//get images
		$imagesFromAnymarket;
		$this->curl->get($this->baseUrl . 'products/' . $anymarket_id . '/images');
		if( $this->curl->error ){
			$report[] = [
				'name' => $product->get_name(),
				'id' => $product->get_id(),
				'type' => 'Get images',
				'errorCode' => $this->curl->errorCode,
				'errorMessage' => $this->curl->errorMessage,
			];

			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket']);
			return false;
		} else {
			$report[] = [
				'name' => $product->get_name(),
				'id' => $product->get_id(),
				'type' => 'Get images',
				'response' => json_encode($this->curl->response, JSON_UNESCAPED_UNICODE)
			];

			$imagesFromAnymarket = $this->curl->response;
		}

		//delete images
		$this->multiCurl->error(function ($instance) use (&$report, $product){
			$report[] = [
				'name' => $product->get_name(),
				'id' => $product->get_id(),
				'type' => 'Delete images',
				'errorCode' => $this->multiCurl->errorCode,
				'errorMessage' => $this->multiCurl->errorMessage,
			];

			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket']);
			return false;
		});

		$this->multiCurl->success(function ($instance) use (&$report, $product){
			$report[] = [
				'name' => $product->get_name(),
				'id' => $product->get_id(),
				'type' => 'Delete images',
				'response' => json_encode($this->multiCurl->response, JSON_UNESCAPED_UNICODE)
			];
		});

		foreach ($imagesFromAnymarket as $imageFromAnymarket){
			$this->multiCurl->addDelete($this->baseUrl . 'products/' . $anymarket_id . '/images' . '/' . $imageFromAnymarket->id );
		}

		$this->multiCurl->start();

		sleep(1);

		//create new images
		$this->multiCurl->error(function ($instance) use (&$report, $product){
			$report[] = [
				'name' => $product->get_name(),
				'id' => $product->get_id(),
				'type' => 'Create images',
				'errorCode' => $this->multiCurl->errorCode,
				'errorMessage' => $this->multiCurl->errorMessage,
			];
		});

		$this->multiCurl->success(function ($instance) use (&$report, $product){
			$report[] = [
				'name' => $product->get_name(),
				'id' => $product->get_id(),
				'type' => 'Create images',
				'data' => $instance->data,
				'response' => json_encode($this->multiCurl->response, JSON_UNESCAPED_UNICODE)
			];
		});

		foreach ($imagesFromWP as $imageFromWP){
			$instance = $this->multiCurl->addPost($this->baseUrl . 'products/' . $anymarket_id . '/images', json_encode($imageFromWP, JSON_UNESCAPED_UNICODE) );
			$instance->data = $imageFromWP;
		}

		$this->multiCurl->start();

		if( get_option('anymarket_is_dev_env') == 'true' ){
			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket'] );
		}

		return $report;
	}
}
