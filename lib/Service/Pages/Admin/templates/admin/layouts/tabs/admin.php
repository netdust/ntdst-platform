<?php
/**
 * Admin Page Template
 *
 * @author: Alex Standiford
 * @date  : 12/21/19
 */


use Netdust\Loaders\Admin\Abstracts\AdminPage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $template ) || ! $template instanceof AdminPage ) {
	return;
}

$sections = $template->get_param( 'sections', [] );
$section  = $template->get_param( 'section', '' );


if ( count( $sections ) > 1 ) {
	echo $template->get_template( 'admin-heading', [
		'section'   => $section,
		'sections'  => $sections,
		'menu_slug' => $template->get_param( 'menu_slug' ),
	] );
}

?>
<?php if ( ! empty( $section ) && ! is_wp_error( $template->section( $section ) ) ): ?>

	<form method="post" id="runner-dispatch">
		<h2><?= $template->get_param( 'title', '' ) ?></h2>
		<p style="max-width:700px;"><?= $template->get_param( 'description', '' ) ?></p>
		<table class="form-table">
			<tbody>

			<?= $template->section( $section )->get_template( 'admin-section' ); ?>

			</tbody>
		</table>
		<?php wp_nonce_field( $template->get_param( 'nonce_action', '' ), 'underpin_nonce' ); ?>
		<?php submit_button(); ?>
	</form>

<?php endif; ?>