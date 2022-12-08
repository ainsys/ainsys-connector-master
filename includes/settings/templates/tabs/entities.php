<?php
/**
 * Settings Entities Tab
 *
 * @package ainsys
 *
 * @global $args
 */
$admin_ui = $args['admin_ui'];
$active = $args['active'];

?>

<div id="setting-section-entities" class="tab-target">
	<?php echo $admin_ui->generate_entities_html(); ?>

	<p><?php _e( 'Detailed', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?> <a href="https://github.com/ainsys/ainsys-wp-connector"> <?php _e( ' API integration', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></a> <?php _e( ' documentation.', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore ?></p>
</div>
