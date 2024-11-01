<?php
/*
Plugin Name: Submittable
Plugin URI: https://wordpress.org/plugins/submission-manager-by-submittable/
Description: Plugin for integrating Submittable&trade; data into your WordPress powered website.
Author: Submittable
Contributor: S. Reahard
Version: 2.02
*/

// ------------------------------------------------------------------------
// REQUIRE MINIMUM VERSION OF WORDPRESS:
// ------------------------------------------------------------------------

function requires_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );

	if ( version_compare($wp_version, "3.0", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 3.0 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
}
add_action( 'admin_init', 'requires_wordpress_version' );

// ------------------------------------------------------------------------
// PLUGIN PREFIX:
// ------------------------------------------------------------------------

// 'submittable_' prefix

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------

register_activation_hook(__FILE__, 'submittable_add_defaults');
register_uninstall_hook(__FILE__, 'submittable_delete_plugin_options');
add_action('admin_init', 'submittable_init' );
add_action('admin_menu', 'submittable_add_options_page');
add_filter( 'plugin_action_links', 'submittable_plugin_action_links', 10, 2 );

// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'submittable_delete_plugin_options')
// --------------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE USER DEACTIVATES AND DELETES THE PLUGIN. IT SIMPLY DELETES
// THE PLUGIN OPTIONS DB ENTRY (WHICH IS AN ARRAY STORING ALL THE PLUGIN OPTIONS).
// --------------------------------------------------------------------------------------

// Delete options table entries ONLY when plugin deactivated AND deleted
function submittable_delete_plugin_options() {
	delete_option('submittable_options');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'submittable_add_defaults')
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE PLUGIN IS ACTIVATED. IF THERE ARE NO THEME OPTIONS
// CURRENTLY SET, OR THE USER HAS SELECTED THE CHECKBOX TO RESET OPTIONS TO THEIR
// DEFAULTS THEN THE OPTIONS ARE SET/RESET.
//
// OTHERWISE, THE PLUGIN OPTIONS REMAIN UNCHANGED.
// ------------------------------------------------------------------------------

// Define default option settings
function submittable_add_defaults() {
	$tmp = get_option('submittable_options');
    if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
		delete_option('submittable_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$arr = array(	"chk_default_options_db" => "",
						"subdomain" => "",
						"button_label" => "",
						"show_fees" => "",
						"show_main_description" => "",
						"include_css" => "yes",
                        "remove_branding" => "",
		);
		update_option('submittable_options', $arr);
	}
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_init', 'submittable_init' )
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_init' HOOK FIRES, AND REGISTERS THE PLUGIN
// SETTINGS WITH THE WORDPRESS SETTINGS API.
// ------------------------------------------------------------------------------

// Init plugin options to white list our options
function submittable_init(){
	register_setting( 'submittable_plugin_options', 'submittable_options', 'submittable_validate_options' );
	wp_enqueue_style('submittable-default-css', plugin_dir_url(__FILE__).'css/submittable-admin.css');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_menu', 'submittable_add_options_page');
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_menu' HOOK FIRES, AND ADDS THE NEW OPTIONS
// PAGE TO THE SETTINGS MENU.
// ------------------------------------------------------------------------------

// Add menu page
function submittable_add_options_page() {
	add_options_page(__('Submittable Plugin Options', 'submittable'), __('Submittable', 'submittable'), 'manage_options', __FILE__, 'submittable_render_form');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION SPECIFIED IN: add_options_page()
// ------------------------------------------------------------------------------
// THIS FUNCTION IS SPECIFIED IN add_options_page() AS THE CALLBACK FUNCTION THAT
// ACTUALLY RENDER THE PLUGIN OPTIONS FORM AS A SUB-MENU UNDER THE EXISTING
// SETTINGS ADMIN MENU.
// ------------------------------------------------------------------------------

// Render the Plugin options form
function submittable_render_form() {
	?>
	<div class="wrap">

		<!-- Display Plugin Icon, Header, and Description -->

		<div class="icon32" id="submittable_admin_icon"><br></div>

		<h2><?php _e('Submittable&trade; Plugin Options', 'submittable'); ?></h2>

		<!-- Beginning of the Plugin Options Form -->
		<form method="post" action="options.php">

			<?php settings_fields('submittable_plugin_options'); ?>
			<?php $options = get_option('submittable_options'); ?>

            <?php // print_r($options); ?>

			<!-- Table Structure Containing Form Controls -->
			<!-- Each Plugin Option Defined on a New Table Row -->
			<table class="form-table">

                <!-- Usage -->
                <tr valign="top">
					<th scope="row"><?php _e('Shortcode Usage', 'submittable'); ?><pre>[submittable]</pre></th>
					<td>
                    <?php _e('Simply add the Submittable&trade; shortcode ( [submittable] ) to any post or page in your site. <br /><i>Be sure to also enter your custom Submittable&trade; subdomain below.</i>', 'submittable'); ?>
					</td>
				</tr>

				<tr><td colspan="2"><div class="submittable-spacer"></div></td></tr>

				<!-- Subdomain -->
				<tr >
					<th scope="row"><?php _e('Submittable&trade; Subdomain', 'submittable'); ?><br /><span class="submittable-input-span">(e.g: http://YOURORGNAME.submittable.com)</span></th>
					<td>
						<input type="text" size="20" name="submittable_options[subdomain]" value="<?php echo $options['subdomain']; ?>" /><span class="submittable-input-span"><?php _e('Only enter the SUBDOMAIN (e.g. YOURORGNAME), not the full domain.', 'submittable'); ?></span>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e('Custom Button Text', 'submittable'); ?><br /><span class="submittable-input-span"><?php _e('(e.g. "Apply", "Learn More")', 'submittable'); ?></span></th>
					<td>
						<input type="text" size="20" name="submittable_options[button_label]" value="<?php echo $options['button_label']; ?>" maxlength="20"/>
					</td>
				</tr>

				<!-- CSS Button Group -->
                <tr valign="top">
					<th scope="row"><?php _e('Include CSS?', 'submittable'); ?></th>
					<td>
						<label class="submittable-relative-css">
                        	<input name="submittable_options[include_css]" type="radio" value="yes" <?php checked('yes', $options['include_css']); ?> />
                            <?php _e('Include plugin CSS?', 'submittable'); ?>
                            <span style="margin-left: 10px"><?php _e('&nbsp;[Default styling]', 'submittable'); ?></span>
                        </label>

						<label class="submittable-relative-css">
                        	<input name="submittable_options[include_css]" type="radio" value="no" <?php checked('no', $options['include_css']); ?> />
                            <?php _e('Disable plugin CSS?', 'submittable'); ?>
                            <span style="margin-left: 10px"><?php _e('&nbsp;[Requires custom CSS]', 'submittable'); ?></span>
                        </label>
                        <span class="submittable-input-span"><?php _e('Select whether you\'d like the plugin to include CSS style rules.', 'submittable'); ?></span>
					</td>
				</tr>

                <!-- Show Main Description Option -->
                <tr valign="top">
					<th scope="row"><?php _e('Show General Guidelines?', 'submittable'); ?></th>
					<td>
                        <label>
                        	<input name="submittable_options[show_main_description]" type="checkbox" value="yes" <?php if (isset($options['show_main_description'])) { checked('yes', $options['show_main_description']); } ?> />
                            <?php _e('Yes', 'submittable'); ?>
                        </label>

						<span class="submittable-input-span"><?php _e('Show your organization\'s General Guidelines (under More > Profile in your Submittable&trade; account) above your categories list.', 'submittable'); ?></span>
                    </td>
                </tr>

                <!-- Show Fees Option -->
                <tr valign="top">
					<th scope="row"><?php _e('Show Fees?', 'submittable'); ?></th>
					<td>
                        <label>
                        	<input name="submittable_options[show_fees]" type="checkbox" value="yes" <?php if (isset($options['show_fees'])) { checked('yes', $options['show_fees']); } ?> />
                            <?php _e('Yes', 'submittable'); ?>
                        </label>

						<span class="submittable-input-span"><?php _e('Show any applicable fees for each category.', 'submittable'); ?></span>
                    </td>
                </tr>
                

				<tr><td colspan="2"><div class="submittable-spacer"></div></td></tr>

				<!-- Remove Branding -->
				<tr valign="top" >
					<th scope="row"><?php _e('Remove Branding?', 'submittable'); ?></th>
					<td>
						<label>
                        	<input name="submittable_options[remove_branding]" type="checkbox" value="yes" <?php if (isset($options['remove_branding'])) { checked("yes", $options['remove_branding']); } ?> />
                            <?php _e('Yes', 'submittable'); ?>
                            <span class="submittable-input-span"><?php _e('Remove any Submittable&trade; branding on your site.', 'submittable') ?></span>
                        </label>
					</td>
				</tr>

                <!-- Reset Data -->
				<tr valign="top" >
					<th scope="row"><?php _e('Reset Data?', 'submittable'); ?></th>
					<td>
						<label>
                        	<input name="submittable_options[chk_default_options_db]" type="checkbox" value="1" <?php if (isset($options['chk_default_options_db'])) { checked('1', $options['chk_default_options_db']); } ?> />
                            <?php _e('Restore default settings upon plugin deactivation/reactivation', 'submittable'); ?>
                        </label>
						<span class="submittable-input-span"><?php _e('Only check this if you want your saved settings to be your default settings if you deactivate and then reactivate the plugin.', 'submittable'); ?></span>
					</td>
				</tr>

			</table>


			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'submittable') ?>" />
			</p>
		</form>

		<div id="submittable_social">

			<a href="http://www.submittable.com" id="submittable_footer_logo" title="<?php _e('Powered By Submittable', 'submittable'); ?>" target="_blank"><img src="https://mnager.submittable.com/Public/Images/submittable-footer-logo.png" /></a>

		</div>

	</div>
	<?php
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function submittable_validate_options($input) {

	$input['subdomain'] = preg_replace( '/\s+/', '', $input['subdomain'] ); // strip whitespace from subdomain first
	$input['subdomain'] =  wp_filter_nohtml_kses($input['subdomain']); // Sanitize textbox input (strip html tags, and escape characters )
	$input['button_label'] =  wp_filter_nohtml_kses($input['button_label']); // Sanitize textbox input (strip html tags, and escape characters )
	return $input;

}

// Display a Settings link on the main Plugins page
function submittable_plugin_action_links( $links, $file ) {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$submittable_links = '<a href="'.get_admin_url().'options-general.php?page=submission-manager-by-submittable/submittable.php">'.__('Settings').'</a>';
		// make the 'Settings' link appear first
		array_unshift( $links, $submittable_links );
	}

	return $links;
}

// ------------------------------------------------------------------------------
// SHORTCODE:
// ------------------------------------------------------------------------------

function submittable_get_content($atts) {
     //reno attributes
	 $submittable_options = get_option('submittable_options');
	 $submittable_options['subdomain'] = preg_replace( '/\s+/', '', $submittable_options['subdomain'] );

	 //checking for blank/empty value
	 if ($submittable_options['subdomain'] == '') {

		 //force enqueue styles if there's an error
		wp_enqueue_style('submittable-default-css', plugin_dir_url(__FILE__).'css/submittable-default.css');
		$error_no_sub = '<div id="submittable_content"><div class="alert">';
		$error_no_sub .= __('Oops! Looks like there\'s some setup info needed with the Submittable Plugin.', 'submittable');
		if (current_user_can('manage_options')) {
			$error_no_sub .= '<br />'.__('Admin: The "subdomain" value is missing in the <a href="/wp-admin/options-general.php?page=submission-manager-by-submittable/submittable.php">Plugin Settings!', 'submittable');
		}
		$error_no_sub .= '</a></div></div>';
		 return $error_no_sub;
		 exit;

	 } else {

		 $submittable_content = ''; // reset variable

		// Get RSS Feed(s)
		include_once(ABSPATH . WPINC . '/feed.php');

		// Cache Filter Duration
		function return_30( $seconds )
			{
			  return 30;
			}

		// Get a SimplePie feed object from the specified feed source.

		add_filter( 'wp_feed_cache_transient_lifetime' , 'return_30' );
		
		$subdomain = $submittable_options['subdomain'];
		$numDots = substr_count( $submittable_options['subdomain'], '.');
		if( $numDots == 0 )
			$subdomain = $subdomain . '.submittable.com';
		
		$rss_url = 'http://'.$subdomain.'/rss/';
		
		$submittable_rss = fetch_feed($rss_url);
		
		remove_filter( 'wp_feed_cache_transient_lifetime' , 'return_30' );

        //$submittable_rss->set_cache_duration(60);
		if (is_wp_error( $submittable_rss ) ) { // If there's an error getting the RSS feed
			$error_string = $submittable_rss->get_error_message();
			//force enqueue styles if there's an error
			wp_enqueue_style('submittable-default-css', plugin_dir_url(__FILE__).'css/submittable-default.css');
			$error_no_rss = '<div id="submittable_content"><div class="alert"><p>';
			$error_no_rss .= __('Oops! There is an error getting the Information from Submittable&trade;.', 'submittable');
			if (current_user_can('manage_options')) {
				$error_no_rss .= '<br />'.__('Admin: Please double check that your entered "<a href="/wp-admin/options-general.php?page=submittable/submittable.php">subdomain</a>" is valid.', 'submittable');
				$error_no_rss .= '<br />'.sprintf(__('You entered: <i>%s</i> for the subdomain.', 'submittable'), $submittable_options['subdomain']);
			}
			$error_no_rss .= '</p><span>';
			$error_no_rss .= '</span></div></div>';
			return $error_no_rss;
			exit;

		} else { // no RSS Error, carrying on
			$submittable_rss->enable_order_by_date(false);

			if ($submittable_options['button_label'] != '') { // reset the button label, if overriding text has been added
				$button_label = $submittable_options['button_label'];
			} else { // Stock Button Text
				$button_label = __('Submit', 'submittable');

		}

			// Figure out how many total items there are.
			$maxitems = $submittable_rss->get_item_quantity(0); // setting get_item_quantity to "0" returns all items

			// Build an array of all the items, starting with element 0 (first element).
			//$rss_items = array_reverse($submittable_rss->get_items(0, $maxitems));
			$rss_items = $submittable_rss->get_items(0, $maxitems);

		} // end is_wp_error check

		$submittable_content .= '<div id="submittable_content">';

		if ($submittable_options['show_main_description'] === "yes") {

			$submittable_content .= '<div id="feed_description">';
			$submittable_content .= $submittable_rss->get_description();
			$submittable_content .= '</div>';

		}

		$submittable_content .= '';

		if ($maxitems == 0) {

			$submittable_content .= '<div class="category panel-btn relative"><div class="title-column">
                        <div class="header-3">'.__('There are currently no open categories.', 'submittable').'</div></div></div>';

	 	} else {

			// Loop through each feed item and display each item as a hyperlink.
    		foreach ( $rss_items as $item ) :
                $submit_link = esc_url( $item->get_permalink() );
                $cat_date = $item->get_date('j F Y | g:i a');
                $cat_title = $item->get_title();
				$get_guid = $item->get_item_tags('', 'guid');
				$cat_guid = $get_guid[0]['data'];
				$cat_description = $item->get_content();
                $get_fee = $item->get_item_tags('', 'fee');
				$fee_amount = $get_fee[0]['data'];
                $cat_fee = $fee_amount != "$0.00" && $submittable_options['show_fees'] == "yes" ? $fee_amount : '';
				$more_button = $cat_description ?
					'<div class="more-column collapsed" data-toggle="collapse" data-target="#description-'.$cat_guid.'">
						More <span class="caret"></span>
					</div>' :
					'<div class="more-column"></div>';

                $submittable_content .= '
                <div class="category panel-btn relative" data-go="'.$submit_link.'">
                    <div class="title-column">
						<div class="header-3">
							<a href="'.$submit_link.'" target="_blank" title="'.$cat_title.' Posted on: '.$cat_date.'">'.$cat_title.'</a>
						</div>
						<div class="fee-column">
							'.$cat_fee.'
						</div>
                    </div>
					'.$more_button.'
                    <div class="submit-column">
                        <a class="btn org-primary link-color-inverted" target="_blank" href="'.$submit_link.'"><span class="hidden-md-down">'.$button_label.'</span><span class="hidden-md-up submitticon-next"></span></a>
                    </div>
                </div>
				<div id="description-'.$cat_guid.'" class="collapse">
					<div class="category-description">
						'.$cat_description.'
					</div>
				</div>';
			endforeach;

		} // end loop through each feed item

		$submittable_content .= '</div>';

		if($submittable_options['remove_branding'] !== 'yes') {
			$submittable_content .= '<div id="submittable_branding" class="sbm-logo">';
			$submittable_content .= '<a href="http://www.submittable.com" target="_blank"><img width="185px" height="20px" src="'.plugin_dir_url(__FILE__).'images/sbm-powered-by.png" alt="" /></a>';
			$submittable_content .= '</div>';
		}

		$submittable_content .= '<!-- /submittable content -->';


	} // end sudomain check

	// Return the output to the shortcode
	return $submittable_content;

} // end submittable_get_content function

add_shortcode('submittable', 'submittable_get_content');


// CHECK TO SEE IF CSS SHOULD BE ENQUEUE'd or NOT

function submittable_filter_posts() {
	$options = get_option('submittable_options');
	if ($options['include_css'] == "yes") {
			return true;
		} else {
			return false;
		}
}

if (submittable_filter_posts()) {

	add_filter('the_posts', 'submittable_enqueue'); // the_posts gets triggered before wp_head

 }

/*
 * Find shortcode and enqueue stylesheet only on pages/posts where it is found
 */
function submittable_enqueue($posts){
	if (empty($posts)) return $posts;

	$shortcode_exists = false; // use this flag to see if styles and scripts need to be enqueued
	$css_files = array();
	foreach ($posts as $post) {

		// find shortcode
		if (preg_match("/\[submittable\]/", $post->post_content, $matches) > 0) {
			$shortcode_exists = true; // ah ha!
		}
	}

	if ($shortcode_exists) {
		wp_enqueue_style('submittable-default-css', plugin_dir_url(__FILE__).'css/submittable-default.css');
		wp_enqueue_script('submittable', plugin_dir_url(__FILE__) . 'js/submittable.js', array('jquery'));
	}

	return $posts;
}