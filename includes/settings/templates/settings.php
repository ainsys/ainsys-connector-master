<?php
/**
 * Settings Main File
 *
 * @package ainsys
 *
 * @global                                            $args
 * @global  Ainsys\Connector\Master\Settings\Admin_UI $this
 */

$settings_nav_tabs     = $this->get_nav_fields();
$settings_content_tabs = $this->get_nav_content_fields();

?>
<div class="wrap ainsys-settings-wrap">
	<h1><img src="<?php echo AINSYS_CONNECTOR_URL; ?>/assets/img/logo.svg" alt="Ainsys logo" class="ainsys-logo"></h1>


	<div class="ainsys-tabs-nav-wrapper">

		<?php do_action( 'ainsys_connector_before_nav_tabs' ); ?>

		<?php foreach ( $settings_nav_tabs as $nav_tab_key => $nav_tab ): ?>
			<a class="nav-tab <?php echo esc_attr( $nav_tab['active'] ) ? 'nav-tab-active' : ''; ?>"
			   href="#setting-section-<?php echo esc_attr( $nav_tab_key ) ?>"
			   data-target="setting-section-<?php echo esc_attr( $nav_tab_key ) ?>"><?php echo esc_html( $nav_tab['label'] ) ?></a>
		<?php endforeach; ?>

		<?php do_action( 'ainsys_connector_after_nav_tabs' ); ?>
	</div>

	<div class="ainsys-tabs-content-wrapper">
		<form method="post" action="options.php">
			<?php

			do_action( 'ainsys_connector_after_content_tabs' );

			foreach ( $settings_content_tabs as $content ):

				load_template(
					AINSYS_CONNECTOR_PLUGIN_DIR . $content['template'],
					false,
					[
						'admin_ui' => $this,
						'active'   => $content['active'],
					]
				);

			endforeach;

			do_action( 'ainsys_connector_after_content_tabs' )

			?>
		</form>

	</div>


</div>

<script>
	jQuery( document ).ready( function ( $ ) {
		$( '#full-uninstall-checkbox' ).on( 'click', function () {
			let val = $( this ).val() === 1 ? 0 : 1
			$( this ).attr( 'value', val )
			$( this ).prop( 'checked', val )
		} );
	} )
</script>
