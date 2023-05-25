<?php
/**
 * Admin Page Template
 *
 * @author: Alex Standiford
 * @date  : 12/21/19
 */


use Netdust\Service\Pages\Admin\AdminPage;
use Netdust\Service\Pages\Admin\AdminSection;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $template ) || ! $template instanceof AdminPage ) {
	return;
}

$sections = $template->get_param( 'sections', [] );

?>
<form method="post" id="runner-dispatch">
    <h2><?= $template->get_param( 'title', '' ) ?></h2>
    <p style="max-width:700px;"><?= $template->get_param( 'description', '' ) ?></p>
    <table class="form-table">
    <tbody>
<?php if ( ! empty( $sections ) ): ?>

			<?php foreach ( $sections as $section ): ?>
				<?php if ( $section instanceof AdminSection ): ?>
					<?= $section->get_template( 'admin-section' ); ?>
				<?php endif; ?>
			<?php endforeach; ?>

<?php endif; ?>
    </tbody>
    </table>

    <?php wp_nonce_field( $template->get_param( 'nonce_action', '' ), 'underpin_nonce' ); ?>
    <?php submit_button(); ?>
</form>
