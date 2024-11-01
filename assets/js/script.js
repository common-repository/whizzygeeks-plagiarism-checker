window.jQuery(document).ready(function (jQuery) {

    jQuery(document).on("click", "#report_tab", function(e) {

        jQuery.ajax({
        type: "POST",
        url: 'https://checkplag.whizzygeeks.com/plag-report.php',
        data: {
            'action': 'wg_pcp_plag_reports',
            'salt' : wg_pcp_frontend_ajax_object.__ARGS_K__
        },
        success: function (res) {
            var result = JSON.parse(res);
            // console.log(result.html);
            jQuery("#reports_option").html(result.html);
          
        },
        error: function (jqXHR, textStatus) {
            jQuery("#error_div").html('<div class="container" style="width: 95%; margin: 0px auto; padding: 0px 0px 30px;  border-radius: 10px;"><div class="row"><div class="col-sm-12"><h2 style="text-align: center; margin-bottom: -20px; color: rgb(250, 169, 22); font-size: 22px; font-weight: 700;">Error #PCP 14</h2><h4 style="text-align: center; font-size: 15px;">Article parsing error</h4><p>Please contact our support team at <a href="https://www.plagiarismcheckerpro.com/#support">www.plagiarismcheckerpro.com in case of any query.</p></div></div>');
        }
        });
   

    });
    
    jQuery(document).on("click", ".rp_page", function(e) {
        e.preventDefault();
        var page_no = jQuery(this).attr("rel"),
            days_filter = jQuery("input[name='days-filter']:checked").attr('id');

        jQuery.ajax({
            type: "POST",
            url: 'https://checkplag.whizzygeeks.com/plag-report.php',
            data: {
                'action'    : 'wg_pcp_plag_reports',
                'salt'      : wg_pcp_frontend_ajax_object.__ARGS_K__,
                'page'      : page_no,
                'filter'    : days_filter
            },
            success: function (res) {
                var result = JSON.parse(res);
                // console.log(result.html);
                jQuery("#reports_option").html(result.html);
                jQuery('.days-filter').parent().removeClass('days-selected');
                jQuery('#'+id).parent().addClass('days-selected');
              
            },
            error: function (jqXHR, textStatus) {
                jQuery("#error_div").html('<div class="container" style="width: 95%; margin: 0px auto; padding: 0px 0px 30px;  border-radius: 10px;"><div class="row"><div class="col-sm-12"><h2 style="text-align: center; margin-bottom: -20px; color: rgb(250, 169, 22); font-size: 22px; font-weight: 700;">Error #PCP 14</h2><h4 style="text-align: center; font-size: 15px;">Article parsing error</h4><p>Please contact our support team at <a href="https://www.plagiarismcheckerpro.com/#support">www.plagiarismcheckerpro.com in case of any query.</p></div></div>');
            }
        });
    });

    jQuery(document).on("click", ".days-filter", function(e) {
        var id = jQuery(this).attr("id");
        jQuery.ajax({
            type: "POST",
            url: 'https://checkplag.whizzygeeks.com/plag-report.php',
            data: {
                'action'    : 'wg_pcp_plag_reports',
                'salt'      : wg_pcp_frontend_ajax_object.__ARGS_K__,
                'filter'    : id
            },
            success: function (res) {
                var result = JSON.parse(res);
                // console.log(result.html);
                jQuery("#reports_option").html(result.html);
                jQuery('.days-filter').parent().removeClass('days-selected');
                jQuery('#'+id).parent().addClass('days-selected');
              
            },
            error: function (jqXHR, textStatus) {
                jQuery("#error_div").html('<div class="container" style="width: 95%; margin: 0px auto; padding: 0px 0px 30px;  border-radius: 10px;"><div class="row"><div class="col-sm-12"><h2 style="text-align: center; margin-bottom: -20px; color: rgb(250, 169, 22); font-size: 22px; font-weight: 700;">Error #PCP 14</h2><h4 style="text-align: center; font-size: 15px;">Article parsing error</h4><p>Please contact our support team at <a href="https://www.plagiarismcheckerpro.com/#support">www.plagiarismcheckerpro.com in case of any query.</p></div></div>');
            }
        });
   

    });

    if( wg_pcp_frontend_ajax_object.__WP_VERSION__ > '5'){
        var ele = jQuery('#editor');
        var html = '<div class="misc-pub-section my-options" id="plag_button"><div class="pcp-lang-radio-div"><input type="radio" name="lang" class="pcp-lang-radio" value="English" checked="checked"><span>English</span> <input type="radio" name="lang" class="pcp-lang-radio" value="Other"><span> Other Language</span> </div> <a href="#plagiarism_online_meta_id"><input value="Check Plagiarism" class="button button-primary button-large" name="plag_submit" id="plag_submit" type="button" style="border-radius: 0px;"></a><div id="loading_ticker_plag" style="display: none;"></div></div>';
        window.setTimeout(function () {
            jQuery(ele).find('.edit-post-header__settings').prepend(html);
        }, 1500); 
    }

    jQuery('#loading_ticker_plag').css({
        'display': 'none'
    });
    jQuery('#error_div').css({
        'display': 'none'
    });
    jQuery('#plagiarism_online_meta_id').css({
        'display': 'none'
    });
    jQuery(document).on("click","#plag_submit", function () {
        jQuery('#loading_ticker_plag').css({
            'background': 'url("' + wg_pcp_frontend_ajax_object.spinner + '")',
            'background-size': 'cover',
            'display': 'block',
            'float': 'right',
            'height': '30px',
            'margin-left': '-30px',
            'width': '30px'
        });
        jQuery('#plagiarism_result').css({
            'background': 'url("' + wg_pcp_frontend_ajax_object.spinner + '")',
            'background-size': 'cover',
            'height': '70px',
            'text-align': 'center',
            'margin': '0px auto',
            'width': '70px'
        });
        jQuery('#plagiarism_online_meta_id').css({
            'display': 'block'
        });

        function get_tinymce_content() {
            if (jQuery("#wp-content-wrap").hasClass("tmce-active")) {
                var a = tinymce.editors.content.getContent();
                return jQuery(a).text();
            } else {
                return jQuery('.block-editor-rich-text').val();
            }
        }


        var a = get_tinymce_content(),
            d = new Date(),
            month = d.getMonth() + 1,
            day = d.getDate(),
            output = d.getFullYear() + '-' + (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day,
            data = jQuery('#plagiarism_active').serialize(),
            task = 'plagiarism';
            lang = jQuery("input[name='lang']:checked").val();
            postId = jQuery('#pcp_post_id').val();
            if(wg_pcp_frontend_ajax_object.__WP_VERSION__ > '5'){
                var activation_key = wg_pcp_frontend_ajax_object.__ARGS_K__;
                var pass = wg_pcp_frontend_ajax_object.__ARGS_S__;
                var query = wg_pcp_frontend_ajax_object.__ARGS_Q__;
                var plan = wg_pcp_frontend_ajax_object.__ARGS_P__;
                var x_domains = wg_pcp_frontend_ajax_object.__ARGS_XD__;
                a = jQuery(".mce-content-body").text();
            } else {
                var activation_key = jQuery('#pl_activekey').val();
                var pass = jQuery('#pl_pass').val();
                var query = jQuery('#pl_query').val();
                var plan = jQuery('#_plan_').val();
            }
            
        jQuery.ajax({
            type: "POST",
            url: 'https://checkplag.whizzygeeks.com/plag-activate.php',
            data: {
                'active_key': activation_key,
                'password': pass,
                'date': output,
                'plan': plan,
                'query': query,
                'task': task,
                'language' : lang
            },
            success: function (res) {
                var result = JSON.parse(res),
                    ht_div = result.html_div;
                if (ht_div != '') {
                    jQuery("#plagiarism_result").html(ht_div);
                    jQuery('#plagiarism_result').css({
                        'background': 'none',
                        'background-size': 'none',
                        'float': 'none',
                        'height': '100%',
                        'margin-left': '0px',
                        'width': '100%'
                    });
                    jQuery('#loading_ticker_plag').css({
                        'background': 'url("' + wg_pcp_frontend_ajax_object.ticker + '")',
                        'background-size': 'cover'
                    });
                } else {
                    var status = result.status,
                        query_left = result.query_left,
                        bypassdb = result.bypassdb,
                        website = result.website,
                        a_key = result.key;
                    if (status == "ok") {
                        if(wg_pcp_frontend_ajax_object.__WP_VERSION__ > '5'){
                            var t = jQuery(".editor-post-title__input").val();
                            var post = jQuery(".rich-text").text();
                            if(typeof t == 'undefined'){
                                var t = jQuery("#title").val(),
                                pt = get_tinymce_content().replace(':', ''),
                                post = pt.replace(';', '');
                                post = post.replace(/\r\n|\n|\r/gm, '');
                            }
                        } else {
                            var t = jQuery("#title").val();
                            pt = a.replace(':', ''),
                            post = pt.replace(';', '');
                        }
                       
                        var qt = t.replace(':', ''),
                        title = qt.replace(';', ''),
                        text = title + '/plag/' + post;
                        jQuery.ajax({
                            type: "POST",
                            url: 'https://checkplag.whizzygeeks.com/check-plg.php',
                            data: {
                                'title': text,
                                'website': website,
                                'active_key': a_key,
                                'bypassdb': bypassdb,
                                'x_domains' : x_domains,
                                'language' : lang,
                                'post_id' : postId
                            },
                            success: function (res) {
                                var result = JSON.parse(res),
                                    plag_html = result.html,
                                    q_left = result.query_left;
                                jQuery("#plagiarism_result").html(plag_html);
                                jQuery('#plagiarism_result').css({
                                    'background': 'none',
                                    'background-size': 'none',
                                    'float': 'none',
                                    'height': '100%',
                                    'margin-left': '0px',
                                    'width': '100%'
                                });
                                jQuery('#loading_ticker_plag').css({
                                    'background': 'url("' + wg_pcp_frontend_ajax_object.ticker + '")',
                                    'background-size': 'cover'
                                });
                                jQuery.ajax({
                                    type: "POST",
                                    url: wg_pcp_frontend_ajax_object.ajaxurl,
                                    data: {
                                        action: 'wg_pcp_store_values',
                                        'status': 'query_left',
                                        'plag_html': plag_html,
                                        'query_left': q_left
                                    },
                                    success: function (res) {
                                        var result = JSON.parse(res);
                                    },
                                    error: function (jqXHR, textStatus) {
                                        jQuery("#plagiarism_result").html('<div class="container" style="width: 95%; margin: 0px auto; padding: 0px 0px 30px;  border-radius: 10px;"><div class="row"><div class="col-sm-12"><h2 style="text-align: center; margin-bottom: -20px; color: rgb(250, 169, 22); font-size: 22px; font-weight: 700;">Error #PCP 14</h2><h4 style="text-align: center; font-size: 15px;">Article parsing error</h4><p>Please contact our support team at <a href="https://www.plagiarismcheckerpro.com/#support">www.plagiarismcheckerpro.com in case of any query.</p></div></div>');
                                    }
                                });
                            },
                            error: function (jqXHR, textStatus) {
                                jQuery("#plagiarism_result").html('<div class="container" style="width: 95%; margin: 0px auto; padding: 0px 0px 30px;  border-radius: 10px;"><div class="row"><div class="col-sm-12"><h2 style="text-align: center; margin-bottom: -20px; color: rgb(250, 169, 22); font-size: 22px; font-weight: 700;">Error #PCP 14</h2><h4 style="text-align: center; font-size: 15px;">Article parsing error</h4><p>Please contact our support team at <a href="https://www.plagiarismcheckerpro.com/#support">www.plagiarismcheckerpro.com in case of any query.</p></div></div>');
                            }
                        });
                    }
                }
            },
            error: function (jqXHR, textStatus) {
                jQuery("#plagiarism_result").html('<div class="container" style="width: 95%; margin: 0px auto; padding: 0px 0px 30px;  border-radius: 10px;"><div class="row"><div class="col-sm-12"><h2 style="text-align: center; margin-bottom: -20px; color: rgb(250, 169, 22); font-size: 22px; font-weight: 700;">Error #PCP 14</h2><h4 style="text-align: center; font-size: 15px;">Article parsing error</h4><p>Please contact our support team at <a href="https://www.plagiarismcheckerpro.com/#support">www.plagiarismcheckerpro.com in case of any query.</p></div></div>');
            }
        });
    });
    jQuery("#plagiarism_active").submit(function (e) {
        e.preventDefault();
        var url = jQuery('#plugin_uri').val(),
            d = new Date(),
            month = d.getMonth() + 1,
            day = d.getDate(),
            output = d.getFullYear() + '-' + (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day,
            data = jQuery('#plagiarism_active').serialize(),
            task = 'activation_pl';

        jQuery.ajax({
            type: "POST",
            url: 'https://checkplag.whizzygeeks.com/plag-activate.php',
            data: data + '&date=' + output + '&task=' + task,
            success: function (res) {
                var result1 = JSON.parse(res),
                    ht_div = result1.html_div;
                if (ht_div != '') {
                    jQuery("#error_div").css({
                        'display': 'block'
                    });
                    jQuery("#error_div").html(ht_div);
                } else {
                    var status = result1.status,
                        plan = result1.plan,
                        task = result1.task,
                        key = result1.active_key,
                        pass = result1.password,
                        website = result1.website,
                        name = result1.name,
                        email = result1.email,
                        query = result1.query,
                        data = {
                            action: 'wg_pcp_store_values',
                            status: status,
                            task: task,
                            active_key: key,
                            password: pass,
                            website: website,
                            name: name,
                            email: email,
                            query: query,
                            plan: plan
                        };
                    if (status == 1) {
                        jQuery.ajax({
                            type: "POST",
                            url: wg_pcp_frontend_ajax_object.ajaxurl,
                            data: data,
                            success: function (msg) {
                                var result = JSON.parse(msg);
                                if (result.status == "1") {
                                    window.alert("Plugin is activated.");
                                    window.location.reload();
                                } else {
                                    window.alert("Plugin is deactivated.<br/>Please activate it again.");
                                    window.location.reload();
                                }
                            },
                            error: function (jqXHR, textStatus) {
                                jQuery("#error_div").html('<div class="container" style="width: 95%; margin: 0px auto; padding: 0px 0px 30px;  border-radius: 10px;"><div class="row"><div class="col-sm-12"><h2 style="text-align: center; margin-bottom: -20px; color: rgb(250, 169, 22); font-size: 22px; font-weight: 700;">Error #PCP 14</h2><h4 style="text-align: center; font-size: 15px;">Article parsing error</h4><p>Please contact our support team at <a href="https://www.plagiarismcheckerpro.com/#support">www.plagiarismcheckerpro.com in case of any query.</p></div></div>');
                            }
                        });
                    }
                }
            },
            error: function (jqXHR, textStatus) {
                jQuery("#error_div").html('<div class="container" style="width: 95%; margin: 0px auto; padding: 0px 0px 30px;  border-radius: 10px;"><div class="row"><div class="col-sm-12"><h2 style="text-align: center; margin-bottom: -20px; color: rgb(250, 169, 22); font-size: 22px; font-weight: 700;">Error #PCP 14</h2><h4 style="text-align: center; font-size: 15px;">Article parsing error</h4><p>Please contact our support team at <a href="https://www.plagiarismcheckerpro.com/#support">www.plagiarismcheckerpro.com in case of any query.</p></div></div>');
            }
        });
    });
    window.setInterval(function () {
        var d = new Date(),
            month = d.getMonth() + 1,
            day = d.getDate(),
            output = d.getFullYear() + '-' + (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day,
            data = jQuery('#plagiarism_active').serialize(),
            task = 'check_pl';

            if(wg_pcp_frontend_ajax_object.__WP_VERSION__ > '5'){
                var activation_key = wg_pcp_frontend_ajax_object.__ARGS_K__;
                var pass = wg_pcp_frontend_ajax_object.__ARGS_S__;
                var query = wg_pcp_frontend_ajax_object.__ARGS_Q__;

            } else {
                var activation_key = jQuery('#pl_activekey').val();
                var pass = jQuery('#pl_pass').val();
                var query = jQuery('#pl_query').val();
            }

        jQuery.ajax({
            url: 'https://checkplag.whizzygeeks.com/plag-activate.php',
            data: {
                'active_key': activation_key,
                'password': pass,
                'query': query,
                'date': output,
                'task': task
            },
            success: function (res) {
                var result = JSON.parse(res),
                    status = result.status,
                    plan = result.plan,
                    task = result.task,
                    key = result.active_key,
                    pass = result.password,
                    website = result.website,
                    name = result.name,
                    email = result.email,
                    query = result.query,
                    data = {
                        action: 'wg_pcp_store_values',
                        status: status,
                        task: task,
                        active_key: key,
                        password: pass,
                        website: website,
                        name: name,
                        email: email,
                        query: query,
                        plan: plan
                    };
                jQuery.ajax({
                    url: wg_pcp_frontend_ajax_object.ajaxurl,
                    data: data,
                    success: function (res) {
                        var result = JSON.parse(res),
                            status = result.status;
                        if (status == "0") {
                            window.alert("Plugin is deactivated.<br/>Please activate it again.");
                            window.location.reload();
                        }
                    },
                    error: function (jqXHR, textStatus) {
                        jQuery("#error_div").html('<div class="container" style="width: 95%; margin: 0px auto; padding: 0px 0px 30px;  border-radius: 10px;"><div class="row"><div class="col-sm-12"><h2 style="text-align: center; margin-bottom: -20px; color: rgb(250, 169, 22); font-size: 22px; font-weight: 700;">Error #PCP 14</h2><h4 style="text-align: center; font-size: 15px;">Article parsing error</h4><p>Please contact our support team at <a href="https://www.plagiarismcheckerpro.com/#support">www.plagiarismcheckerpro.com in case of any query.</p></div></div>');
                    }
                });
            },
            error: function (jqXHR, textStatus) {
                jQuery("#error_div").html('<div class="container" style="width: 95%; margin: 0px auto; padding: 0px 0px 30px;  border-radius: 10px;"><div class="row"><div class="col-sm-12"><h2 style="text-align: center; margin-bottom: -20px; color: rgb(250, 169, 22); font-size: 22px; font-weight: 700;">Error #PCP 14</h2><h4 style="text-align: center; font-size: 15px;">Article parsing error</h4><p>Please contact our support team at <a href="https://www.plagiarismcheckerpro.com/#support">www.plagiarismcheckerpro.com in case of any query.</p></div></div>');
            }
        });
    }, 18000000);
    jQuery("#account_details").on("click", function () {
        jQuery.ajax({
            type: "POST",
            url: wg_pcp_frontend_ajax_object.ajaxurl,
            data: {
                'action': 'wg_pcp_edit_account'
            },
            success: function (res) {
                var result = JSON.parse(res);
                jQuery("#account").html(result);
                jQuery("#account_form").submit(function (e) {
                    e.preventDefault();
                    var data = jQuery('#account_form').serialize(),
                        task = 'account_pl';
                    jQuery.ajax({
                        type: "POST",
                        url: 'https://checkplag.whizzygeeks.com/plag-form.php',
                        data: {
                            'action': 'wg_pcp_edit_account',
                            'data' : data
                        },
                        success: function (res) {
                            jQuery.ajax({
                                type: 'POST',
                                url: wg_pcp_frontend_ajax_object.ajaxurl,
                                data: data + '&status=' + task + '&action=wg_pcp_store_values',
                                success: function (res) {
                                    var result = JSON.parse(res);
                                    window.location.reload();
                                }
                            });
                        }
                    });    
                  
                });
            },
            error: function (jqXHR, textStatus) {
                jQuery("#error_div").html('<div class="container" style="width: 95%; margin: 0px auto; padding: 0px 0px 30px;  border-radius: 10px;"><div class="row"><div class="col-sm-12"><h2 style="text-align: center; margin-bottom: -20px; color: rgb(250, 169, 22); font-size: 22px; font-weight: 700;">Error #PCP 14</h2><h4 style="text-align: center; font-size: 15px;">Article parsing error</h4><p>Please contact our support team at <a href="https://www.plagiarismcheckerpro.com/#support">www.plagiarismcheckerpro.com in case of any query.</p></div></div>');
            }
        });
    });
    jQuery("#setting_details").on("click", function () {
        jQuery.ajax({
            type: "POST",
            url: wg_pcp_frontend_ajax_object.ajaxurl,
            data: {
                'action': 'wg_pcp_edit_setting'
            },
            success: function (res) {
                var result = JSON.parse(res);
                jQuery("#setting").html(result);
                jQuery("#setting_form").submit(function (e) {
                    e.preventDefault();
                    var data = jQuery('#setting_form').serialize(),
                        task = 'setting';
                    jQuery.ajax({
                        type: 'POST',
                        url: wg_pcp_frontend_ajax_object.ajaxurl,
                        data: data + '&status=' + task + '&action=wg_pcp_store_values',
                        success: function (res) {
                            var result = JSON.parse(res);
                            window.location.reload();
                        }
                    });
                });
            },
            error: function (jqXHR, textStatus) {
                jQuery("#error_div").html('<div class="container" style="width: 95%; margin: 0px auto; padding: 0px 0px 30px;  border-radius: 10px;"><div class="row"><div class="col-sm-12"><h2 style="text-align: center; margin-bottom: -20px; color: rgb(250, 169, 22); font-size: 22px; font-weight: 700;">Error #PCP 14</h2><h4 style="text-align: center; font-size: 15px;">Article parsing error</h4><p>Please contact our support team at <a href="https://www.plagiarismcheckerpro.com/#support">www.plagiarismcheckerpro.com in case of any query.</p></div></div>');
            }
        });
    }); 

    jQuery("#setting_details").on("click", function () {
       
    });

});
