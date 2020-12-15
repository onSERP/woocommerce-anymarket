<?php

namespace Anymarket\Anymarket;

/**
 * Export variations
 */
class ExportVariations extends ExportService
{
	/**
	 * 1 - get variations that already exists on anymarket
	 *
	 * @return array $variationsToExport
	 */
	public function getVariations(){

		$report = [];
		$variationsFromAnymarket;

		$this->curl->get($this->baseUrl . 'variations');

		if( $this->curl->error ){
			$report[] = [
				'type' => 'Get variations',
				'url' => $this->curl->url,
				'errorCode' => $this->curl->errorCode,
				'errorMessage' => $this->curl->errorMessage,
			];

			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket']);
			return false;

		} else {
			$report[] = [
				'type' => 'Get variations',
				'url' => $this->curl->url,
				'response' => json_encode($this->curl->response, JSON_UNESCAPED_UNICODE),
				'responseCode' => json_encode($this->curl->httpStatusCode, JSON_UNESCAPED_UNICODE)
			];

			$variationsFromAnymarket = $this->curl->response;
		}

		$variationsToExport = [];

		foreach( wc_get_attribute_taxonomies() as $attributeTax ){

			foreach ( $variationsFromAnymarket->content as $variationType ){

				if ( $variationType->name === $attributeTax->attribute_label ){
					$variationsToExport[] = [
						'name' => $attributeTax->attribute_name,
						'anymarket_id' => $variationType->id
					];
				}

			}

		}

		return $variationsToExport;
	}

	/**
	 * 2 - create variation values in variation
	 * types that already exists on anymarket
	 *
	 * @return array $report
	 */
	public function variationValues(){

		$report = [];

		$this->multiCurl->success(function ($instance) use (&$report) {
			$report[] = [
				'name' => $instance->name,
				'type' => $instance->type,
				'data' => json_encode($instance->data, JSON_UNESCAPED_UNICODE),
				'url' => $instance->url,
				'response' => json_encode($instance->response, JSON_UNESCAPED_UNICODE),
				'responseCode' => $instance->httpStatusCode,

			];
		});

		$this->multiCurl->error(function ($instance) use (&$report) {
			$report[] = [
				'name' => $instance->name,
				'type' => $instance->type,
				'data' => json_encode($instance->data, JSON_UNESCAPED_UNICODE),
				'url' => $instance->url,
				'errorCode' => $instance->errorCode,
				'errorMessage' => $instance->response->message,
			];
		});

		foreach ( $this->getVariations() as $variationToExport ){

			$terms = get_terms([
				'taxonomy' => 'pa_' . $variationToExport['name'],
				'hide_empty' => false
			] );

			$url = $this->baseUrl . 'variations/' . $variationToExport['anymarket_id'] . '/values';

			foreach( $terms as $term ){
				if ( $term instanceof \WP_Term ){

					$data = ['description' => $term->name];

					$instance = $this->multiCurl->addPost($url, json_encode($data, JSON_UNESCAPED_UNICODE));
					$instance->name = $term->name;
					$instance->type = 'Create variation item';
					$instance->data = $data;
				}
			}

		}

		$this->multiCurl->start();

		if( get_option('anymarket_show_logs') == 'true' ){
			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket'] );
		}
	}

	/**
	 * create variation types on anymarket
	 *
	 * @return array $report
	 */
	public function variationTypes(){

		$report = [];


		$this->multiCurl->success(function ($instance) use (&$report) {
			$report[] = [
				'name' => $instance->name,
				'type' => 'Create variation types',
				'data' => json_encode($instance->data, JSON_UNESCAPED_UNICODE),
				'response' => json_encode($instance->response, JSON_UNESCAPED_UNICODE),
				'responseCode' => $instance->httpStatusCode,

			];
		});

		$this->multiCurl->error(function ($instance) use (&$report) {
			$report[] = [
				'name' => $instance->name,
				'type' => 'Create variation types',
				'data' => json_encode($instance->data, JSON_UNESCAPED_UNICODE),
				'errorCode' => $instance->errorCode,
				'errorMessage' => $instance->response->message,
			];
		});

		foreach( wc_get_attribute_taxonomies() as $attributeTax ){

			$terms = get_terms([
				'taxonomy' => 'pa_' . $attributeTax->attribute_name,
				'hide_empty' => false
			]);

			$values = [];

			foreach ($terms as $term_key => $term){
				$values[] = ['description' => $term->name];
			}

			$attribute_has_visual_variation = !empty( get_option( 'attribute_' . $attributeTax->attribute_name . '_has_visual_variation' ) ) ? true : false;

			$data = [
				'name' => $attributeTax->attribute_label,
				'visualVariation' => $attribute_has_visual_variation,
				'values' => $values,
			];

			if ( ! anymarket_in_multidimensional_array( $attributeTax->attribute_name, $this->getVariations() ) ){
				$instance = $this->multiCurl->addPost($this->baseUrl . 'variations' , json_encode($data, JSON_UNESCAPED_UNICODE));
				$instance->name = $data['name'];
				$instance->data = $data;
			}



		}

		$this->multiCurl->start();

		if( get_option('anymarket_show_logs') == 'true' ){
			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket'] );
		}

		return $report;

	}

	public function assignVariationIDs ( $object_id, $meta_value ){
		global $wpdb;

		$report = [];
		$skus;

		$this->curl->get($this->baseUrl . 'products/' . $meta_value . '/skus');

		if( $this->curl->error ){
			$report[] = [
				'type' => 'Get skus ids',
				'url' => $this->curl->url,
				'errorCode' => $this->curl->errorCode,
				'errorMessage' => $this->curl->errorMessage,
			];

			if( get_option('anymarket_show_logs') == 'true' ){
				$this->logger->error( print_r($report, true), ['source' => 'woocommerce-anymarket'] );
			}

			return false;

		} else {
			$report[] = [
				'type' => 'Get skus ids',
				'url' => $this->curl->url,
				'response' => json_encode($this->curl->response, JSON_UNESCAPED_UNICODE),
				'responseCode' => json_encode($this->curl->httpStatusCode, JSON_UNESCAPED_UNICODE)
			];

			if( get_option('anymarket_show_logs') == 'true' ){
				$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket'] );
			}

			$skus = $this->curl->response;
		}

		$skusIDs = array_map( function ( $value ) {
			return [
				'id' => $value->id,
				'sku' => $value->partnerId
			];
		}, $skus);

		foreach ( $skusIDs as $skusID ){

			$product_id = $wpdb->get_var(
				$wpdb->prepare(
					"
					SELECT pm.post_id
					FROM $wpdb->postmeta pm
					LEFT JOIN $wpdb->posts p
						ON p.ID = pm.post_id
					WHERE pm.meta_key = '%s'
						AND p.post_status = '%s'
						AND p.post_type = '%s'
						AND pm.meta_value = '%s'
					"
					, '_sku', 'publish', 'product_variation', $skusID['sku'] )
			);

			update_post_meta( $product_id, 'anymarket_variation_id', $skusID['id'] );

			if( get_option('anymarket_show_logs') == 'true' ){
				$this->logger->debug( $product_id . ' tem sku ' . $skusID['sku'] . ' e recebe o id ' . $skusID['id'], ['source' => 'woocommerce-anymarket'] );
			}

		}
	}

}
