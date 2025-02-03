<?php
/**
 * Title: front-page
 * Slug: santareportcard/front-page
 * Inserter: no
 */
?>
<!-- wp:template-part {"slug":"header","area":"header"} /-->

<!-- wp:group {"tagName":"main","className":"hero container hero-content is-style-default","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<main class="wp-block-group hero container hero-content is-style-default"><!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group"><!-- wp:group {"tagName":"main","className":"hero","layout":{"type":"constrained","contentSize":""}} -->
<main class="wp-block-group hero"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"style":{"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"}}}} -->
<h2 class="wp-block-heading" style="margin-top:0;margin-right:0;margin-bottom:0;margin-left:0"><?php /* Translators: 1. is the start of a 'span' HTML element, 2. is the end of a 'span' HTML element */ 
echo sprintf( esc_html__( 'Help Keep Kids on %1$sSanta\'s Nice List%2$s', 'santareportcard' ), '<span>', '</span>' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e('Track behavior, set goals, and spread Christmas joy all year round with our interactive Santa Report Card system.', 'santareportcard');?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"width":50,"style":{"color":{"background":"#dc2626"},"spacing":{"padding":{"left":"0","right":"0","top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}},"border":{"width":"0px","style":"none","radius":"0px"}},"fontSize":"large"} -->
<div class="wp-block-button has-custom-width wp-block-button__width-50 has-custom-font-size has-large-font-size"><a class="wp-block-button__link has-background wp-element-button" style="border-style:none;border-width:0px;border-radius:0px;background-color:#dc2626;padding-top:var(--wp--preset--spacing--20);padding-right:0;padding-bottom:var(--wp--preset--spacing--20);padding-left:0"><?php esc_html_e('Join the Waitlist', 'santareportcard');?></a></div>
<!-- /wp:button -->

<!-- wp:button {"width":50,"className":"is-style-outline","style":{"border":{"color":"#dc2626","style":"solid","width":"3px","radius":"0px"},"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}}} -->
<div class="wp-block-button has-custom-width wp-block-button__width-50 is-style-outline"><a class="wp-block-button__link has-border-color wp-element-button" style="border-color:#dc2626;border-style:solid;border-width:3px;border-radius:0px;padding-top:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20)"><?php esc_html_e('Learn More', 'santareportcard');?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></main>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:image {"scale":"cover","sizeSlug":"large","linkDestination":"none","style":{"spacing":{"margin":{"top":"0","bottom":"0"}},"shadow":"var:preset|shadow|natural","border":{"radius":"15px"}}} -->
<figure class="wp-block-image size-large has-custom-border" style="margin-top:0;margin-bottom:0"><img src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/images/Designer-79-1-1024x585.jpeg" alt="" class="" style="border-radius:15px;box-shadow:var(--wp--preset--shadow--natural);object-fit:cover"/></figure>
<!-- /wp:image --></main>
<!-- /wp:group -->

<!-- wp:columns {"className":"features container"} -->
<div class="wp-block-columns features container"><!-- wp:column {"className":"feature-card"} -->
<div class="wp-block-column feature-card"><!-- wp:image -->
<figure class="wp-block-image"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:heading {"level":3,"className":" h3 "} -->
<h3 class="wp-block-heading  h3"><?php esc_html_e('Coming this winter', 'santareportcard');?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"medium"} -->
<p class="has-medium-font-size"><?php esc_html_e('Get ready for a magical experience that helps children stay on track for Santa\'s nice list.', 'santareportcard');?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"feature-card"} -->
<div class="wp-block-column feature-card"></div>
<!-- /wp:column -->

<!-- wp:column {"className":"feature-card"} -->
<div class="wp-block-column feature-card"></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->