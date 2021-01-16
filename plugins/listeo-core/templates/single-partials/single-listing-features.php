<!-- Features -->
<?php   
$term_list = get_the_term_list( $post->ID, 'listing_feature' );
if(!empty($term_list)): ?>
<h3 class="listing-desc-headline"><?php esc_html_e('Features','listeo_core'); ?></h3>
<?php
echo get_the_term_list( $post->ID, 'listing_feature', '<ul class="listing-features checkboxes margin-top-0"><li>', '</li><li>', '</li></ul>' );
?>
<?php  endif; ?>
