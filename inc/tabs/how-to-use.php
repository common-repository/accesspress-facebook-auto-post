<div class="asap-section" id="asap-section-how" <?php if ($active_tab != 'how') { ?>style="display: none;"<?php } ?>>
     <p class="asap-authorize-note apfap-graph-api-options"><?php _e('Important Note: This plugin is replacement of our plugin "AccessPress Facebook Auto Post" This is not an official APP of Facebook but is a Free WordPress plugin which only helps you to auto post any post ,pages or custom post type content to your facebook account in your profile or fan pages.', 'accesspress-facebook-auto-post'); ?></p>
    <div class="more-title"><?php _e('Graph API For Auto Post To Your Facebook Pages', 'accesspress-facebook-auto-post'); ?></div>
    <div class="asap-network-right-text-wrap">
                <strong><?php esc_html_e('To obtain Facebook App ID and App Secret', AFAP_TD); ?></strong>
                <ol>
                    <li><?php esc_html_e('Create a Business type Facebook App by following the link below.', AFAP_TD); ?><br><a target="_blank" href="https://developers.facebook.com/apps/"><?php echo esc_html('https://developers.facebook.com/apps/', AFAP_TD); ?></a></li>
                    <img class="zoom-in-img" src="<?php echo AFAP_IMG_DIR.'/business-type.png'; ?>" >
                    <li><?php esc_html_e('Fill the App details as shown in the image below then, Create the App.', AFAP_TD); ?></li>
                    <img class="zoom-in-img" src="<?php echo AFAP_IMG_DIR.'/app-details.png'; ?>" >
                    <li><?php esc_html_e('After creating the App you can find App ID and App Secret in App Dashboard >> Settings >> Basic as shown in the image below.', AFAP_TD); ?></li>
                    <img class="zoom-in-img" src="<?php echo AFAP_IMG_DIR.'/app-id-app-secret.png'; ?>" >
                </ol>
                <br>
                <strong><?php esc_html_e('To obtain the User Access Token for the App that you just created', AFAP_TD); ?></strong>
                <ol>
                    <li><?php esc_html_e('Click on the link below to go to Facebook\'s Graph API Explorer.', AFAP_TD); ?><br><a target="_blank" href="https://developers.facebook.com/tools/explorer/"><?php echo esc_html('https://developers.facebook.com/tools/explorer/', AFAP_TD); ?></a></li>
                    <li><?php esc_html_e('Select the App that you just created and then select required permissions. The required permissions for publishing content to your facebook page are pages_show_list, pages_read_engagement, pages_manage_posts.', AFAP_TD); ?></li>
                    <li><?php esc_html_e('Select the type of token that you want to generate. In this case you need User Access Token.', AFAP_TD); ?></li>
                    <li><?php esc_html_e('After selecting the App, the required permissions and the required access token you can click on ', AFAP_TD); ?><strong><?php esc_html_e('Generate Access Token', AFAP_TD); ?></strong><?php esc_html_e(' to generate the required access token.', AFAP_TD); ?></li>
                    <img class="zoom-in-img" src="<?php echo AFAP_IMG_DIR.'/generate-access-token.png'; ?>" >
                </ol>
            </div>
</div>
