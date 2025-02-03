<?php
/**
 * Title: footer
 * Slug: santareportcard/footer
 * Inserter: no
 */
?>
<!-- wp:group {"tagName":"footer","layout":{"type":"constrained"}} -->
<footer class="wp-block-group"><!-- wp:group {"className":"container","layout":{"type":"constrained"}} -->
<div class="wp-block-group container"><!-- wp:paragraph -->
<p><?php esc_html_e('© 2025 Santa Report Card. Spreading Christmas joy all year round.', 'santareportcard');?></p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"footer-links","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group footer-links"><!-- wp:paragraph -->
<p><?php /* Translators: 1. is the start of a 'a' HTML element, 2. is the end of a 'a' HTML element */ 
echo sprintf( esc_html__( '%1$sPrivacy Policy%2$s', 'santareportcard' ), '<a href="' . esc_url( '#' ) . '">', '</a>' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php /* Translators: 1. is the start of a 'a' HTML element, 2. is the end of a 'a' HTML element */ 
echo sprintf( esc_html__( '%1$sTerms of Service%2$s', 'santareportcard' ), '<a href="' . esc_url( '#' ) . '">', '</a>' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></footer>
<!-- /wp:group -->