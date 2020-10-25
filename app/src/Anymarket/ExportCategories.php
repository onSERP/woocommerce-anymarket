<?php

namespace Anymarket\Anymarket;

/**
 * Export products
 */
class ExportCategories extends ExportService
{
	/**
	 * Recieves list of categories ids and export them to anymarket.
	 *
	 * @param array $term_ids
	 * @return array $report list of successful or unsuccessful exportations
	 */
	public function export( array $term_ids, bool $update = true ){

		$report = [];

		$terms = get_terms([
			'taxonomy' => 'product_cat',
			'include' => $term_ids,
			'orderby' => 'parent',
			'order' => 'ASC',
			'hide_empty' => false,
		]);


		foreach( $terms as $key => $term ){
			$key === 9 && sleep(1);

			$priceFactor;
			if( empty( carbon_get_term_meta($term->term_id, 'anymarket_category_markup') ) ){
				carbon_set_term_meta($term->term_id, 'anymarket_category_markup', '1');
				$priceFactor = 1;
			} else {
				$priceFactor = carbon_get_term_meta($term->term_id, 'anymarket_category_markup');
				$priceFactor = str_replace(',', '.', $priceFactor);
			}

			$data = [];
			$data['name'] = $term->name;
			$data['priceFactor'] = $priceFactor;

			// if category is not on anymarket
			if ( empty( carbon_get_term_meta($term->term_id, 'anymarket_id') ) ){

				$data['definitionPriceScope'] = 'COST';

				// check if category has parent
				if( 0 !== $term->parent ){
					$parent_anymarket = carbon_get_term_meta($term->parent, 'anymarket_id');
					// check if the parent is on anymarket, if not push its 'anymarket id' to data
					if( !empty($parent_anymarket) ) $data['parent']['id'] = $parent_anymarket;
				}

				//make the request
				$this->curl->post($this->baseUrl . 'categories', json_encode($data, JSON_UNESCAPED_UNICODE));
				if($this->curl->error){
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'type' => 'Create categories',
						'url' => $this->curl->url,
						'errorCode' => $this->curl->errorCode,
						'errorMessage' => $this->curl->errorMessage,
						'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
					];
				} else {
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'type' => 'Create categories',
						'url' => $this->curl->url,
						'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
						'response' => json_encode($this->curl->response, JSON_UNESCAPED_UNICODE),
						'responseCode' => $this->curl->httpStatusCode,
					];

					carbon_set_term_meta( $term->term_id, 'anymarket_id', $this->curl->response->id );
				}

			} else {
				if (false === $update) return;

				$anymarket_id = carbon_get_term_meta($term->term_id, 'anymarket_id');

				if( 0 !== $term->parent ){
					$parent_anymarket = carbon_get_term_meta($term->parent, 'anymarket_id');
					// check if the parent is on anymarket, if not push its 'anymarket id' to data
					if( !empty($parent_anymarket) ) $data['parent']['id'] = $parent_anymarket;
				}

				//make the request
				$this->curl->put($this->baseUrl . 'categories/' . $anymarket_id, json_encode($data, JSON_UNESCAPED_UNICODE) );
				if($this->curl->error){
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'type' => 'Update categories',
						'url' => $this->curl->url,
						'errorCode' => $this->curl->errorCode,
						'errorMessage' => $this->curl->response->message,
						'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
					];
				} else {
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'type' => 'Update categories',
						'url' => $this->curl->url,
						'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
						'response' => json_encode($this->curl->response, JSON_UNESCAPED_UNICODE),
						'responseCode' => $this->curl->httpStatusCode,
					];
				}
			}
		}

		if( get_option('anymarket_show_logs') == 'true' ){
			$this->logger->debug( print_r($report, true) , ['source' => 'woocommerce-anymarket'] );
		}

		return $report;
	}

	public function delete( int $term_id ){

		$report = [];
		$anymarket_id = carbon_get_term_meta($term_id, 'anymarket_id');

		$term = get_term( $term_id );

		$this->curl->delete($this->baseUrl . 'categories/' . $anymarket_id );

		if($this->curl->error){
			$report[] = [
				'name' => $term->name,
				'id' => $term_id,
				'type' => 'Delete category',
				'url' => $this->curl->url,
				'errorCode' => $this->curl->errorCode,
				'errorMessage' => $this->curl->response->message,
			];

			$this->logger->error( print_r($report, true) , ['source' => 'woocommerce-anymarket'] );

			return new \WP_Error('error', $this->curl->response->message);
		} else {
			$report[] = [
				'name' => $term->name,
				'id' => $term->term_id,
				'type' => 'Delete category',
				'url' => $this->curl->url,
				'response' => json_encode($this->curl->response, JSON_UNESCAPED_UNICODE),
				'responseCode' => $this->curl->httpStatusCode,
			];

			carbon_set_term_meta($term_id, 'anymarket_id', '');
		}

		if( get_option('anymarket_show_logs') == 'true' ){
			$this->logger->debug( print_r($report, true) , ['source' => 'woocommerce-anymarket'] );
		}

		return $report;
	}

}
