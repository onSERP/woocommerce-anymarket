<?php
/**
 * An example view.
 *
 * Layout: layouts/example.php
 *
 * @package Anymarket
 */

?>
<div class="anymarket__view">
	<?php \Anymarket::render( 'partials/example', [ 'message' => __( 'Hello World!', 'anymarket' ) ] ); ?>
</div>
