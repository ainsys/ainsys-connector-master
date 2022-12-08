<?php
/**
 * Settings Tests Tab
 *
 * @package ainsys
 *
 * @global $args
 */
$admin_ui = $args['admin_ui'];
$active = $args['active'];

?>
<div id="setting-section-test" class="tab-target">
	<div class="ainsys-test-block">
		<?php echo $admin_ui->generate_test_html(); ?>
	</div>
</div>