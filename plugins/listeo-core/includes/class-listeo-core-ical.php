<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Listeo_Core_Listing class
 */
class Listeo_Core_iCal {
	
	private static $_instance = null;
    private static $bookings = null; 
    

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

	public function __construct () {

        Listeo_Core_iCal::$bookings = new Listeo_Core_Bookings_Calendar;

        add_action( 'wp_ajax_add_new_listing_ical', array( $this, 'add_new_listing_ical' ) );
        add_action( 'wp_ajax_add_remove_listing_ical', array( $this, 'add_remove_listing_ical' ) );
        add_action( 'wp_ajax_refresh_listing_import_ical', array( $this, 'refresh_listing_import_ical' ) );
        
        // set schedules to generate ical files
        if ( ! wp_next_scheduled( 'listeo_update_booking_icals' ) ) {
            wp_schedule_event( time(), '30min', 'listeo_update_booking_icals' );
        }

        add_action( 'listeo_update_booking_icals', array( $this, 'listeo_update_booking_icals' ) ); 

	}


    function add_new_listing_ical(){

        $listing_id = $_POST['listing_id'];
        $name = $_POST['name'];
        $url = $_POST['url'];

        if(empty($name) || empty($url) || !intval($listing_id)) {
            $result['type'] = 'error';
            $result['notification'] =  esc_html__( "Please fill the form fields" , "listeo_core" );
            wp_send_json($result);
            die();
        }

        $extension = pathinfo($url, PATHINFO_EXTENSION);
        listeo_write_log($extension );
        $extension = explode('?',$extension);
        
        $name = sanitize_title($name);

        if( !filter_var( $url, FILTER_VALIDATE_URL ) ){

            $result['type'] = 'error';
            $result['notification'] =  esc_html__( "Please provide valid URL" , "listeo_core" );
            wp_send_json($result);

            die();
        }

        if (  !in_array( $extension[0], array( 'ical', 'ics', 'ifb', 'icalendar' ) ) ) {
            if(strpos($url, 'calendar') !==  false || strpos($url, 'accommodation_id') !==  false) {
                //let listeo in
            } else {

                $result['type'] = 'error';
                $result['notification'] =  esc_html__( "No valid iCal file recognized. Please import 'ical', 'ics', 'ifb' or 'icalendar' file" , "listeo_core" );
                wp_send_json($result);

                die();

            }
        }

        $icals_array = array();
        $temp_array = array();

        $new_ical = array(
            'name' => $name,
            'url'   => $url
        );

        $temp_array['url'] = esc_url_raw($url);
        $temp_array['name'] = esc_html($name);

        $icals_array[] = $temp_array;
        $current_icals = get_post_meta($listing_id,'listeo_ical_imports',true);
        
        if(is_array($current_icals)){
            //todo check if the same link was already added
            if(in_array( $name, array_column($current_icals, 'name'))) {
               $result['type'] = 'error';
                $result['notification'] =  esc_html__( "It look's like you've already calendar with that name" , "listeo_core" );
                wp_send_json($result);

                die(); 
            } else if(in_array( $url, array_column($current_icals, 'url'))) {

                $result['type'] = 'error';
                $result['notification'] =  esc_html__( "It look's like you've already added that calendar URL" , "listeo_core" );
                wp_send_json($result);

                die();
            } else {

                $current_icals = array_merge($current_icals, $icals_array );    
            
            }
            

        } else {

            $current_icals = $icals_array;

        }
       
        $action = update_post_meta( $listing_id, 'listeo_ical_imports', $current_icals );
        
        if($action){

        
        $output = $this->get_saved_icals( $listing_id );
        $imported = $this->import_bookings_from_ical($temp_array,$listing_id);
        if($imported){
            //$imported_info = sprintf( __( "We've successfully imported %s events", 'listeo_core' ), $imported );
            $imported_info = sprintf( _n( "We've successfully imported %s event", "We've successfully imported %s events", $imported, 'listeo_core' ), $imported );
        } else {
            $imported_info = esc_html__( "No events imported" , "listeo_core" );
        }
            if ($output) {
                
                $result['type'] = 'success';
                $result['output'] =  $output;
                $result['notification'] =  $imported_info;


            } else {
            
                $result['type'] = 'error';
                $result['output'] =  $output;
                $result['notification'] =  $imported_info;
            }
        } else {

            $result['type'] = 'error';
            $result['notification'] = esc_html__('There was problem updating the field.','listeo_core');
            
        }
 
        wp_send_json($result);

        die();
    }

