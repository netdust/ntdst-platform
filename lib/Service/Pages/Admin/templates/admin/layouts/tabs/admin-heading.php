<?php
/**
 * Admin Heading Template
 * Default template to render an admin page.
 *
 * @since 1.0.0
 */


use Netdust\Loaders\Admin\Abstracts\AdminPage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $template ) || ! $template instanceof AdminPage ) {
	return;
}

$current = $template->get_param( 'section', '' );
?>
<nav class="nav-tab-wrapper">
	<?php foreach ( $template->get_param( 'sections' ) as $section ): ?>
		<a class="nav-tab<?= $current === $section->id ? ' nav-tab-active' : '' ?>" href="<?= $template->get_section_url( $section->id ) ?>"><?= $template->section( $section->id )->name; ?></a>
	<?php endforeach; ?>
</nav>