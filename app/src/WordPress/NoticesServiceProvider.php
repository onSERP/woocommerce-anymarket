<?php

namespace Anymarket\WordPress;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Register admin notices
 */
class NoticesServiceProvider implements ServiceProviderInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function register( $container){
		//nothing to register
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ){

		add_action( 'admin_notices', [$this, 'productExportNotice'] );

		add_action( 'admin_notices', [$this, 'categoryExportNotice'] );

		if ( defined( 'ANYMARKET_BRAND_CPT' ) ){
			add_action( 'admin_notices', [$this, 'brandExportNotice'] );
		}

		add_action( 'admin_notices', [$this, 'productDeleteNotice'] );

		add_action( 'admin_notices', [$this, 'categoryDeleteNotice'] );

		add_action( 'admin_notices', [$this, 'variationExportNotice'] );

		add_action( 'admin_notices', [$this, 'productNotices'] );
	}

	/**
	 * Add admin notices for anymarket bulk actions
	 *
	 * @return void
	 */
	public function productExportNotice(){
		$report = get_transient( 'anymarket_product_export_result' );

		if( !empty($report) ):

			echo '<div class="updated notice is-dismissible"><p>' ;

			foreach ($report as $item) {
				if( empty($item['errorCode']) ){
					printf( __('Produto <b>%s</b> exportado com sucesso.', 'anymarket'), $item['name'] );
					print("<br/>");
				} else{
					if( $item['errorMessage'] == 'Amount must not be null' ){
						printf( __('O produto <b>%1$s</b> falhou na exportação. Você precisa ativar o gerenciamento de estoque no produto.', 'anymarket'), $item['name'] );
					} else {
						printf( __('O produto <b>%1$s</b> falhou na exportação. Mensagem do erro: %2$s.', 'anymarket'), $item['name'], $item['errorMessage'] );
					}


					if ($item['errorCode'] === 404){
						echo ' ';
						_e('Se você excluiu este produto no Anymarket, você deverá desativar e reativar a integração do produto novamente', 'anymarket');
					}

					print("<br/>");
				}
			}

			echo '</p></div>';
			return;
		endif;
	}

	/**
	 * Add admin notices for anymarket bulk actions
	 *
	 * @return void
	 */
	public function categoryExportNotice(){
		$report = get_transient( 'anymarket_category_export_result' );

		if( !empty($report) ):

			echo '<div class="updated notice is-dismissible"><p>' ;

			foreach ($report as $item) {
				if( empty($item['errorCode']) ){
					printf( __('Categoria <b>%s</b> exportada com sucesso.', 'anymarket'), $item['name'] );
					print("<br/>");
				} else{
					printf( __('A categoria <b>%1$s</b> falhou na exportação. Código do erro: %2$s.', 'anymarket'), $item['name'], $item['errorCode'] );

					if ($item['errorCode'] === 404){
						echo ' ';
						_e('Se você excluiu esta categoria no Anymarket, você deverá recriá-la no Woocommerce para refazer a integração', 'anymarket');
					}

					print("<br/>");
				}
			}

			echo '</p></div>';
			return;
		endif;
	}

	/**
	 * Add admin notices for anymarket category deletion
	 *
	 * @return void
	 */
	public function brandExportNotice(){
		$report = get_transient( 'anymarket_brand_export_result' );

		if( !empty($report) ):

			echo '<div class="updated notice is-dismissible"><p>' ;

			foreach ($report as $item) {
				if( empty($item['errorCode']) ){
					printf( __('Marca <b>%s</b> exportada com sucesso.', 'anymarket'), $item['name'] );
					print("<br/>");
				} else{
					printf( __('A marca <b>%1$s</b> falhou na exportação. Código do erro: %2$s.', 'anymarket'), $item['name'], $item['errorCode'] );

					if ($item['errorCode'] === 404){
						echo ' ';
						_e('Se você excluiu esta marca no Anymarket, você deverá recriá-la no Woocommerce para refazer a integração', 'anymarket');
					}

					print("<br/>");
				}
			}

			echo '</p></div>';
			return;
		endif;
	}

	/**
	 * Add admin notices for anymarket category deletion
	 *
	 * @return void
	 */
	public function productDeleteNotice(){
		$report = get_transient( 'anymarket_remove_integration_product_done' );

		if( !empty($report) ):
			echo '<div class="updated notice is-dismissible"><p>' ;

			foreach ($report as $item) {
					printf( __('Produto <b>%s</b> removido da integração com sucesso.', 'anymarket'), $item->post_title );
					print("<br/>");
			}

			echo '</p></div>';
			return;
		endif;
	}


	/**
	 * Add admin notices for anymarket category deletion
	 *
	 * @return void
	 */
	public function categoryDeleteNotice(){

		$report = get_transient( 'anymarket_category_delete_error' );

		if( !empty($report) ):
			echo '<div class="updated notice is-dismissible"><p>' ;

			foreach ($report as $item) {
					printf( __('Erro ao remover a categoria <b>%s</b> do anymarket', 'anymarket'), $item['name'] );
					print("<br/>");
			}

			echo '</p></div>';
			return;
		endif;

		$report = get_transient( 'anymarket_category_delete_success' );

		if( !empty($report) ):
			echo '<div class="updated notice is-dismissible"><p>' ;

			foreach ($report as $item) {
					printf( __('Categoria <b>%s</b> removida do anymarket', 'anymarket'), $item['name'] );
					print("<br/>");
			}

			echo '</p></div>';
			return;
		endif;

	}

	/**
	 * Add admin notices for anymarket category deletion
	 *
	 * @return void
	 */
	public function variationExportNotice(){

		$report = get_transient( 'anymarket_variation_type_export_error' );

		if( !empty($report) ):
			echo '<div class="updated notice is-dismissible"><p>' ;

			foreach ($report as $item) {
					printf( __('A variação <b>%1$s</b> falhou na exportação. Mensagem do erro: %2$s.', 'anymarket'), $item['name'], $item['errorMessage'] );
					print("<br/>");
			}

			echo '</p></div>';
			return;
		endif;

		$report = get_transient( 'anymarket_variation_type_export_success' );

		if( !empty($report) ):
			echo '<div class="updated notice is-dismissible"><p>' ;

			foreach ($report as $item) {
					printf( __('Variação <b>%s</b> exportada com sucesso.', 'anymarket'), $item['name'] );
					print("<br/>");
			}

			echo '</p></div>';
			return;
		endif;

		$report = get_transient( 'anymarket_variation_value_export_error' );

		if( !empty($report) ):
			echo '<div class="updated notice is-dismissible"><p>' ;

			foreach ($report as $item) {
					printf( __('O valor de variação <b>%1$s</b> falhou na exportação. Mensagem do erro: %2$s.', 'anymarket'), $item['name'], $item['errorMessage'] );
					print("<br/>");
			}

			echo '</p></div>';
			return;
		endif;

		$report = get_transient( 'anymarket_variation_value_export_success' );

		if( !empty($report) ):
			echo '<div class="updated notice is-dismissible"><p>' ;

			foreach ($report as $item) {
					printf( __('Valor de variação <b>%s</b> exportado com sucesso.', 'anymarket'), $item['name'] );
					print("<br/>");
			}

			echo '</p></div>';
			return;
		endif;

	}

	/**
	 * Create product notices
	 *
	 * @return void
	 */
	public function productNotices(){
		$is_dev = get_option( 'anymarket_is_dev_env' );
		$env = $is_dev === 'true' ? 'sandbox' : 'app';

		$screen = get_current_screen();
		if( 'post' === $screen->base && 'product' === $screen->post_type ){
			echo '<div class="notice is-dismissible notice-warning"><p>';
			echo __('<b>Importante:</b> Antes de adicionar variações você deve criá-las no anymarket.', 'anymarket');
			echo " <a href=\"http://${env}.anymarket.com.br/#/variations/list\" target=\"_blank\"><b>Para criar clique aqui <span class=\"dashicons dashicons-external\"></span></b></a>";
			echo '</p></div>';
		}
	}
}
