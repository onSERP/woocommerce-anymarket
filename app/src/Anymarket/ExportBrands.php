<?php

namespace Anymarket\Anymarket;

/**
 * Export brands
 */
class ExportBrands extends ExportService implements ExportInterface
{
	/**
	 * Recieves list of categories ids and export them to anymarket.
	 *
	 * @param array $term_ids
	 * @return array $report list of successful or unsuccessful exportations
	 */
	public function export( array $term_ids ){

		$report = [];

		$terms = get_terms([
			'taxonomy' => ANYMARKET_BRAND_CPT,
			'include' => $term_ids,
			'orderby' => 'parent',
			'order' => 'ASC',
			'hide_empty' => false,
		]);


		foreach( $terms as $key => $term ){
			$key === 9 && sleep(1);

			$data = [];
			$data['name'] = $term->name;

			// if category is not on anymarket
			if ( empty( carbon_get_term_meta($term->term_id, 'anymarket_id') ) ){

				// check if category has parent
				if( 0 !== $term->parent ){
					$parent_anymarket = carbon_get_term_meta($term->parent, 'anymarket_id');
					// check if the parent is on anymarket, if not push its 'anymarket id' to data
					if( !empty($parent_anymarket) ) $data['parent']['id'] = $parent_anymarket;
				}

				//make the request
				$this->curl->post($this->baseUrl . 'brands', json_encode($data, JSON_UNESCAPED_UNICODE));
				if($this->curl->error){
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'type' => 'Create brand',
						'errorCode' => $this->curl->errorCode,
						'errorMessage' => $this->curl->errorMessage,
						'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
					];
				} else {
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'type' => 'Create brand',
						'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
					];

					carbon_set_term_meta( $term->term_id, 'anymarket_id', $this->curl->response->id );
				}

			} else {
				$anymarket_id = carbon_get_term_meta($term->term_id, 'anymarket_id');

				//make the request
				$this->curl->put($this->baseUrl . 'brands/' . $anymarket_id, json_encode($data, JSON_UNESCAPED_UNICODE) );
				if($this->curl->error){
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'type' => 'Update brand',
						'errorCode' => $this->curl->errorCode,
						'errorMessage' => $this->curl->response->message,
						'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
					];
				} else {
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'type' => 'Update brand',
						'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
					];
				}
			}
		}

		if( get_option('anymarket_is_dev_env') == 'true' ){
			$this->logger->debug( print_r($report, true) , ['source' => 'woocommerce-anymarket'] );
		}

		return $report;
	}

}
