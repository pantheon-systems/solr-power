<?php
/*	
		Copyright (c) 2009 Matt Weber

		Permission is hereby granted, free of charge, to any person obtaining a copy
		of this software and associated documentation files (the "Software"), to deal
		in the Software without restriction, including without limitation the rights
		to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
		copies of the Software, and to permit persons to whom the Software is
		furnished to do so, subject to the following conditions:

		The above copyright notice and this permission notice shall be included in
		all copies or substantial portions of the Software.

		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
		IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
		FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
		AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
		LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
		OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
		THE SOFTWARE.
*/
/*
 * @todo Filter input, escape output, stop using $_POST directly
 *
 * @todo add button to submit schema
 *
 * Do we need to custom build a schema based on custom post types, etc?
 * 
 */
//get the plugin settings
$s4wp_settings = s4wp_get_option('plugin_s4wp_settings');

#set defaults if not initialized
if ( $s4wp_settings['s4wp_solr_initialized'] != 1 ) {
	
	$options = s4wp_initalize_options();
	$options['s4wp_index_all_sites'] = 0;
	//$options['s4wp_server']['info']['single'] = array('host'=>'localhost','port'=>8983, 'path'=>'/solr');
	//$options['s4wp_server']['info']['master'] = array('host'=>'localhost','port'=>8983, 'path'=>'/solr');
	//$options['s4wp_server']['type']['search'] = 'master';
	//$options['s4wp_server']['type']['update'] = 'master';
	
		 
	//update existing settings from multiple option record to a single array
	//if old options exist, update to new system
	// Pretty sure we don't need any of this. Seems left over from an older version. - Cal
	$delete_option_function = 'delete_option';

	if ( is_multisite() ) {
		$indexall = get_site_option('s4wp_index_all_sites');
		$delete_option_function = 'delete_site_option';
	}

	//find each of the old options function
	//update our new array and delete the record.
	foreach( $options as $key => $value ) {

		if( $existing = get_option($key) ) {
			$options[$key] = $existing;
			$indexall      = FALSE;
			//run the appropriate delete options function
			$delete_option_function($key);
		}
	}
	
	// WTH?
	$s4wp_settings = $options;
	//save our options array
	s4wp_update_option($options);
}


wp_reset_vars(array('action'));

# save form settings if we get the update action
# we do saving here instead of using options.php because we need to use
# s4wp_update_option instead of update option.
# As it stands we have 27 options instead of making 27 insert calls (which is what update_options does)
# Lets create an array of all our options and save it once.
if ( isset($_POST['action']) and $_POST['action'] == 'update' ) {	 
	//lets loop through our setting fields $_POST['settings']
	
	foreach ( $s4wp_settings as $option => $old_value ) {

		if ( !isset($_POST['settings'][$option]) ) {
			continue;
		}
		$value = $_POST['settings'][ $option ];

		switch ( $option ) {
			case 's4wp_solr_initialized':
				$value = trim( $old_value );
				break;

		case 's4wp_server':
			//remove empty server entries
			$s_value = &$value['info'];
			
			foreach ( $s_value as $key => $v ) {
				//lets rename the array_keys
				if(!$v['host']) unset( $s_value[ $key ] );
			}

			break;

		}

		if ( !is_array($value) ) {
			$value = trim($value); 
		}

		$value = stripslashes_deep( $value );
		$s4wp_settings[ $option ] = $value;
	}
	// if we are in single server mode set the server types to master
	// and configure the master server to the values of the single server
	//if ($s4wp_settings['s4wp_connect_type'] =='solr_single'){
	//	$s4wp_settings['s4wp_server']['info']['master']= $s4wp_settings['s4wp_server']['info']['single'];
	//	$s4wp_settings['s4wp_server']['type']['search'] = 'master';
	//	$s4wp_settings['s4wp_server']['type']['update'] = 'master';
	//}
	// if this is a multi server setup we steal the master settings
	// and stuff them into the single server settings in case the user
	// decides to change it later 
	//else {
	//	$s4wp_settings['s4wp_server']['info']['single']= $s4wp_settings['s4wp_server']['info']['master'];
	//}
	//lets save our options array
	s4wp_update_option( $s4wp_settings );

	//we need to make call for the options again 
	//as we need them to come out in an a sanitised format
	//otherwise fields that need to run s4wp_filter_list2str will come up with nothin
	$s4wp_settings = s4wp_get_option( 'plugin_s4wp_settings' );

	?>
	<div id="message" class="updated fade"><p><strong><?php _e('Success!', 'solr4wp') ?></strong></p></div>
	<?php
}

# checks if we need to check the checkbox
function s4wp_checkCheckbox( $fieldValue ) {

	if( $fieldValue == '1' ){
		echo 'checked="checked"';
	}

	return;
}

