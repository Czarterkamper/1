<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;
/**
 * Listeo_Core_Listing class
 */
class Listeo_Core_Compare {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.26
	 */
	private static $_instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since  1.26
	 * @static
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function __construct() {

		add_action('listeo_core_after_wrapper', array($this,'inject_template'));

		add_action('wp_ajax_listeo_core_compare_this', array($this, 'add_to_compare'));
		add_action('wp_ajax_nopriv_listeo_core_compare_this', array($this, 'add_to_compare'));

		add_action('wp_ajax_listeo_core_uncompare_this', array($this, 'remove_compare'));
		add_action('wp_ajax_nopriv_listeo_core_uncompare_this', array($this, 'remove_compare'));

		add_action('wp_ajax_listeo_core_uncompare_all', array($this, 'remove_all_compare'));
		add_action('wp_ajax_nopriv_listeo_core_uncompare_all', array($this, 'remove_all_compare'));

		add_shortcode( 'listeo_core_compare', array( $this, 'listeo_core_compare' ) );
		

	}

	function inject_template(){
		if(!empty(get_option( 'compare_page' )) && !is_page(get_option( 'compare_page' ))){
			$template_loader = new Listeo_Core_Template_Loader;
			$template_loader->get_template_part( 'compare-side' );	
		}
		
	}


