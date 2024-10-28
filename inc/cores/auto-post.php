<?php
// $this->print_array($post);
$id = $post->ID;
// $this->print_array($accounts);
$post_type = get_post_type($id);
$taxonomies = get_object_taxonomies($post_type);
$terms = wp_get_post_terms($id, $taxonomies);
$account_details = get_option('afap_settings');
$categories = isset($account_details['category']) ? $account_details['category'] : array();
$category_flag = false;
if (count($categories) == 0) {
    $category_flag = true;
} else if (in_array('all', $categories)) {
    $category_flag = true;
} else {
    foreach ($terms as $term) {
        if (in_array($term->term_id, $categories)) {
            $category_flag = true;
        }
    }
}
if ($post_type == "page") {
   $category_flag = true;
}
$account_details['post_types'] = (isset($account_details['post_types']) && !empty($account_details['post_types']))?$account_details['post_types']:array();
if (in_array($post_type, $account_details['post_types']) && $category_flag) {
 foreach ($account_details as $key => $val) {
        $$key = $val;
 }
    $post_title = strip_tags($post->post_title);
    $post_content = strip_tags($post->post_content);
    $post_content = str_replace('&nbsp;','',$post_content);
    $post_content = strip_shortcodes($post_content);
    $post_content = ($post_content!='')?substr($post_content,0,10000):'';
    $post_excerpt = $post->post_excerpt;
    $post_link = get_the_permalink($id);
    $post_author_id = $post->post_author;
    $site_name = get_option('blogname');
    $caption = get_bloginfo('description');
    $author_name = get_the_author_meta('user_nicename', $post_author_id);
    $tags_arr = array();
    $post_type = $post->post_type; // Post type
    $hashtags = '';
    if($post_type != 'page'){
     $tagnames = wp_get_post_tags($id);
     if(isset($tagnames) && !empty($tagnames)){
        $tagnames = (array)$tagnames;
        foreach ($tagnames as $key => $value) {
         $tags_arr[] = $value->name;
        }
      }
     $hashtags   = ( !empty( $tags_arr ) ) ? '#'.implode( ' #', $tags_arr ) : '';
    }

    // Added tag support for wall post
    $message_format = str_replace('#post_title', $post_title, $message_format);
    $message_format = str_replace('#post_content', $post_content, $message_format);
    $message_format = str_replace('#post_excerpt', $post_excerpt, $message_format);
    $message_format = str_replace('#post_link', $post_link, $message_format);
    $message_format = str_replace('#author_name', $author_name, $message_format);
// 	if($post_type == 'product'){
//         $_product = wc_get_product( $post->ID );
//         $message_format = str_replace('#woo_sale_price', $_product->get_sale_price(), $message_format);
//         $message_format = str_replace('#woo_regular_price', $_product->get_regular_price(), $message_format);
//     }
    // Added for Feature Image auto post thumbnail
    $post_featured_img = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
    if (isset($post_featured_img[0]) && !empty($post_featured_img[0])) {
        //check post featrued image is set the use that image
        $post_img = $post_featured_img[0];
     }else{
      $post_img = '';
     }
    // End for thumbnail
    $auto_publish=$account_details['auto_publish'];
    $auto_publish_pages = (empty($account_details['page_group_lists']))?array(1):$account_details['page_group_lists'];
    $page_details = (empty($account_details['page_details']))?array(1):$account_details['page_details'];
if(!empty($page_details)){
    $page_details = json_decode($page_details, true);
}else{
    $page_details = array();
}
// $this->print_array($fap_auth_tokens);
 //exit();
// require_once( ASAP_PLUGIN_PATH . '/Facebook/Facebook_API.php' );
if($auto_publish=='1'){
	if(!empty($page_details)){
		if (is_array($auto_publish_pages) && !empty($auto_publish_pages)) {
	//         $facebook_api = new ASAP_REST_API();
			foreach ($auto_publish_pages as $auto_publish_page) {
				 $fb_page_id = $auto_publish_page; 
				 foreach ($page_details['data'] as $page_detail ) {
					if($page_detail['id'] == $fb_page_id){
						$page_name = $page_detail['name'];
						$page_access_token = $page_detail['access_token'];
					}
				}

				$post_method = "feed"; //"feed" for wallposts
				$send['message'] = $message_format;
				if( !empty( $send['message'] ) ){
					$send['message'] = urlencode($send['message']);  
				}
				$send['link'] = $post_link;
				if( !empty( $send['link'] ) ){
					$send['link'] = urlencode($send['link']);  
				}

				$post_id = $id;
				$log_time = date('Y-m-d h:i:s A');

				$publish_feed_url = "https://graph.facebook.com/v12.0/".$fb_page_id."/feed/?message=".$send['message']."&link=".$send['link']."&access_token=".$page_access_token;
				$post_request = wp_remote_post($publish_feed_url);
				if( is_wp_error( $post_request ) ) {
					$error_message = $post_request->get_error_message();	
				} else {
					$post_response = wp_remote_retrieve_body($post_request);
					$post_response = json_decode($post_response);
					if(isset($post_response->error) || !$post_response){
						$error_message = $post_response->error->message;
						$postflg = false;
					}
					if( isset( $post_response->id ) ) {
						$postflg = true;
					}
				}
				if( $postflg != false ) {
					 $log_status = 1;
					 $log_details = __('Posted Successfully on ', ASAP_TD) . $page_name;
				}else{
					 $log_status = 0;
					 $log_details = $error_message;
				}

				/**
				 * Inserting log to logs table
				 * */
				global $wpdb;
				$log_table_name = $wpdb->prefix . 'afap_logs';
				$wpdb->insert(
						$log_table_name, array(
					'post_id' => $id,
					'log_status' => $log_status,
					'log_time' => $log_time,
					'log_details' => $log_details
						), array(
					'%d',
					'%d',
					'%s',
					'%s'
				  )
				);
			}//foreach auto publish pages

		 }//If autopublish page is not empty check closed 
	}
}
}