function s4wp_checkConnectOption( $optionType, $connectType ) {

	if ( $optionType === $connectType ) {
			echo 'checked="checked"';
	}

	return;
}



# check for any POST settings
/*
 * @todo Fix this. If Statement sucks. -- Cal
 */
if ( isset( $_POST['s4wp_ping'] ) and $_POST['s4wp_ping'] ) {

	if (s4wp_ping_server()) {
?>
<div id="message" class="updated fade"><p><strong><?php _e('Ping Success!', 'solr4wp') ?></strong></p></div>
<?php
	} else {
?>
		<div id="message" class="updated fade"><p><strong><?php _e('Ping Failed!', 'solr4wp') ?></strong></p></div>
<?php
	}

} elseif ( isset($_POST['s4wp_deleteall']) and $_POST['s4wp_deleteall'] ) {
		s4wp_delete_all();
?>
		<div id="message" class="updated fade"><p><strong><?php _e('All Indexed Pages Deleted!', 'solr4wp') ?></strong></p></div>
<?php
} elseif ( isset($_POST['s4wp_optimize']) and $_POST['s4wp_optimize'] ) {
		s4wp_optimize();
?>
		<div id="message" class="updated fade"><p><strong><?php _e('Index Optimized!', 'solr4wp') ?></strong></p></div>
<?php
} elseif ( isset($_POST['s4wp_init_blogs']) and $_POST['s4wp_init_blogs'] ) {
		s4wp_copy_config_to_all_blogs();
}	
?>
		<div id="message" class="updated fade"><p><strong><?php _e('Solr for Wordpress Configured for All Blogs!', 'solr4wp') ?></strong></p></div>


<div class="wrap">
<h2><?php _e('Solr For WordPress', 'solr4wp') ?></h2>

<form method="post" action="options-general.php?page=<?php echo plugin_dir_path( __FILE__ );?>solr-for-wordpress-on-pantheon.php">
<h3><?php _e('Configure Solr', 'solr4wp') ?></h3>
<?php // @todo add the rest of the discovered info here. ?>
<pre>
Solr Server IP address : <?php echo getenv( 'PANTHEON_INDEX_HOST' ); ?><br />
Solr Server Port       : <?php echo getenv( 'PANTHEON_INDEX_PORT' ); ?><br />
Solr Server Path       : <?php echo s4wp_compute_path(); ?><br />
</pre>
<hr />
<h3><?php _e( 'Indexing Options', 'solr4wp' ) ?></h3>
<table class="form-table">
	<tr valign="top">
		<th scope="row" style="width:200px;"><?php _e('Index Pages', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_index_pages]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_index_pages']); ?> /></td>
		<th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Index Posts', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_index_posts]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_index_posts']); ?> /></td>
	</tr>

	<tr valign="top">
		<th scope="row" style="width:200px;"><?php _e('Remove Page on Delete', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_delete_page]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_delete_page']); ?> /></td>
		<th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Remove Post on Delete', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_delete_post]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_delete_post']); ?> /></td>
	</tr>

	<tr valign="top">
		<th scope="row" style="width:200px;"><?php _e('Remove Page on Status Change', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_private_page]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_private_page']); ?> /></td>
		<th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Remove Post on Status Change', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_private_post]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_private_post']); ?> /></td>
	</tr>

	<tr valign="top">
		<th scope="row" style="width:200px;"><?php _e('Index Comments', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_index_comments]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_index_comments']); ?> /></td>
	</tr>
			
<?php
//is this a multisite installation
if ( is_multisite() && is_main_site() ) {
?>

	<tr valign="top">
		<th scope="row" style="width:200px;"><?php _e('Index all Sites', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_index_all_sites]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_index_all_sites']); ?> /></td>
	</tr>
<?php } ?>
<?php // @todo drop-down combo box off all custom fields ?>
	<tr valign="top">
		<th scope="row"><?php _e('Index custom fields (comma separated names list)') ?></th>
		<td><input type="text" name="settings[s4wp_index_custom_fields]" value="<?php print( s4wp_filter_list2str($s4wp_settings['s4wp_index_custom_fields'], 'solr4wp')); ?>" /></td>
	</tr>
<?php // @todo drop-down combo box off all pages & posts?>
	<tr valign="top">
		<th scope="row"><?php _e('Excludes Posts or Pages (comma separated ids list)') ?></th>
		<td><input type="text" name="settings[s4wp_exclude_pages]" value="<?php print(s4wp_filter_list2str($s4wp_settings['s4wp_exclude_pages'], 'solr4wp')); ?>" /></td>
	</tr>