    function add_remove_listing_ical() {
        
        $listing_id = $_POST['listing_id'];
        $index = $_POST['index'];

        $current_icals = get_post_meta($listing_id,'listeo_ical_imports',true);

        $removed_ical = $current_icals[$index];
        
        unset($current_icals[$index]);
        
        $action = update_post_meta( $listing_id, 'listeo_ical_imports', $current_icals );
        
        $output = $this -> get_saved_icals( $listing_id );
        $removed = $this -> remove_from_ical($removed_ical,$listing_id); // false or int (number of removed)

        if($removed){
            
            $removed_info = sprintf( _n( "We've successfully removed this calendar with %s event", "We've successfully removed this calendar with %s events", $removed, 'listeo_core' ), $removed );
        
        } else {

            $removed_info = esc_html__( "Calendar was removed, no events deleted" , "listeo_core" );
        
        }
        if ($action) {
          
          $result['type'] = 'success';
          $result['output'] =  $output;
          $result['notification'] =  $removed_info;

        } else {
           $result['type'] = 'error';
           $result['output'] =  $output;
           $result['notification'] =  $removed_info;
        }

        wp_send_json($result);

        die();   
    }

    function refresh_listing_import_ical(){
        $listing_id = $_POST['listing_id'];
        if(!empty($listing_id) || intval($listing_id)) {
            
            $this->import_events($listing_id);
            $result['type'] = 'success';
            $result['notification'] =  esc_html__('Events from calendars were imported','listeo_core');

        } else {

            $result['type'] = 'error';
            $result['notification'] =  esc_html__('There was error with the import, please try again','listeo_core');

        }
        wp_send_json($result);

        die();   
    }
    
    public static function get_saved_icals($listing_id){

        $icals_list = get_post_meta($listing_id,'listeo_ical_imports',true);

        ob_start(); 

        if(!empty($icals_list)) : ?>
            <h4><?php esc_html_e('Imported Calendars','listeo_core'); ?></h4>
            <ul>
                <?php 
                $i = 0;
                foreach ($icals_list as $key => $value) { ?>
                <li><span><?php echo esc_html($value['name']); ?></span> <small><?php echo url_shorten($value['url']); ?></small>
                    <a href="#" data-listing-id="<?php echo esc_attr($listing_id);?>" data-remove="<?php echo esc_attr($key) ?>" class="ical-remove"><?php esc_html_e('Remove','listeo_core'); ?></a> 
                </li>    
                <?php $i++;
            } ?>
            </ul>
            <a href="#" data-listing-id="<?php echo esc_attr($listing_id); ?>" class="update-all-icals" ><?php esc_html_e('Import manually all calendars now','listeo_core'); ?><i class="tip" data-tip-content="<?php esc_html_e('All calendars are automaticaly refreshed every 30 minutes','listeo_core'); ?>"></i></a>
        <?php
        endif;
        $list = ob_get_contents();
        ob_end_clean();
        return $list;
    }


    public static function get_ical_export_url($id){

        $ical_page = get_option('listeo_ical_page');
        
        if($ical_page){
            
            $url = get_permalink($ical_page);
            $slug = get_post_field( 'post_name', $id ); 
            $hash = bin2hex($id.'|'.$slug);
            
            return esc_url_raw( add_query_arg( 'calendar', $hash, $url) ) ;

        } else {
            return false;
        }
    
    }


