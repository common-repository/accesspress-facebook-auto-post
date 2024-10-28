<div class="asap-section" id="asap-section-settings" <?php if ($active_tab != 'settings') { ?>style="display: none;"<?php } ?>>
    <div class="asap-network-wrap asap-network-inner-wrap asap-fbgraph-settings-wrapper">
        <h4 class="asap-network-title"><?php _e('Your Account Details', 'accesspress-facebook-auto-post'); ?></h4>

        <?php
        $account_details = get_option('afap_settings');
        $account_extra_details = get_option('afap_extra_settings');
        $authorize_status = $account_extra_details['authorize_status'];
//         $this->print_array($account_details);
        $api_type = (isset($account_details['api_type']) && $account_details['api_type'] != '')?esc_attr($account_details['api_type']):'graph_api';
        $page_group_lists = (isset($account_details['page_group_lists']) && !empty($account_details['page_group_lists']))?$account_details['page_group_lists']:array();
         $user_data_arr = (isset($account_details['user_data']) && !empty($account_details['user_data']))?$account_details['user_data']:'';
//          $account_pages_and_groups = $this->account_pages_and_groups($data = 'all_app_users_with_name');
        //this->print_array($account_extra_details);
		$graph_pages_and_groups = $this->graph_pages_and_groups();
		$all_page_details = (isset($account_details['page_details']) && $account_details['page_details'] !='')?$account_details['page_details']:'';
        ?>
        <?php if (isset($_SESSION['asap_message'])) { ?><p class="asap-authorize_note"><?php
            echo $_SESSION['asap_message'];
            unset($_SESSION['asap_message']);
            ?></p><?php } ?>

    <div class="apfap-graph-api-options">
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="afap_fb_authorize_action"/>
            <?php wp_nonce_field('afap_fb_authorize_action', 'afap_fb_authorize_nonce'); ?>
            <input type="submit" name="asap_fb_authorize" value="<?php echo ($authorize_status == 0) ? __('Authorize', 'accesspress-facebook-auto-post') : __('Reauthorize', 'accesspress-facebook-auto-post'); ?>" style="display: none;"/>
        </form>
    </div>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        		<input type="hidden" name="action" value="afap_form_action"/>
                <?php wp_nonce_field('afap_form_action', 'afap_form_nonce') ?>
                <div class="asap-network-field-wrap" style="padding-top:10px">
                    <label><?php _e('Auto Publish', 'accesspress-facebook-auto-post'); ?></label>
                    <div class="asap-network-field"><input type="checkbox" value="1" name="account_details[auto_publish]" <?php checked($account_details['auto_publish'], true); ?>/></div>
                </div>
                 <!-- facebook graph api options start -->
                <div class="asap-network-field-wrap apfap-graph-api-options">
                    <label><?php _e('Application ID', 'accesspress-facebook-auto-post'); ?></label>
                    <div class="asap-network-field"><input type="text" name="account_details[application_id]"   id="afap_fgraph_app_id" value="<?php echo isset($account_details['application_id']) ? esc_attr($account_details['application_id']) : ''; ?>"/>
					<div class="asap-field-note">
						<?php esc_html_e('Note: Please fill the App ID after creating a new Business type Facebook App in ', AFAP_TD); ?><a target="_blank" href="https://developers.facebook.com/apps/"><?php echo esc_html('https://developers.facebook.com/apps/', AFAP_TD); ?></a><?php esc_html_e('. You can get App ID and App Secret from App Dashboard >> Settings >> Basic.', AFAP_TD); ?>
					</div>
					</div>
                </div>
                <div class="asap-network-field-wrap apfap-graph-api-options">
                    <label><?php _e('Application Secret', 'accesspress-facebook-auto-post'); ?></label>
                    <div class="asap-network-field">
                        <input type="text" name="account_details[application_secret]" id="afap_fgraph_app_secret" value="<?php echo isset($account_details['application_secret']) ? esc_attr($account_details['application_secret']) : ''; ?>"/>
                        <div class="asap-field-note">
						<?php esc_html_e('Note: Please fill the App Secret of the created Facebook App.', AFAP_TD); ?>
					</div>
                    </div>
                </div>
                <div class="asap-network-field-wrap apfap-graph-api-options">
                    <label><?php _e('User Access Token', 'accesspress-facebook-auto-post'); ?></label>
                    <div class="asap-network-field">
                        <input type="text" name="account_details[facebook_user_id]" id="afap_fgraph_user_access_token"value="<?php echo isset($account_details['facebook_user_id']) ? esc_attr($account_details['facebook_user_id']) : ''; ?>"/>
                      <div class="asap-field-note">
						<?php esc_html_e('Note: Please fill the User Access Token for the Facebook App that you just created. User Access Token can be obtained from ', AFAP_TD); ?><a target="_blank" href="https://developers.facebook.com/tools/explorer/"><?php echo esc_html('https://developers.facebook.com/tools/explorer/', AFAP_TD); ?></a><br><?php esc_html_e('You need to select pages_show_list, pages_read_engagement, pages_manage_posts and publish_to_groups permissions and generate User Token for the App that you created.', AFAP_TD); ?>
					</div>
                    </div>
                </div>



                <div class="asap-network-field-wrap apfap-android-api-options">
                    <label></label>
                    <div class="asap-network-field">
                        <a class="button-primary" id="asap-get-pages-button" href="javascript:void(0);" >
                          <?php _e('Get Pages', 'accesspress-facebook-auto-post'); ?>
                        </a>
                        <div class="asap-ajax-loader">
                         <img src= "<?php echo esc_attr(AFAP_IMG_DIR).'/ajax-loader.gif'; ?>" >
                        </div>
                        <div id="asap-error-msg"></div>
                    </div>
                </div>
               
                <div class="asap-network-field-wrap">
                <label>List Of Pages you manage</label>
                <div class="asap-network-field">
                 <select name="account_details[page_group_lists][]" id="asap-graph-pages-select" multiple="multiple">
                    <?php if(!empty($graph_pages_and_groups)){
                     foreach( $graph_pages_and_groups as $page_id => $page_name) { ?>
                        <option value="<?php echo $page_id; ?>" <?php if (in_array($page_id, $page_group_lists)) { ?> selected = "selected" <?php } ?>>
                            <?php echo $page_name; ?>
                        </option>
                    <?php }
                      }else{ ?>
                     <option selected="true" disabled> No any lists available.</option>
                   <?php  }?>

                </select>
                <textarea name="account_details[page_details]" id="asap-graph-pages-all-json" style="display:none;"><?php echo $all_page_details;?></textarea>
                </div>
            </div>


                 <!-- facebook andriod api options end -->
                <div class="asap-network-field-wrap">
                    <label><?php _e('Post Message Format', 'accesspress-facebook-auto-post'); ?></label>
                    <div class="asap-network-field">
                        <textarea name="account_details[message_format]"><?php echo $account_details['message_format']; ?></textarea>
                        <div class="asap-field-note">
                            <?php _e('Please use #post_title,#post_content,#post_excerpt,#post_link,#author_name for the corresponding post title, post content, post excerpt, post link, post author name respectively.', 'accesspress-facebook-auto-post'); ?>
                        </div>
                    </div>
                </div>
            <?php include('post-settings.php'); ?>
        </form>
    </div>
</div>
