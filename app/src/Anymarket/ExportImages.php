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
				'url' => $this->curl->url,
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
				'url' => $this->curl->url,
				'response' => json_encode($this->curl->response, JSON_UNESCAPED_UNICODE),
				'responseCode' => json_encode($this->curl->httpStatusCode, JSON_UNESCAPED_UNICODE)
			];

			$imagesFromAnymarket = $this->curl->response;
		}

		// TODO:match images to avoid making these requests

		//delete images
		$this->multiCurl->error(function ($instance) use (&$report, $product){
			$report[] = [
				'name' => $product->get_name(),
				'id' => $product->get_id(),
				'type' => 'Delete images',
				'url' => $instance->url,
				'errorCode' => $instance->errorCode,
				'errorMessage' => $instance->response->message,
			];

			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket']);
			return false;
		});

		$this->multiCurl->success(function ($instance) use (&$report, $product){
			$report[] = [
				'name' => $product->get_name(),
				'id' => $product->get_id(),
				'type' => 'Delete images',
				'url' => $instance->url,
				'response' => json_encode($instance->response, JSON_UNESCAPED_UNICODE),
				'responseCode' => json_encode($instance->httpStatusCode, JSON_UNESCAPED_UNICODE)
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
				'url' => $instance->url,
				'errorCode' => $instance->errorCode,
				'errorMessage' => $instance->errorMessage,
				'data' => json_encode($instance->data, JSON_UNESCAPED_UNICODE),
			];
		});

		$this->multiCurl->success(function ($instance) use (&$report, $product){
			$report[] = [
				'name' => $product->get_name(),
				'id' => $product->get_id(),
				'type' => 'Create images',
				'data' => json_encode($instance->data, JSON_UNESCAPED_UNICODE),
				'url' => $instance->url,
				'response' => json_encode($instance->response, JSON_UNESCAPED_UNICODE),
				'responseCode' => json_encode($instance->httpStatusCode, JSON_UNESCAPED_UNICODE)
			];
		});

		foreach ($imagesFromWP as $imageFromWP){
			$instance = $this->multiCurl->addPost($this->baseUrl . 'products/' . $anymarket_id . '/images', json_encode($imageFromWP, JSON_UNESCAPED_UNICODE) );
			$instance->data = $imageFromWP;
		}

		$this->multiCurl->start();

		if( get_option('anymarket_show_logs') == 'true' ){
			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket'] );
		}

		return $report;
	}
}
