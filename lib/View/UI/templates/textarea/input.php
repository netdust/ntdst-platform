<?php
/**
 * Text Field Template
 *
 * @author: Alex Standiford
 * @date  : 12/21/19
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! isset( $template ) ) {
    return;
}

$name = $template->get_param( 'name' );

?>

<textarea
    name="<?= $name ?>"
    id="<?= $template->get_id() ?>"

    <?php if ( $template->get_param( 'has_description', false ) ): ?>
        aria-describedby="<?= $name ?>_description"
    <?php endif; ?>

    style="display: block;"
    class="<?= $template->get_param( 'class', 'regular-text' ) ?>"
    <?= $template->attributes( [
        'rows',
        'cols',
        'autofocus',
        'dirname',
        'disabled',
        'form',
        'maxlength',
        'placeholder',
        'readonly',
        'required',
        'wrap',
    ] ); ?>

><?= $template->get_field_value(); ?></textarea>
