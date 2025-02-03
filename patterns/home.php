<?php
/**
 * Title: home
 * Slug: santareportcard/home
 * Inserter: no
 */
?>
<!-- wp:group {"metadata":{"categories":["header","wireframe"],"patternName":"core/simple-header-with-tagline","name":"Simple header with tagline"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","right":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30"}},"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}}},"textColor":"contrast","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-contrast-color has-text-color has-link-color" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--30)"><!-- wp:group {"align":"wide","layout":{"type":"flex","justifyContent":"space-between","flexWrap":"wrap"}} -->
<div class="wp-block-group alignwide"><!-- wp:group {"style":{"spacing":{"blockGap":"24px"}},"layout":{"type":"flex"}} -->
<div class="wp-block-group"><!-- wp:site-logo /-->

<!-- wp:group {"style":{"spacing":{"blockGap":"4px"}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:site-title {"style":{"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}}}} /-->

<!-- wp:site-tagline {"fontSize":"small"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:navigation {"ref":4,"fontSize":"small","layout":{"type":"flex","setCascadingProperties":true,"justifyContent":"right"}} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"tagName":"main","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="margin-top:var(--wp--preset--spacing--60)"><!-- wp:heading {"textAlign":"left","level":1} -->
<h1 class="wp-block-heading has-text-align-left"><?php esc_html_e('Blog', 'santareportcard');?></h1>
<!-- /wp:heading -->

<!-- wp:query {"queryId":0,"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true,"taxQuery":null,"parents":[]},"align":"full","layout":{"type":"default"}} -->
<div class="wp-block-query alignfull"><!-- wp:post-template {"align":"full","layout":{"type":"default"}} -->
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"3/2"} /-->

<!-- wp:post-title {"isLink":true,"fontSize":"x-large"} /-->

<!-- wp:post-content {"align":"full","fontSize":"medium","layout":{"type":"constrained"}} /-->

<!-- wp:post-date {"isLink":true,"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}},"fontSize":"small"} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p><?php esc_html_e('Sorry, but nothing was found. Please try a search with different keywords.', 'santareportcard');?></p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide"><!-- wp:query-pagination {"paginationArrow":"arrow","align":"wide","layout":{"type":"flex","justifyContent":"space-between"}} -->
<!-- wp:query-pagination-previous /-->

<!-- wp:query-pagination-numbers /-->

<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination --></div>
<!-- /wp:group --></div>
<!-- /wp:query --></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer"} /-->