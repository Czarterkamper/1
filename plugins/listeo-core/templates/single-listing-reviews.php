	
<?php
global $post;
//Gather comments for a specific page/post 
$comments = get_comments(array(
    'post_id' => $post->ID,
    'status' => 'approve' //Change this to the type of comments to be displayed
));

// You can start editing here -- including this comment!
if ( $comments ) : ?>
<div id="listing-reviews" class="listing-section">
	<h3 class="listing-desc-headline margin-top-75 margin-bottom-20"><?php
		printf( // WPCS: XSS OK.
			esc_html( _nx( 'Reviews %1$s', ' Review %1$s', listeo_get_reviews_number(), 'comments title', 'listeo' ) ),
			'<span class="reviews-amount">(' . number_format_i18n( listeo_get_reviews_number() ). ')</span>'
		);
	?></h3>

	<div class="clearfix"></div>
	
	<!-- Reviews -->
	<section class="comments listing-reviews">
		<ul class="comment-list">
			<?php
				wp_list_comments( array(
					'style'      	=> 'ul',
					'short_ping' 	=> true,
					'callback' 		=> 'listeo_comment_review',
				),$comments );
			?>
		</ul><!-- .comment-list -->
	</section>

	<!-- Pagination -->
	<div class="clearfix"></div>
	
		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
		
			<div class="row">
				<div class="col-md-12">
					<!-- Pagination -->
					<div class="pagination-container margin-top-30">
						<nav class="pagination">
							<div class="nav-links">

								<div class="nav-previous"><?php previous_comments_link( esc_html__( 'Older Comments', 'listeo' ) ); ?></div>
								<div class="nav-next"><?php next_comments_link( esc_html__( 'Newer Comments', 'listeo' ) ); ?></div>

							</div><!-- .nav-links -->
							<!-- <ul>
								<li><a href="#" class="current-page">1</a></li>
								<li><a href="#">2</a></li>
								<li><a href="#"><i class="sl sl-icon-arrow-right"></i></a></li>
							</ul> -->
						</nav>
					</div>
				</div>
			</div>
		<div class="clearfix"></div>
		<!-- Pagination / End -->
		<?php endif; // Check for comment navigation. ?>
</div>
<?php endif; // Check for have_comments().


// If comments are closed and there are comments, let's leave a little note, shall we?
if ( ! comments_open() ): ?>
	<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'listeo' ); ?></p>
<?php
else : 
	if( (int) $post->post_author == get_current_user_id() ) {   ?>
		<div class="margin-top-50"></div>
	<?php } else { 
	// Get the comments for the logged in user.
    $usercomment = false;
    if(is_user_logged_in()) {
		$usercomment = get_comments( array (
            'user_id' => get_current_user_id(),
            'post_id' => $post->ID,
    	) );
    }
    
    if ( $usercomment ) { ?>
        <div class="notification notice margin-top-50"><p>You've already reviewed this listing.</p></div>
    <?php } else { ?>
	<div id="add-review" class="add-review-box">
		<!-- Add Review -->
		<h3 class="listing-desc-headline margin-bottom-20"><?php esc_html_e('Add Review','listeo_core') ?></h3>
		<?php comment_form(); ?>
	</div>
	<!-- Add Review Box / End -->
	<?php }
	} ?>
<?php endif; ?>