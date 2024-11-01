<?php
    /*
	   Plugin Name: WG Plagiarism Checker Pro
	   Plugin URI:  https://www.plagiarismcheckerpro.com/
	   Description: Check plagiarized content in your wordpress articles. And help you to maintain your article's uniqueness.
       Version:     2.4.0
	   Author:      Whizzygeeks
	   Author URI:  https://www.whizzygeeks.com/
   */

    if ( ! defined( 'ABSPATH' ) )
    {
        echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
        exit;
    }
    else
    {   
        function decrypt( $value ){
            $methods = openssl_get_cipher_methods();
            $iv = "1234567812345678";
            $secretKey = "glop";

            $key = openssl_decrypt($value, 'AES-128-CBC', $secretKey, 0, $iv);

            return $key;
        }/*decrypting credentials end*/


        function wg_pcp_plagiarism_uninstall()
        {
            function delete_wg_pcp_options($wg_pcp_option)
            {
                if(get_option($wg_pcp_option) !== false)
                {
                    delete_option($wg_pcp_option);
                }
            }

            $wg_pcp_option = array('_wg_pcp-wp_plan', '_wg_pcp-wp_status', '_wg_pcp_plan', '_wg_pcp_status', 'wg_pcp_query', 'wg_pcp_email', 'wg_pcp_name', 'wg_pcp_website', 'wg_pcp_pass', 'wg_pcp_activekey', '_wg_pcp_ status_code', '_wg_pcp_plan_', 'wg_pcp_ex_domains', 'wg_pcp_post_type');

            for($i=0; $i<count($wg_pcp_option); $i++)
            {
                delete_wg_pcp_options($wg_pcp_option[$i]);
            }
        }
        register_uninstall_hook(__FILE__, 'wg_pcp_plagiarism_uninstall');
        
        function wg_pcp_plagiarism_install()
        {
            wg_pcp_plagiarism_uninstall();
        }
        register_activation_hook( __FILE__, 'wg_pcp_plagiarism_install' );
        
        /*adding script and css files in header*/
        function wg_pcp_admin_register_head() 
        {
            if( is_admin() )
            {   
                global $wp_version; 
                $all_plugs = get_plugins();
                $is_editor_installed = array_key_exists('classic-editor/classic-editor.php', $all_plugs);
                
                wp_register_style( 'style',  plugin_dir_url( __FILE__ ) . 'assets/css/style.css', '', '2.4.0' );
                wp_enqueue_style( 'style' );
                wp_register_script( 'script-js', plugin_dir_url( __FILE__ ) . 'assets/js/script.js' , '', '2.4.0', true );

                wp_localize_script( 'script-js', 'wg_pcp_frontend_ajax_object',
                    array( 
                        'ajaxurl'    => admin_url( 'admin-ajax.php' ),
                        'spinner'    => plugin_dir_url( __FILE__ ) . 'assets/spinner.gif',
                        'ticker'     => plugin_dir_url( __FILE__ ) . 'assets/tick.gif',
                        '__WP_VERSION__' => $wp_version,
                        '__ARGS_K__' => esc_html( decrypt( get_option( 'wg_pcp_activekey' ) ) ),
                        '__ARGS_S__' => esc_html(decrypt( get_option( 'wg_pcp_pass' ) )),
                        '__ARGS_P__' => esc_html( get_option( '_wg_pcp_plan_' ) ),
                        '__ARGS_Q__' => esc_html( get_option( 'wg_pcp_query' ) ),
                        '__ARGS_CPT__' => esc_html( get_option( 'wg_pcp_post_type' ) ),
                        '__ARGS_XD__' => esc_html( get_option( 'wg_pcp_ex_domains' ) ),
                        '__ARGS_CL_EDTR__' => $is_editor_installed,
                        
                    )
                );
                wp_enqueue_script( 'script-js' ); 
                wp_register_script( 'f6a7b9fccc-js', plugin_dir_url( __FILE__ ) . 'assets/js/f6a7b9fccc.js' , '', '', true );
                wp_enqueue_script( 'f6a7b9fccc-js' );
               
            }
            else
            {
                echo "<div class='notice notice-info'><p>Sorry! You are not authourized to access this plugin.</p></div>";
            }
        }

        add_action('admin_head', 'wg_pcp_admin_register_head');
        /*adding script and css files in header end*/

        add_action( 'plugins_loaded', 'wg_pcp_plagiarism_init' );

        /*function for loading plugin*/
        function wg_pcp_plagiarism_init() 
        {
            add_action('admin_menu', 'wg_pcp_plagiarism_plugin_setup_menu');
        }

        function wg_pcp_plagiarism_plugin_setup_menu()
        {
            if(get_option('_wg_pcp_status') == 1 && get_option('_wg_pcp-wp_status') == 1)
            {
                /*meta box*/
                function wg_pcp_layers_child_add_meta_box() 
                {
                  $screens = array('post');
                  foreach ( $screens as $screen ) 
                  {
                      add_meta_box(
                        'plagiarism_online_meta_id',
                        __( '<strong style="font-size: 16px; color: #298560; font-family: tahoma;">WG Plagiarism Checker Pro Result</strong><input type="hidden" id="pcp_post_id" name="pcp_post_id" value="'. get_the_ID().'">', 'layerswp' ),
                        'wg_pcp_plagiarism_online_meta_box_callback',
                        $screen,
                            'normal',
                            'high'
                       );
                    }
                }
                add_action( 'add_meta_boxes', 'wg_pcp_layers_child_add_meta_box' );

                function wg_pcp_plagiarism_online_meta_box_callback()
                {
                    echo '<p id="plagiarism_result" style="width:100%;"></p>';
                }

                add_action( 'post_submitbox_misc_actions', 'wg_pcp_my_post_submitbox_misc_actions' );
                function wg_pcp_my_post_submitbox_misc_actions($post)
                {
                    wg_pcp_check(); 
                    $post_types = (get_option( 'wg_pcp_post_type' )) ? explode(',', get_option( 'wg_pcp_post_type' )) : '';
                    // die(get_post_type());
                    if($post_types != '' &&  in_array( get_post_type(), $post_types )){
?>
                    <div class="misc-pub-section my-options" id="plag_button">
                        <div class="pcp-lang-radio-div"><input type="radio" name="lang" class="pcp-lang-radio" value="English" checked="checked"><span>English</span> <input type="radio" name="lang" class="pcp-lang-radio" value="Other"><span> Other Language</span> </div> <a href="#plagiarism_online_meta_id"><input type="button" value="Check Plagiarism" class="button button-primary button-large" name="plag_submit" id="plag_submit"></a>
                        <div id="loading_ticker_plag"></div>
                    </div>
<?php
                    }
                }

                add_menu_page( 'WG Plagiarism Checker Pro Plugin Page', 'WG Plagiarism Checker Pro', 'manage_options', 'wg-pcp-plag-settings', 'wg_pcp_plagiarism_admin', plugin_dir_url( __FILE__ ) . 'assets/icon.png' );
                add_submenu_page( 'wg-pcp-plag-settings', 'WG Plagiarism Checker Pro Plugin Page', 'Documentation', 'manage_options', 'wg-pcp-plag-doc', 'wg_pcp_plagiarism_doc');      
            }
            else
            {
                $user = wp_get_current_user();
                if ( in_array( 'administrator', (array) $user->roles ) ) 
                {
                    add_menu_page( 'WG Plagiarism Checker Pro Plugin Page', 'WG Plagiarism Checker Pro Plugin', 'manage_options', 'wg-pcp-plag-settings', 'wg_pcp_plagiarism_admin_check', plugin_dir_url( __FILE__ ).'assets/icon.png' );
                    add_submenu_page( 'wg-pcp-plag-settings', 'WG Plagiarism Checker Pro Plugin Page', 'Documentation', 'manage_options', 'wg-pcp-plag-doc', 'wg_pcp_plagiarism_doc');
                }
                else
                {
                    add_menu_page( 'WG Plagiarism Checker Pro Plugin Page', 'WG Plagiarism Checker Pro Plugin', 'manage_options', 'wg-pcp-plag-settings', 'wg_pcp_plagiarism_admin_check_error', plugin_dir_url( __FILE__ ).'assets/icon.png' );
                }
            }
        }

        function wg_pcp_plagiarism_admin_check_error()
        {
            echo '<div class="notice notice-error">Sorry! You are unauthorized to access WG Plagiarism Checker Pro Plugin.</div>';
        }
        
        function wg_pcp_active_handler()
        {
            if(!empty($_POST['active_key']) && check_admin_referer('wg_pcp_nonce_action','wg_pcp_nonce_field') && strlen($_POST['active_key']) == 32)
            {
                $wg_pcp_key = encrypt(sanitize_text_field( $_POST['active_key'] ));
            }
            else
            {
                $wg_pcp_active_error = 'Activation Key is required if you want to access plugin.';
            }
            if(!empty($_POST['pass']) && check_admin_referer('wg_pcp_nonce_action','wg_pcp_nonce_field') && strlen($_POST['pass']) == 32)
            {
                $wg_pcp_pass = encrypt(sanitize_text_field( $_POST['pass'] ));
            }
            else
            {
                $wg_pcp_pass_error = 'Secret Key is required if you want to access plugin.';
            }
            wp_die();
        }
        add_action("wp_ajax_wg_pcp_active_form", "wp_ajax_wg_pcp_active_handler");

        function wg_pcp_plagiarism_admin_check()
        {
    ?>
        <!--activation form-->
        
        <div class="wrap">
            <div id="error_div"></div>
            <form method="post" enctype="multipart/form-data" name="authentication" action="" id="plagiarism_active">
                <table>
                    <tr>
                        <h4>If you do not have Access and Secret Key you can generate it on <a href="https://www.plagiarismcheckerpro.com/">www.plagiarismcheckerpro.com</a></h4>
                    </tr>
                    <tr>
                        <th valign="top"><label for="activation_key">Access Key</label></th>
                        <td>
                            <input type="text" name="active_key" placeholder="Activation Key" value="<?php 
                                    if( get_option('wg_pcp_activekey') !== false )
                                    {
                                        echo esc_html( decrypt( get_option( 'wg_pcp_activekey' ) ) ); 
                                    }
                            ?>" maxlength="32" required>
                        </td>
                        <td valign="middle">
                            <div class="tooltip">
                                <i class="fa fa-info-circle fa-lg" aria-hidden="true"></i>
                                <span class="tooltiptext tooltip-right" style="width:370px !important; padding-top:2px;">
                                    Access Key goes here which have been provided to you.
                                </span>
                            </div>
                        </td>
                        <?php if( isset( $wg_pcp_active_error ) )
                            {
                        ?>
                        <td valign="middle">
                            <div class="tooltip" style="margin-left:0px">
                                <i class="fa fa-exclamation-circle fa-lg error" aria-hidden="true"></i>
                                <span class="tooltiptext tooltip-right error">
                                    <?php echo esc_html( $wg_pcp_active_error ); ?>
                                </span>
                            </div>
                        </td>
                        <?php 
                            }
                        ?>
                    </tr>
                    <tr>
                        <th valign="top"><label for="password">Secret Key</label></th>
                        <td>
                            <input type="password" name="password" placeholder="Password" value="<?php if( isset($wg_pcp_pass) )
                                { 
                                    echo esc_html($wg_pcp_pass); 
                                }
                            ?>" maxlength="32" required>
                        </td>
                        <td valign="middle">
                            <div class="tooltip">
                                <i class="fa fa-info-circle fa-lg" aria-hidden="true"></i>
                                <span class="tooltiptext tooltip-right" style="width:370px !important; padding-top:2px;">
                                    Secret Key goes here which have been provided to you.
                                </span>
                            </div>
                        </td>
                        <?php 
                            if( isset( $wg_pcp_pass_error ) )
                            {
                        ?>
                        <td valign="middle">
                            <div class="tooltip" style="margin-left:0px">
                                <i class="fa fa-exclamation-circle fa-lg error" aria-hidden="true"></i>
                                <span class="tooltiptext tooltip-right error">
                                    <?php echo esc_html($wg_pcp_pass_error);?>
                                </span>
                            </div>
                        </td>
                        <?php 
                            }
                        ?>
                    </tr>
                    <tr>
                        <td col span="2"><input type="submit" name="activate" value="Activate" class="button-primary" id="activate"></td>
                        <?php wp_nonce_field('wg_pcp_nonce_action','wg_pcp_nonce_field'); ?>
                    </tr>
                </table>
            </form>
        </div>
        <?php
        }

        function wg_pcp_check()
        {
    ?>
            <input type="hidden" value="<?php $wg_pcp_access_key = decrypt( get_option( 'wg_pcp_activekey' ) ); echo esc_html( $wg_pcp_access_key );?>" id="pl_activekey">
            <input type="hidden" value="<?php $wg_pcp_secret_key = decrypt( get_option( 'wg_pcp_pass' ) ); echo esc_html($wg_pcp_secret_key); ?>" id="pl_pass">
            <input type="hidden" value="<?php echo esc_html( get_option( '_wg_pcp_plan_' ) ); ?>" id="_plan_">
            <input type="hidden" value="<?php echo esc_html( get_option( 'wg_pcp_query' ) ); ?>" id="pl_query">
            <?php
        }

        function wg_pcp_plagiarism_admin()
        {
            if( isset( $_POST['submit'] ) )
            {
                if(!empty($_POST['Name']) && check_admin_referer('wg_pcp_edit_nonce_action','wg_pcp_edit_nonce_field') && strlen($_POST['Name']) > 10 && strlen($_POST['Name']) < 50)
                {
                    $wg_pcp_name = sanitize_text_field($_POST['Name']);
                }
                if(!empty($_POST['email']) && check_admin_referer('wg_pcp_edit_nonce_action','wg_pcp_edit_nonce_field')&& strlen($_POST['API']) <= 250)
                {
                    $wg_pcp_email = sanitize_email($_POST['email']);
                }
                if(!empty($_POST['API']) && check_admin_referer('wg_pcp_edit_nonce_action','wg_pcp_edit_nonce_field') && strlen($_POST['API']) == 32)
                {
                    $wg_pcp_key = sanitize_text_field($_POST['API']);
                }
            }
?>
                <script type="text/javascript">
                   function openTab(evt, cityName) {
                  var i, tabcontent, tablinks;
                  tabcontent = document.getElementsByClassName("tabcontent");
                  for (i = 0; i < tabcontent.length; i++) {
                    tabcontent[i].style.display = "none";
                  }
                  tablinks = document.getElementsByClassName("tablinks");
                  for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" active", "");
                  }
                  document.getElementById(cityName).style.display = "block";
                  evt.currentTarget.className += " active";
                }
                </script>
                <div class="wrap">
                    <h2>WG Plagiarism Checker Pro Plugin</h2>
                    <h5>A plugin which showcase use of plagiarized content in your wordpress articles. And help you to maintain your article's uniqueness.</h5>
                </div><br/><br/>


            <div class="tab">
              <button class="tablinks" onclick="openTab(event, 'option')">Subscription information</button>
              <button class="tablinks" onclick="openTab(event, 'setting')">Settings</button>
              <button class="tablinks" onclick="openTab(event, 'reports')" id="report_tab">Reporting Panel</button>
            </div>

            <div id="option" class="tabcontent">
             <div id="account">
                    <table class="account">
                        <caption style="text-align: center;">Account Details</caption>
                        <tr>
                            <th align="left"><label>Name</label></th>
                            <td style="text-transform: capitalize;">
                                <?php   if(get_option('wg_pcp_name') !== false)
                                        {
                                            echo esc_html(decrypt(get_option('wg_pcp_name')));
                                        }
                                        else
                                        {
                                            echo esc_html('name');
                                        }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th align="left"><label>Email Address</label></th>
                            <td>
                                <?php   if(get_option('wg_pcp_email') !== false)
                                        {
                                            echo esc_html(decrypt(get_option('wg_pcp_email')));
                                        }
                                        else
                                        {
                                            echo esc_html('email');
                                        }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th align="left"><label>Queries</label></th>
                            <td>
                                <?php   
                                        $wp_pcp_plan_str = strtolower(get_option('_wg_pcp_plan_'));
                                        if($wp_pcp_plan_str == 'trial')
                                        {
                                            $total_queries = 50; 
                                        }
                                        else if($wp_pcp_plan_str == 'basic')
                                        {
                                            $total_queries = 1000;
                                        }
                                        else if($wp_pcp_plan_str == 'go')
                                        {
                                            $total_queries = 3000;
                                        }
                                        else if($wp_pcp_plan_str == 'go plus')
                                        {
                                            $total_queries = 4250;
                                        }
                                        else if($wp_pcp_plan_str == 'professional')
                                        {
                                            $total_queries = 5100;
                                        }
                                        else if($wp_pcp_plan_str == 'professional plus')
                                        {
                                            $total_queries = 10300;
                                        }
                                        else if($wp_pcp_plan_str == 'business')
                                        {
                                            $total_queries = 51500;
                                        }
                                        else if($wp_pcp_plan_str == 'enterprise')
                                        {
                                            $total_queries = 105000;
                                        }
                                        else
                                        {
                                            $total_queries = 'no';
                                        }
                                        if(get_option('wg_pcp_query') !== false)
                                        {
                                            echo esc_html(get_option('wg_pcp_query')).' Queries left from '.esc_html($total_queries).' Queries.';
                                        }
                                        else
                                        {
                                            echo esc_html('-');
                                        }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th align="left"><label>Plugin Status</label></th>
                            <td>
                                <?php   if(get_option('_wg_pcp_status_code') == 1)
                                        {
                                            echo esc_html('Activated');
                                        }
                                        else
                                        {
                                            echo esc_html('Deactivated');
                                        }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th align="left"><label>API Key</label></th>
                            <td style="font-weight: bold;">
                                <?php   if(get_option('wg_pcp_activekey') !== false)
                                        {
                                            echo esc_html(decrypt(get_option('wg_pcp_activekey')));
                                        }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: none;" colspan="2"><input type="button" id="account_details" value="Edit Details"></td>
                            <?php wp_nonce_field('wg_pcp_edit_nonce_action','wg_pcp_edit_nonce_field'); ?>
                        </tr>
                    </table>
                </div>
            </div>

            <div id="setting" class="tabcontent">
                <div id="option">
                    <table class="option">
                        <caption style="text-align: center;">Settings</caption>
                        <tr>
                            <th align="left"><label>Exclude Domains</label></th>
                            <td style="text-transform: capitalize;">
                                <?php   if(get_option('wg_pcp_ex_domains') !== false)
                                        {
                                            echo esc_html(get_option('wg_pcp_ex_domains'));
                                        }
                                        else
                                        {
                                            echo '-';
                                        }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th align="left"><label>Subscription Type</label></th>
                            <td>
                                <?php   if(get_option('wg_pcp_subs_type') !== false)
                                        {
                                            echo esc_html(decrypt(get_option('wg_pcp_subs_type')));
                                        }
                                        else
                                        {
                                            echo 'English Only';
                                        }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th align="left"><label>Check for Custom Post Type</label></th>
                            <td>
                                <?php   
                                        
                                        if(get_option('wg_pcp_post_type') !== false)
                                        {
                                            echo esc_html(get_option('wg_pcp_post_type'));
                                        }
                                        else
                                        {
                                            echo esc_html('None');
                                        }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th align="left"><label>Check On Update</label></th>
                            <td>
                                <?php   if(get_option('_wg_pcp_check_on_update') == 1)
                                        {
                                            echo esc_html(get_option('_wg_pcp_check_on_update'));
                                        }
                                        else
                                        {
                                            echo 'No';
                                        }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th align="left"><label>Check on New Article</label></th>
                            <td style="">
                                <?php   if(get_option('wg_pcp_check_new_article') !== false)
                                        {
                                            echo esc_html(get_option('wg_pcp_check_new_article'));
                                        } else {
                                            echo 'No';
                                        }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: none;" colspan="2"><input type="button" id="setting_details" value="Edit Setting Details"></td>
                            <?php wp_nonce_field('wg_pcp_edit_nonce_action','wg_pcp_edit_nonce_field'); ?>
                        </tr>
                    </table>
                </div>
            </div>

            <div id="reports" class="tabcontent">
                <div style="width:98%; float:left;padding: 8px;border: 2px solid #dcdcdc;margin-bottom: 5px;"><lable  style="width:9%; float:left;font-size: 16px;font-weight:bold;">Duration : </lable><div style="width: 30%; float:left;font-size: 13px;font-weight:bold;"><span class="days-selected"><lable for="7D">last 7 days </lable><input type="radio" id="7D" class="days-filter" style="margin-top:2px;" checked="checked" name="days-filter"></span></div><div style="width: 30%; float:left;font-size: 13px;font-weight:bold;"><span><lable for="15D">last 15 days </lable><input type="radio" id="15D" class="days-filter" name="days-filter" style="margin-top:2px;"></span></div><div style="width: 30%; float:left;font-size: 13px;font-weight:bold;"><span><lable for="45D">last 45 days</lable> <input type="radio" id="45D" class="days-filter" style="margin-top:2px;" name="days-filter"></span></div></div>
                <div id="reports_option">
                    <table class="option" style="border: 1px solid #008080;">
                        <caption style="text-align: center;">Loading Plagiarism Reports ..</caption>
                        
                    </table>
                </div>
            </div>


               

                <?php
        }/*plagiarism_admin function ends here*/

        function wg_pcp_plagiarism_doc()
        {
?>
                    <div class="wrap">
                        <h2>WG Plagiarism Checker Pro Plugin</h2>
                        <h4>A plugin which showcase use of plagiarized content in your wordpress articles. And help you to maintain your article's uniqueness.</h4>
                    </div>
                    <h2>Documentation</h2>
                    <p><strong>Installation</strong></p>
                    <p><strong>Installation via WordPress Admin area</strong></p>
                    <ol>
                        <li><strong>Step 1.</strong> Log into your WordPress admin area.</li>
                        <li><strong>Step 2.</strong> Click on the left side plugin menu.</li>
                        <li><strong>Step 3.</strong> Now at the top you can see the Add New button, click the button.(As in Fig.1.1)</li>
                        <li><strong>Step 4.</strong> Again at the top you can see/view Upload Plugin button, click the button. (As in Fig.1.2)</li>
                        <li><strong>Step 5.</strong> Now upload the plugin zip file (<strong>online-plagiarism-checker.zip</strong>). (As in Fig.1.3) After upload, install the zip and click on "Activate Plugin".(As in Fig.1.4)</li>
                    </ol>
                    <p style="margin: 0px auto; text-align: center; margin-bottom: 30px;"><img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>assets/images/admin.jpg" style="margin-right: 10%; position: relative; top: -11px;;"><img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>assets/images/upload.png" style="border: 2px solid darkgrey; border-radius: 4px; padding: 5px 10px;"></p>
                    <p style="margin: 0px auto; text-align: center;"><img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>assets/images/fig.1.3.jpg"></p>
                    <p style="text-align: center;">Fig 1.3</p>
                    <p style="margin: 0px auto; text-align: center;"><img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>assets/images/fig.1.4.jpg"></p>
                    <p style="text-align: center;">Fig 1.4</p>
                    <p style="font-weight: 600;"><strong>Quick Guide</strong></p>
                    <p>Please follow these quick steps given below to activate your plugin.</p>
                    <p><strong>Admin &gt;&gt; WG Plagiarism Checker Pro Plugin</strong></p>
                    <h3>Step 1:</h3>
                    <h4>Activate your plugin</h4>
                    <h4>1.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Enter the Access Key which you have been provided at the time of plugin purchase in the first input box.</h4>
                    <p style="margin: 0px auto; text-align: center; margin-bottom: 30px;"><img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>assets/images/access key.png" style="margin-right: 10%; position: relative;"><img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>assets/images/access key ans.jpg" style=""></p>
                    <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h4>
                    <h4>2.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Enter the Secret Key which you have been provided at the time of plugin purchase in the second input box.</h4>
                    <p style="margin: 0px auto; text-align: center; margin-bottom: 30px;"><img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>assets/images/pass.png" style="margin-right: 10%; position: relative;"><img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>assets/images/pass ans.jpg" style=""></p>
                    <h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h4>
                    <h4>3.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Enter the website you have provided us while purchasing the plugin. But be sure that you have given us the same website which you are using for your wordpress site.</h4>
                    <h4>4.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Yippee! Your plugin is activated. You can enjoy its features now.</h4>
                    <p>&nbsp;</p>
                    <p><strong>&nbsp;</strong></p>
                    <h3>Step 2:</h3>
                    <p><strong>Check Plagiarism of the article</strong></p>
                    <h4>1.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; After writing the article, click on the &ldquo;Check Plagiarism&rdquo; button as shown.</h4>
                    <p style="margin: 0px auto; text-align: center; background: url('<?php echo esc_url(plugin_dir_url(__FILE__)); ?>assets/images/article.jpg'); width: 60%; height: 400px; background-size: cover; margin-bottom: 20px;"></p>

                    <h4>2.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; The result will get displayed on the page in &ldquo;WG Plagiarism Checker Pro Result&rdquo; box as shown.</h4>
                    <p style="margin: 0px auto; text-align: center; background: url('<?php echo esc_url(plugin_dir_url(__FILE__)); ?>assets/images/result.jpg'); width: 60%; height: 400px; background-size: cover;"></p>
                    <?php
        }

       

        function encrypt( $str )
        {
            $methods = openssl_get_cipher_methods();
            $iv = "1234567812345678";
            $secretKey = "glop";
            $encrypted = openssl_encrypt($str, 'AES-128-CBC', $secretKey, 0, $iv);
            return $encrypted;
        }
        
        function wg_pcp_store_values()
        {
            $wg_pcp_stat = sanitize_text_field($_POST['status']);
            if( $wg_pcp_stat == 'account_pl')
            {
                
                $wg_pcp_nm      = sanitize_text_field($_POST['Name']);
                $wg_pcp_email   = sanitize_text_field($_POST['email']);
                $wg_pcp_api     = sanitize_text_field($_POST['API']);
                
                if( get_option( 'wg_pcp_name' ) !== false )
                {
                    update_option('wg_pcp_name', encrypt($wg_pcp_nm));
                }
                else
                {
                    add_option('wg_pcp_name', encrypt($wg_pcp_nm));
                }
                
                if( get_option( 'wg_pcp_email' ) !== false )
                {
                    update_option('wg_pcp_email', encrypt($wg_pcp_email));
                }
                else
                {
                    add_option('wg_pcp_email', encrypt($wg_pcp_email));
                }
                if( get_option( 'wg_pcp_activekey' ) !== false )
                {
                    update_option('wg_pcp_activekey', encrypt($wg_pcp_api));
                }
                else
                {
                    add_option('wg_pcp_activekey', encrypt($wg_pcp_api));
                }
                $code = array();
            }
            else if($wg_pcp_stat == 'setting')
            {
                $code = '';
                $ex_domains = sanitize_text_field($_POST['ex_domains']); 
                $post_type = sanitize_text_field($_POST['post_type']); 
                if( get_option( 'wg_pcp_ex_domains' ) !== false )
                {
                    update_option('wg_pcp_ex_domains', $ex_domains);
                }
                else
                {
                    add_option('wg_pcp_ex_domains', $ex_domains);
                } 
                if( get_option( 'wg_pcp_post_type' ) !== false )
                {
                    update_option('wg_pcp_post_type', $post_type);
                }
                else
                {
                    add_option('wg_pcp_post_type', $post_type);
                }
                $code = $plag;
            }
            else if($wg_pcp_stat == 'query_left')
            {
                $code = '';
                $status = sanitize_text_field($_POST['status']); 
                $query_left = sanitize_text_field($_POST['query_left']); 
                $plag = sanitize_text_field($_POST['plag_html']); 
                if( get_option( 'wg_pcp_query' ) !== false )
                {
                    update_option('wg_pcp_query', $query_left);
                }
                else
                {
                    add_option('wg_pcp_query', $query_left);
                }
                $code = $plag;
            }
            else
            {
                $wg_pcp_status_m = sanitize_text_field($_POST['status']);
                $wg_pcp_plan_m   = sanitize_text_field($_POST['plan']);
                $wg_pcp_task     = sanitize_text_field($_POST['task']);
                $wg_pcp_key      = sanitize_text_field($_POST['active_key']);
                $wg_pcp_pass     = sanitize_text_field($_POST['password']);
                $wg_pcp_website  = sanitize_text_field($_POST['website']);
                $wg_pcp_name     = sanitize_text_field($_POST['name']);
                $wg_pcp_email    = sanitize_text_field($_POST['email']);
                $wg_pcp_query    = intval(sanitize_text_field($_POST['query']));

                if( $wg_pcp_task == 'activation_pl')
                {
                    if( get_option( 'wg_pcp_activekey' ) !== false )
                    {
                        update_option('wg_pcp_activekey', encrypt($wg_pcp_key));
                    }
                    else
                    {
                        add_option('wg_pcp_activekey', encrypt($wg_pcp_key));
                    }
                    if( get_option( 'wg_pcp_pass' ) !== false )
                    {
                        update_option('wg_pcp_pass', encrypt($wg_pcp_pass));
                    }
                    else
                    {
                        add_option('wg_pcp_pass', encrypt($wg_pcp_pass));
                    }
                    if( get_option( 'wg_pcp_website' ) !== false )
                    {
                        update_option('wg_pcp_website', encrypt($wg_pcp_website));
                    }
                    else
                    {
                        add_option('wg_pcp_website', encrypt($wg_pcp_website));
                    }
                    if( get_option( 'wg_pcp_name' ) !== false )
                    {
                        update_option('wg_pcp_name', encrypt($wg_pcp_name));
                    }
                    else
                    {
                        add_option('wg_pcp_name', encrypt($wg_pcp_name));
                    }
                    if( get_option( 'wg_pcp_email' ) !== false )
                    {
                        update_option('wg_pcp_email', encrypt($wg_pcp_email));
                    }
                    else
                    {
                        add_option('wg_pcp_email', encrypt($wg_pcp_email));
                    }
                    if( get_option( 'wg_pcp_query' ) !== false )
                    {
                        update_option('wg_pcp_query', encrypt($wg_pcp_query));
                    }
                    else
                    {
                        add_option('wg_pcp_query', encrypt($wg_pcp_query));
                    }
                }


                if( !get_option('_wg_pcp_status') )
                {
                    update_option('_wg_pcp_status', $wg_pcp_status_m);
                    $status = $wg_pcp_status_m;
                }
                else
                {
                    add_option('_wg_pcp_status', $wg_pcp_status_m);
                    $status = $wg_pcp_status_m;
                }

                if( !get_option('_wg_pcp_plan') )
                {
                    update_option('_wg_pcp_plan', $wg_pcp_plan_m);
                    $plan = $wg_pcp_plan_m;
                }
                else
                {
                    add_option('_wg_pcp_plan', $wg_pcp_plan_m);
                    $plan = $wg_pcp_plan_m;
                }
                if( !get_option('_wg_pcp-wp_status') )
                {
                    update_option('_wg_pcp-wp_status', $wg_pcp_status_m);
                    $status = $wg_pcp_status_m;
                }
                else
                {
                    add_option('_wg_pcp-wp_status', $wg_pcp_status_m);
                    $status = $wg_pcp_status_m;
                }

                if( !get_option('_wg_pcp-wp_plan') )
                {
                    update_option('_wg_pcp-wp_plan', $wg_pcp_plan_m);
                    $plan = $wg_pcp_plan_m;
                }
                else
                {
                    add_option('_wg_pcp-wp_plan', $wg_pcp_plan_m);
                    $plan = $wg_pcp_plan_m;
                }
                if( !get_option('_wg_pcp_status_code') )
                {
                    update_option('_wg_pcp_status_code', $wg_pcp_status_m);
                    $status = $wg_pcp_status_m;
                }
                else
                {
                    add_option('_wg_pcp_status_code', $wg_pcp_status_m);
                    $status = $wg_pcp_status_m;
                }

                if( !get_option('_wg_pcp_plan_') )
                {
                    update_option('_wg_pcp_plan_', $wg_pcp_plan_m);
                    $plan = $wg_pcp_plan_m;
                }
                else
                {
                    add_option('_wg_pcp_plan_', $wg_pcp_plan_m);
                    $plan = $wg_pcp_plan_m;
                }
                if( get_option( 'wg_pcp_query' ) !== false )
                {
                    update_option('wg_pcp_query', $wg_pcp_query);
                }
                else
                {
                    add_option('wg_pcp_query', $wg_pcp_query);
                }
                $code = array(
                    'status'    => $status,
                    'plan'      => $plan
                );
            }
            
            ob_clean();
            echo json_encode($code);
            wp_die();
        }
        add_action( 'wp_ajax_wg_pcp_store_values', 'wg_pcp_store_values' );
        
        function wg_pcp_edit_account()
        {
            if(get_option('wg_pcp_name') !== false)
            {
                $wg_pcp_name = decrypt(get_option('wg_pcp_name'));
            }
            else
            {
                $wg_pcp_name = '';
            }
            if(get_option('wg_pcp_email') !== false)
            {
                $wg_pcp_email = decrypt(get_option('wg_pcp_email'));
            }
            else
            {
                $wg_pcp_email = '';
            }
            if(get_option('wg_pcp_activekey') !== false)
            {
                $wg_pcp_key = decrypt(get_option('wg_pcp_activekey'));
            }
            else
            {
                $wg_pcp_key = '';
            }
            $a = '<form method="post" name="account" id="account_form">
                    <table class="account" id="account_change">
                    <caption style="text-align: center;">Account Details</caption>
                            <tr>
                                <th align="left"><label>Name</label></th>
                                <td style="text-transform: capitalize;"><input type="text" value="'.esc_html($wg_pcp_name).'" name="Name"></td>
                            </tr>
                            <tr>
                                <th align="left"><label>Email Address</label></th>
                                <td><input type="email" value="'.esc_html($wg_pcp_email).'" name="email"></td>
                            </tr>
                            <tr>
                                <th align="left"><label>API Key</label></th>
                                <td style="font-weight: bold;"><input type="text" value="'.esc_html($wg_pcp_key).'" name="API"></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; border: none;" colspan="2"><input type="submit" id="submit_del" name="submit_del" value="Submit"></td>
                            </tr>
                            <input type="hidden" value="'.esc_html($uri2).'" id="pl_plugin_uri_main">
                            <input type="hidden" value="'.esc_html($wg_pcp_key).'" id="pl_plugin_user_key">
                    </table>
                </form>';
            echo json_encode($a);
            exit;
        }
        add_action( 'wp_ajax_wg_pcp_edit_account' , 'wg_pcp_edit_account' );
        
        function wg_pcp_edit_setting()
        {
            if(get_option('wg_pcp_ex_domains') !== false)
            {
                $wg_pcp_ex_domains = get_option('wg_pcp_ex_domains');
            }
            else
            {
                $wg_pcp_ex_domains = '';
            }
            if(get_option('wg_pcp_post_type') !== false)
            {
                $wg_pcp_post_type = get_option('wg_pcp_post_type');
            }
            else
            {
                $wg_pcp_post_type = '';
            }
            if(get_option('wg_pcp_activekey') !== false)
            {
                $wg_pcp_key = decrypt(get_option('wg_pcp_activekey'));
            }
            else
            {
                $wg_pcp_key = '';
            }
            $a = '<form method="post" name="setting" id="setting_form">
                    <table class="account" id="account_change">
                    <caption style="text-align: center;">Account Settings</caption>
                            <tr>
                                <th align="left"><label>Exclude Domains<br><small>Note : Seperated by commas{,}</small></label></th>
                                <td style="text-transform: capitalize;"><input type="text" value="'.esc_html($wg_pcp_ex_domains).'" name="ex_domains"></td>
                            </tr>
                            <tr>
                                <th align="left"><label>Check for Custom Post Type</label></th>
                                <td><input type="text" value="'.esc_html($wg_pcp_post_type).'" name="post_type"></td>
                            </tr>
                         
                            <tr>
                                <td style="font-weight: bold; border: none;" colspan="2"><input type="submit" id="submit_del" name="submit_del" value="Submit"></td>
                            </tr>
                            <input type="hidden" value="'.esc_html($uri2).'" id="pl_plugin_uri_main">
                    </table>
                </form>';
            echo json_encode($a);
            exit;
        }
        add_action( 'wp_ajax_wg_pcp_edit_setting' , 'wg_pcp_edit_setting' );
    }
?>
