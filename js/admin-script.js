(function ($) {
    $(function () {
        $('.asap-tab').click(function(){
           var attr_id = $(this).attr('id');
           var id = attr_id.replace('asap-tab-','');
           $('.asap-tab').removeClass('asap-active-tab');
           $(this).addClass('asap-active-tab'); 
           $('.asap-section').hide();
           $('#asap-section-'+id).show();
        });
        
        
        
        $('#asap-fb-authorize-ref').click(function(){
           $('input[name="asap_fb_authorize"]').click(); 
        });

        $('.asfap-apitype').change(function(){
           if (this.value === 'graph_api') {
               $('.apfap-graph-api-options').show();
               $('.apfap-android-api-options').hide();
            }
            else if (this.value === 'mobile_api') {
               $('.apfap-graph-api-options').hide();
              $('.apfap-android-api-options').show();
            }

        });

        var apitype = $(".asfap-apitype:checked").val();
        if (apitype === 'graph_api') {
               $('.apfap-graph-api-options').show();
               $('.apfap-android-api-options').hide();
         }
          else if (apitype === 'mobile_api') {
              $('.apfap-graph-api-options').hide();
              $('.apfap-android-api-options').show();
          }

          /*
            * Get facebook pages using graph api
           */

         $('.asap-fbgraph-settings-wrapper').on('click','#asap-get-pages-button',function (e) {
          "use strict";
            e.preventDefault();
            var fgraph_appid = $('#afap_fgraph_app_id').val();
            var fgraph_appsecret = $('#afap_fgraph_app_secret').val();
            var fgraph_usertoken = $('#afap_fgraph_user_access_token').val();
            if(fgraph_appid === '' || fgraph_appsecret === '' || fgraph_usertoken === ''){
             alert('Please fill App ID, App Secret and User Token fields!');
            } else {
               $.ajax({
                type: 'post',
                url: asfap_backend_js_obj.ajax_url,
                data: {
                    fgraph_appid: fgraph_appid,
                    fgraph_appsecret: fgraph_appsecret,
                    fgraph_usertoken: fgraph_usertoken,
                    action: 'asfap_get_fbgraph_pages_action',
                    _wpnonce: asfap_backend_js_obj.ajax_nonce
                },
                beforeSend: function (xhr) {
                    $('.asap-ajax-loader').css('visibility','visible');
                    $('.asap-ajax-loader').css('opacity',1);
                },
                success: function (resp) {
                    $('.asap-ajax-loader').css('visibility','hidden');
                    $('.asap-ajax-loader').css('opacity',0);
                    if(resp.success == false){
                        $('#asap-error-msg').html('An error occured. Please try again after you have filled the APP ID, APP Secret and User Access Token correctly').css({color:'red'});
                    } else {
              var result = $.parseJSON(resp.data);
              if(result.error){
                 $('#asap-error-msg').html('An error occured. Please fill in the correct APP ID and APP Secret or renew your User Access Token and try again.').css({color:'red'});
              } else {
                $('#asap-error-msg').html('The pages managed by you were fetched successfully.').css({color:'green'}).delay(1000).fadeOut();
                var dropdown3 = $('#asap-graph-pages-select');
                var allJson = $('#asap-graph-pages-all-json');
                dropdown3.empty();
                allJson.empty();
                $.each(result.data, function(index, curItem) {
                  dropdown3.append($("<option data-page-token=''></option>").attr('value', curItem.id).data('page-token', curItem.access_token).text(curItem.name));
                });
                allJson.append(resp.data);
              }
                    }
                }
            });
            }
          });
      var dropdown = $('#asap-button-template-floating');
      $('.asap-network-inner-wrap').on('click','.asap-add-account-button',function (e) {
        e.preventDefault();
        var token_url = $('#asap-generated-access-url').val();
        $.ajax({
            type: 'post',
            url: asfap_backend_js_obj.ajax_url,
            data: {
                token_url: token_url,
                action: 'asfap_add_account_action',
                _wpnonce: asfap_backend_js_obj.ajax_nonce
            },
            beforeSend: function (xhr) {
                $('.asap-ajax-loader').css('visibility','visible');
                $('.asap-ajax-loader').css('opacity',1);
            },
            success: function (res) {
                //console.log(res.result);
                if(res.type == 'success'){
                    $('#asap-error-msg').html(res.message).css({color:'green'}).delay(2000).fadeOut();
                    dropdown.empty();
                    $.each(res.result, function(key, value) {
                      if(key == "fap_user_accounts"){
                       $.each(this, function(k, v) {
                        if(k == "auth_accounts"){
                            $.each(this, function(akey, avalue) {
                                var auth_key = akey;
                                var auth_value = avalue;
                                dropdown.append($('<option></option>').attr('value', auth_key).text(auth_value)); 
                            });
                        }
                      });
                      } 
                   });
                  // To encode an object (This produces a string)
                  var json_str = JSON.stringify(res.result);
                  $('textarea#asap-account-all-json').html('');
                  $('textarea#asap-account-all-json').html(json_str);
                }
                else{
                    $('#asap-error-msg').html(res.message).css({color:'red'});
                }
                $('.asap-ajax-loader').css( 'visibility' , 'hidden' );
                $('.asap-ajax-loader').css( 'opacity', 0 );
            }
        });
    });
  });//document.ready close
}(jQuery));