<?php

namespace Anymarket\Anymarket;


/**
 * Export stuff from wp to anymarket
 */
class ExportService
{
	public function exportAllProducts(){

	}

	public function exportAllCategories(){

	}

	/**
	 * Undocumented function
	 *
	 * @param array $post_ids
	 * @return WP_Error|Boolean
	 */
	public function bulkExportProductsWp( array $post_ids ){

		return 'success';
	}

	/**
	 * Undocumented function
	 *
	 * @param array $post_ids
	 * @return WP_Error|Boolean
	 */
	public function bulkExportCategoriesWp( array $post_ids ){

		return 'success';
	}


}