    public static function generate_event($value){
        
        $details = json_decode($value['comment']); 
        $comment = '';
        $id = $value['listing_id'];
        if( isset($details->first_name) || isset($details->last_name) ) : 
            $comment .= esc_html__('Name: ');
            if(isset($details->first_name)) $comment .= $details->first_name.' '; 
            if(isset($details->last_name))  $comment .=  $details->last_name.' '; 
            $comment .= ' ';
        endif;  
        if( isset($details->email)) :  $comment .= esc_html__('Email: ') . $details->email .' '; endif;
        if( isset($details->phone)) :  $comment .= esc_html__('Phone: ') .$details->phone .' '; endif;

        $start_date =$value['date_start'];
        $end_date = $value['date_end'];
        
        $timestamp = date_i18n('Ymd\THis',time(), true);  
        
        if($start_date != '' ) {
            $start_date = strtotime($start_date);
            $start_date = wp_date("Ymd\THis", $start_date);
        }

        if($end_date != '') {
            $end_date = strtotime($end_date);
            $end_date = wp_date('Ymd\THis', $end_date);
        } else {
            $end_date = wp_date("Ymd\THis", $start_date + (1 * 60 * 60)); // 1 hour after
        } 
            $event = "BEGIN:VEVENT
SUMMARY:".get_the_title($id)."
DESCRIPTION:".listeo_escape_string($comment)."
DTSTART:".$start_date."
DTEND:".$end_date."
UID:" . md5(uniqid(mt_rand(), true)) . "@".$_SERVER['HTTP_HOST']."
DTSTAMP:".$timestamp."
END:VEVENT
"; 
        // $event = 0;
        return $event;
    } 
    


	
    public static function get_ical_events($id){
    
    $ical = false;    
    $listing_type = get_post_meta($id,'_listing_type',true);
    if ( $listing_type == 'rental' || $listing_type   == 'service' ) {       
            
            $eol = "\r\n";
            $post = get_post($id);
    
    $booking = array();

    // get reservations for next 10 years to make unable to set it in datapicker
    if( $listing_type  == 'rental' ) {
        $records = self::$bookings->get_bookings( 
            date('Y-m-d H:i:s', strtotime('-1 year')),  
            date('Y-m-d H:i:s', strtotime('+2 years')), 
            array( 
                'listing_id' => $id, 
                'type' => 'reservation',
                'status' => 'icalimports', //filter out other imports
            ), 
            $by = 'booking_date', $limit = '', $offset = '' ,$all = '', 
            $listing_type = 'rental'
        );
    } else {
        $records = self::$bookings->get_bookings( 
            date('Y-m-d H:i:s', strtotime('-1 year')),  
            date('Y-m-d H:i:s', strtotime('+2 years')), 
            array( 
                'listing_id' => $id, 
                'type' => 'reservation',
                'status' => 'icalimports', //filter out other imports
            ),
            'booking_date',
            $limit = '', 
            $offset = ''
            );    
    }
    
    ob_start();
        foreach ($records as $key => $value) { 
            //var_dump($value['date_start']);
            echo self::generate_event($value);
    
        } 
        $ical = ob_get_contents();
        ob_end_clean();
    }
        return $ical;
    }

    
    function listeo_update_booking_icals(){
         
        $args = array(
            'post_type' => 'listing',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                    array(
                        'key'       =>  'listeo_ical_imports',
                        'compare'   =>  'EXISTS'
                    )
                )
            );

        $query = new WP_Query( $args );
        $posts = $query->get_posts();

        foreach( $posts as $post ) { 
            $ical = $this->import_events($post);
           
        }

    }


    function import_events($listing_id){

        $icals_list = get_post_meta($listing_id,'listeo_ical_imports',true);

        if(!empty($icals_list)) :
            foreach ($icals_list as $key => $value) { 
                self::import_bookings_from_ical($value,$listing_id);
            }
        endif;
    }
    function remove_from_ical($arr,$listing_id){
        
        $url = $arr['url'];
        $name = $arr['name'];
        $id = $listing_id;

        $removed = self::$bookings -> delete_bookings ( array(
                'listing_id' => $listing_id,  
                'type' => 'reservation',
                'comment'       => $name.$id.'icalimport'
            ) );
        return $removed;
    }
    

    function import_bookings_from_ical($arr,$listing_id){
        $url = $arr['url'];
        $name = $arr['name'];
        $id = $listing_id;

        $vcal = new ICal();
        $imported_count = 0;
        $vcal->initURL($url );
    
        if ($vcal->hasEvents()) {
            //remove previously added bookings
            self::$bookings -> delete_bookings ( array(
                'listing_id' => $listing_id,  
                'type' => 'reservation',
                'comment'       => $name.$id.'icalimport'
            ) );

            // add new bookings from future
                $current_date = new DateTime();
              
                foreach($vcal->events() as $event) {
                // $event['DTSTART'] = new \DateTime($event['DTSTART']);
                // $event['DTEND'] = new \DateTime($event['DTEND']);
                //if($event['DTSTART'] > $current_date) {
                    $imported =  self::$bookings -> insert_booking( array(
                            'listing_id'    => $listing_id,  
                            'type'          => 'reservation',
                            'owner_id'      => 0,
                            'date_start'    => $event['DTSTART'],
                            'date_end'      => $event['DTEND'],
                            'comment'       => $name.$id.'icalimport',
                            'order_id'      => NULL,
                            'status'        => 'icalimports'
                        )); 
                    $imported_count++;
                   // }
                }    
                
        }
        
        return $imported_count;
    }
}