</table>
<hr />
<h3><?php _e('Result Options', 'solr4wp') ?></h3>
<table class="form-table">
	<tr valign="top">
		<th scope="row" style="width:200px;"><?php _e('Output Result Info', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_output_info]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_output_info']); ?> /></td>
		<th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Output Result Pager', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_output_pager]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_output_pager']); ?> /></td>
	</tr>

	<tr valign="top">
		<th scope="row" style="width:200px;"><?php _e('Output Facets', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_output_facets]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_output_facets']); ?> /></td>
		<th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Category Facet as Taxonomy', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_cat_as_taxo]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_cat_as_taxo']); ?> /></td>
	</tr>

	<tr valign="top">
		<th scope="row" style="width:200px;"><?php _e('Categories as Facet', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_categories]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_facet_on_categories']); ?> /></td>
		<th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Tags as Facet', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_tags]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_facet_on_tags']); ?> /></td>
	</tr>
	
	<tr valign="top">
		<th scope="row" style="width:200px;"><?php _e('Author as Facet', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_author]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_facet_on_author']); ?> /></td>
		<th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Type as Facet', 'solr4wp') ?></th>
		<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_type]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_facet_on_type']); ?> /></td>
	</tr>

	 <tr valign="top">
		 <th scope="row" style="width:200px;"><?php _e('Taxonomy as Facet', 'solr4wp') ?></th>
		 <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_taxonomy]" value="1" <?php echo s4wp_checkCheckbox($s4wp_settings['s4wp_facet_on_taxonomy']); ?> /></td>
	</tr>
		
	<tr valign="top">
		<th scope="row"><?php _e('Custom fields as Facet (comma separated ordered names list)') ?></th>
		<td><input type="text" name="settings[s4wp_facet_on_custom_fields]" value="<?php print( s4wp_filter_list2str($s4wp_settings['s4wp_facet_on_custom_fields'], 'solr4wp')); ?>" /></td>
	</tr>
								 
	<tr valign="top">
		<th scope="row"><?php _e('Number of Results Per Page', 'solr4wp') ?></th>
		<td><input type="text" name="settings[s4wp_num_results]" value="<?php _e($s4wp_settings['s4wp_num_results'], 'solr4wp'); ?>" /></td>
	</tr>	 
	
	<tr valign="top">
		<th scope="row"><?php _e('Max Number of Tags to Display', 'solr4wp') ?></th>
		<td><input type="text" name="settings[s4wp_max_display_tags]" value="<?php _e($s4wp_settings['s4wp_max_display_tags'], 'solr4wp'); ?>" /></td>
	</tr>
</table>
<hr />

<?php settings_fields('s4w-options-group'); ?>

<p class="submit">
<input type="hidden" name="action" value="update" />
<input id="settingsbutton" type="submit" class="button-primary" value="<?php _e('Save Changes', 'solr4wp') ?>" />
</p>

</form>
<hr />
<form method="post" action="options-general.php?page=<?php echo plugin_dir_path( __FILE__ );?>solr-for-wordpress-on-pantheon.php">
<h3><?php _e('Actions', 'solr4wp') ?></h3>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e('Check Server Settings', 'solr4wp') ?></th>
		<td><input type="submit" class="button-primary" name="s4wp_ping" value="<?php _e('Execute', 'solr4wp') ?>" /></td>
	</tr>

<?php if(is_multisite()) { ?>
	<tr valign="top">
		<th scope="row"><?php _e('Push Solr Configuration to All Blogs', 'solr4wp') ?></th>
		<td><input type="submit" class="button-primary" name="s4wp_init_blogs" value="<?php _e('Execute', 'solr4wp') ?>" /></td>
	</tr>
<?php } ?>
	

	<tr valign="top">
		<th scope="row"><?php _e('Load All Pages', 'solr4wp') ?></th>
		<td><input type="submit" class="button-primary" name="s4wp_pageload" value="<?php _e('Execute', 'solr4wp') ?>" /></td>
	</tr>

	<tr valign="top">
		<th scope="row"><?php _e('Load All Posts', 'solr4wp') ?></th>
		<td><input type="submit" class="button-primary" name="s4wp_postload" value="<?php _e('Execute', 'solr4wp') ?>" /></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><?php _e('Optimize Index', 'solr4wp') ?></th>
		<td><input type="submit" class="button-primary" name="s4wp_optimize" value="<?php _e('Execute', 'solr4wp') ?>" /></td>
	</tr>
			
	<tr valign="top">
		<th scope="row"><?php _e('Delete All', 'solr4wp') ?></th>
		<td><input type="submit" class="button-primary" name="s4wp_deleteall" value="<?php _e('Execute', 'solr4wp') ?>" /></td>
	</tr>

</table>
</form>

</div>