	public function add_to_compare() {
		if ( !wp_verify_nonce( $_REQUEST['nonce'], 'listeo_core_compare_this_nonce')) {
	    	exit('No naughty business please');
	    }   

	    $post_id = $_REQUEST['post_id'];
		$template_loader = new Listeo_Core_Template_Loader; 

	    if(is_user_logged_in()){
		   	$userID = $this->get_user_id();
		   	if($this->check_if_added($post_id)) {
				$result['type'] = 'error';
				$result['message'] = __( 'You\'ve already added that post' , 'listeo_core' );
		   	} 
		   	else {
				$compare_posts =  (array) $this->get_compare_posts();
				if( count($compare_posts) >=4 ) {
					$result['type'] = 'error';
					$result['message'] = __( 'You can only compare 4 listings' , 'listeo_core' );
				} else {
			   		$compare_posts[] = $post_id;
					$action = update_user_meta( $userID, 'listeo_core-compare-posts', $compare_posts );
					
					if($action === false) {
						$result['type'] = 'error';
						$result['message'] = __( 'Oops, something went wrong, please try again' , 'listeo_core' );
					} else {
						$result['type'] = 'success';
						$result['message'] = __( 'Listing was added to compare list' , 'listeo_core' );
						ob_start();
							$compare_post = get_post( $post_id  );
							setup_postdata($compare_post);
							$nonce = wp_create_nonce("listeo_core_uncompare_this_nonce"); ?>
							<div class="listing-item compact">
									<a href="<?php echo get_permalink($post_id); ?>" class="listing-img-container">
									<div data-post_id="<?php echo esc_attr($post_id); ?>" data-nonce="<?php echo esc_attr($nonce); ?>" class="remove-from-compare"><i class="fa fa-close"></i></div>
									<div class="listing-badges"><?php the_listing_offer_type($compare_post);?></div>
									<div class="listing-img-content">
										<span class="listing-compact-title"><?php echo get_the_title($post_id); ?> <i><?php echo get_the_listing_price( $compare_post ); ?></i></span>
									</div>
									<?php 		
										if(has_post_thumbnail($post_id)){ 

											echo get_the_post_thumbnail($post_id,'listeo-listing-grid'); 
										} else {
											var_dump('test');
											$gallery = (array) get_post_meta( $post_id, '_gallery', true );
											if(!empty($gallery)){
												$ids = array_keys($gallery);
												if(!empty($ids[0])){ 
													echo  wp_get_attachment_image($ids[0],'listeo-listing-grid'); 
												}	
											} else { ?>
													<img src="<?php echo get_listeo_core_placeholder_image(); ?>" alt="">
											<?php } 
										} 
									?>
								</a>
							</div>
						<?php
						wp_reset_postdata();
						$html = ob_get_clean();
						$result['html'] = $html;
					}
				}
			}
		}
		else {
			$compare_posts = array();
			if(isset( $_COOKIE['listeo_core-compareposts'] )) {
				$compare_posts = $_COOKIE['listeo_core-compareposts'];
				$compare_posts = explode(',', $compare_posts);
			}

			if( count($compare_posts) >=4 ) {
				$result['type'] = 'error';
				$result['message'] = __( 'You can only compare 4 listings' , 'listeo_core' );
			} else {
				if($this->check_if_added($post_id)) {
					$result['type'] = 'error';
					$result['message'] = __( 'You\'ve already added that post' , 'listeo_core' );
			   	} else {
					$compare_posts[] = $post_id;
					$compare_posts = implode(',', $compare_posts);
					$expire = time()+60*60*24*30;
		    		setcookie("listeo_core-compareposts", $compare_posts, $expire, "/");
		    		ob_start();
									$compare_post = get_post( $post_id  );
									setup_postdata($compare_post);
									$nonce = wp_create_nonce("listeo_core_uncompare_this_nonce"); ?>
									<div class="listing-item compact">
											<a href="<?php echo get_permalink($post_id); ?>" class="listing-img-container">
											<div data-post_id="<?php echo esc_attr($post_id); ?>" data-nonce="<?php echo esc_attr($nonce); ?>" class="remove-from-compare"><i class="fa fa-close"></i></div>
											<div class="listing-badges"><?php the_listing_offer_type($compare_post);?></div>
											<div class="listing-img-content">
												<span class="listing-compact-title"><?php echo get_the_title($post_id); ?> <i><?php echo get_the_listing_price( $compare_post ); ?></i></span>
											</div>
											<?php 		
												if(has_post_thumbnail($post_id)){ 
													echo get_the_post_thumbnail($post_id,'listeo-listing-grid'); 
												} else {
													$gallery = (array) get_post_meta( $post_id, '_gallery', true );
													if(!empty($gallery)){
														$ids = array_keys($gallery);
														if(!empty($ids[0])){ 
															echo  wp_get_attachment_image($ids[0],'listeo-listing-grid'); 
														}	
													} else { ?>
															<img src="<?php echo get_listeo_core_placeholder_image(); ?>" alt="">
													<?php } 
												} 
											?>
										</a>
									</div>
								<?php
								wp_reset_postdata();
								$html = ob_get_clean();
								$result['html'] = $html;
		    		$result['type'] = 'success';
					$result['message'] = __( 'Listing was added to compare list' , 'listeo_core' );
				}
			}
		}
		   
		
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	      $result = json_encode($result);
	      echo $result;
	   	}
		else {
		  header('Location: '.$_SERVER['HTTP_REFERER']);
		}
	   	die();
	}	

	public function remove_compare() {
		if ( !wp_verify_nonce( $_REQUEST['nonce'], 'listeo_core_uncompare_this_nonce')) {
	      exit('No naughty business please');
	  	}   
	   	$post_id = $_REQUEST['post_id'];
	   	if(is_user_logged_in()){
		   	$userID = $this->get_user_id();
		
	   		$compare_posts = $this->get_compare_posts();
	   		$compare_posts = array_diff($compare_posts, array($post_id));
	        $compare_posts = array_values($compare_posts);

			$action = update_user_meta( $userID, 'listeo_core-compare-posts', $compare_posts, false );
			if($action === false) {
				$result['type'] = 'error';
				$result['message'] = __('Oops, something went wrong, please try again','listeo_core');
			} else {
				$result['type'] = 'success';
				$result['message'] = __('Listing was removed from the list','listeo_core');
			}
		} else {
			$compare_posts = array();
			if(isset( $_COOKIE['listeo_core-compareposts'] )) {
				$compare_posts = $_COOKIE['listeo_core-compareposts'];
				$compare_posts = explode(',', $compare_posts);
			}
			$compare_posts = array_diff($compare_posts, array($post_id));
			$compare_posts = implode(',', $compare_posts);
			$expire = time()+60*60*24*30;
    		setcookie("listeo_core-compareposts", $compare_posts, $expire, "/");
    		$result['type'] = 'success';
			$result['message'] = __('Listing was removed from the list','listeo_core');
		}

	   	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	      $result = json_encode($result);
	      echo $result;
	   	} else {
	      header('Location: '.$_SERVER['HTTP_REFERER']);
	   	}

	   die();
	}

	public function remove_all_compare() {
		if ( !wp_verify_nonce( $_REQUEST['nonce'], 'listeo_core_uncompare_all_nonce')) {
	      exit('No naughty business please');
	  	}   
	   	
	   	if(is_user_logged_in()){
		   	$userID = $this->get_user_id();
	   		$compare_posts = array();

			$action = update_user_meta( $userID, 'listeo_core-compare-posts', $compare_posts, false );
			if($action === false) {
				$result['type'] = 'error';
				$result['message'] = __('Oops, something went wrong, please try again','listeo_core');
			} else {
				$result['type'] = 'success';
				$result['message'] = __('Properties were removed from the list','listeo_core');
			}
		} else {
			unset($_COOKIE['listeo_core-compareposts']);
    		setcookie("listeo_core-compareposts", "", time()-3600, "/");
    		$result['type'] = 'success';
			$result['message'] = __('Properties were removed from the list','listeo_core');
		}

	   	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	      $result = json_encode($result);
	      echo $result;
	   	} else {
	      header('Location: '.$_SERVER['HTTP_REFERER']);
	   	}

	   die();
	}

	function get_user_id() {
	    global $current_user;
	    wp_get_current_user();
	    return $current_user->ID;
	}

	function get_compare_posts() {
		$compare_post_ids = array();
		if(is_user_logged_in()){
			$compare_post_ids = (array) get_user_meta($this->get_user_id(), 'listeo_core-compare-posts', true);
		} else {
			if(isset( $_COOKIE['listeo_core-compareposts'] )) {
				$compare_posts = $_COOKIE['listeo_core-compareposts'];
				$compare_post_ids = explode(',', $compare_posts);
			}
		}
		return $compare_post_ids;
	}

	function check_if_added($id) {
		$compare_post_ids = $this->get_compare_posts();
		
		if ($compare_post_ids) {
            foreach ($compare_post_ids as $compare_id) {
                if ($compare_id == $id) { 
                	return true; 
                }
            }
        } 
        return false;
	}

	function listeo_core_compare( $atts = array() ) {
		extract( $atts = shortcode_atts( apply_filters( 'listeo_core_compare_defaults', array(
			'custom_class'				=> '',
		) ), $atts ) );

		ob_start();
		$template_loader = new Listeo_Core_Template_Loader;
		$listings = array();
		$listings_top = array();
		$compare_posts = array();
		if(is_user_logged_in()){
			 	global $current_user;
			    wp_get_current_user();
			    $user_id =  $current_user->ID;
			    $compare_posts = get_user_meta($user_id, 'listeo_core-compare-posts', true);
			   
		} else{
				if(isset( $_COOKIE['listeo_core-compareposts'] )) {
		        $compare_posts = $_COOKIE['listeo_core-compareposts'];
		        $compare_posts = explode(',', $compare_posts);
		    }
		}
		if(!empty($compare_posts)) :
			$query = new WP_Query( array( 'post_type' => 'listing', 'post__in' => $compare_posts ) );
			if (  $query->have_posts() ) {
				
				$listings_top = array();
			    $listings_top[0]['title'] = 'Title';
			    $listings_top[0]['id'] = 'ID';
			    $listings_top[0]['url']  = 'URL';
		        $listings_top[0]['image'] = 'Image';
		        $listings_top[0]['price'] = 'Price';

		        $details_list = Listeo_Core_Meta_Boxes::meta_boxes_main_details(); 
				foreach ($details_list['fields'] as $detail => $value) {
					$listings[$value['id']][] = $value['name'];
				}
		        	
        		$feature_terms = get_terms( 'listing_feature', array(
				    'hide_empty' => false,
				) );
				foreach ($feature_terms as $key => $value) {
					$listings[$value->slug][] = $value->name;
				}

		        $main_details_list = Listeo_Core_Meta_Boxes::meta_boxes_details();
		        foreach ($main_details_list['fields'] as $detail => $value) {
					$listings[$value['id']][] = $value['name'];
				}

			    while (  $query->have_posts() ) {
			        $query->the_post();
			        $post_id = $query->post->ID; 
			        $image = false;
					if(has_post_thumbnail()){ 
						$image = get_the_post_thumbnail_url($post_id,'listeo-listing-grid'); 

					} else {
						$gallery = (array) get_post_meta( $post_id, '_gallery', true );

						if(!empty($gallery)){
							$ids = array_keys($gallery);

							if(!empty($ids[0])){ 
								$image = wp_get_attachment_image_url($ids[0],'listeo-listing-grid'); 
							}	
						} else { $image =  get_listeo_core_placeholder_image(); } 
					} 


			        $listings_top[$post_id]['id'] 	= $post_id;
			        $listings_top[$post_id]['title'] 	= get_the_title($post_id);
			        $listings_top[$post_id]['url'] 	= get_the_permalink($post_id);
			        $listings_top[$post_id]['image'] 	= $image;
			        $listings_top[$post_id]['price']	= get_the_listing_price();

				 	$terms = wp_get_post_terms($post_id, 'listing_feature',array("fields" => "ids"));

    		
			
			        foreach ($details_list['fields'] as $detail => $value) {
						$meta_value = get_post_meta($post_id, $value['id'],true);
							$listings[$value['id']][] = $meta_value;
					}

					foreach ($feature_terms as $key => $value) {
    					
    					if(in_array($value->term_id,$terms)){
			        		$listings[$value->slug][]	= '<span class="available"></span>'; 						
    					} else {
    						$listings[$value->slug][]	= '<span class="not-available"></span>'; 						
    					}
					}
					
					foreach ($main_details_list['fields'] as $detail => $value) {
						$meta_value = get_post_meta($post_id, $value['id'],true);
						
							if($value['id'] == '_area'){
								$scale = get_option( 'scale', 'sq ft' );
								$listings[$value['id']][] = $meta_value.apply_filters('listeo_core_scale',$scale);	
							} else {
								$listings[$value['id']][] = $meta_value;	
							}
						
					}
 				}

				 
			}
				// Reset the `$post` data to the current post in main query.
			wp_reset_postdata();
				wp_reset_query(); 
		endif;
		$template_loader->set_template_data( array( 'listings' => $listings,'listings_top'=>$listings_top ) )->get_template_part( 'compare-listings' ); 


		return ob_get_clean();
	}
}