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
$s4wp_settings = solr_options();

#set defaults if not initialized
if ($s4wp_settings['s4wp_solr_initialized'] != 1) {

  $options = SolrPower_Options::get_instance()->initalize_options();
  $options['s4wp_index_all_sites'] = 0;

  //update existing settings from multiple option record to a single array
  //if old options exist, update to new system
  // Pretty sure we don't need any of this. Seems left over from an older version. - Cal
  $delete_option_function = 'delete_option';

  if (is_multisite()) {
    $indexall = get_site_option('s4wp_index_all_sites');
    $delete_option_function = 'delete_site_option';
  }

  //find each of the old options function
  //update our new array and delete the record.
  foreach($options as $key => $value ) {
    if( $existing = get_option($key)) {
      $options[$key] = $existing;
      $indexall = FALSE;
      //run the appropriate delete options function
      $delete_option_function($key);
    }
  }

  // WTH?
  $s4wp_settings = $options;
  //save our options array
 // s4wp_update_option($options);
}


wp_reset_vars(array('action'));

# save form settings if we get the update action
# we do saving here instead of using options.php because we need to use
# s4wp_update_option instead of update option.
# As it stands we have 27 options instead of making 27 insert calls (which is what update_options does)
# Lets create an array of all our options and save it once.
if (isset($_POST['action']) && $_POST['action'] == 'update') {
  //lets loop through our setting fields $_POST['settings']

  foreach ($s4wp_settings as $option => $old_value ) {
    if (!isset($_POST['settings'][$option])) {
		$s4wp_settings[$option] = 0;
      continue;
    }
		$value = $_POST[ 'settings' ][ $option ];
		switch ($option) {
      case 's4wp_solr_initialized':
        $value = trim($old_value);
        break;
    case 's4wp_server':
      //remove empty server entries
      $s_value = &$value['info'];

      foreach ($s_value as $key => $v) {
        //lets rename the array_keys
        if(!$v['host']) unset($s_value[$key]);
      }
      break;

    }
    if ( !is_array($value) ) $value = trim($value);
    $value = stripslashes_deep($value);
    $s4wp_settings[$option] = $value;
  }

  //lets save our options array
  SolrPower_Options::get_instance()->update_option($s4wp_settings);

  //we need to make call for the options again
  //as we need them to come out in an a sanitised format
  //otherwise fields that need to run s4wp_filter_list2str will come up with nothin
  $s4wp_settings = solr_options();

  ?>
  <div id="message" class="updated fade"><p><strong><?php _e('Success!', 'solr4wp') ?></strong></p></div>
  <?php
}

# checks if we need to check the checkbox
function s4wp_checkCheckbox( $fieldValue ) {
  if( $fieldValue == '1'){
    echo 'checked="checked"';
  }
}

function s4wp_checkConnectOption($optionType, $connectType) {
    if ( $optionType === $connectType ) {
        echo 'checked="checked"';
    }
}


# check for any POST settings
/*
 * @todo Fix this. If Statement sucks. -- Cal
 *
 */
if (isset($_POST['s4wp_ping']) and $_POST['s4wp_ping']) {
    if (  SolrPower_Api::get_instance()->ping_server()) {
?>
<div id="message" class="updated fade"><p><strong><?php _e('Ping Success!', 'solr4wp') ?></strong></p></div>
<?php
    } else {
?>
    <div id="message" class="updated fade"><p><strong><?php _e('Ping Failed!', 'solr4wp') ?></strong></p></div>
<?php
    }
} else if (isset($_POST['s4wp_deleteall']) and $_POST['s4wp_deleteall']) {
    SolrPower_Sync::get_instance()->delete_all();
?>
    <div id="message" class="updated fade"><p><strong><?php _e('All Indexed Pages Deleted!', 'solr4wp') ?></strong></p></div>
<?php
}  else if (isset($_POST['s4wp_repost_schema']) and $_POST['s4wp_repost_schema']) {
    SolrPower_Sync::get_instance()->delete_all();
    $output = SolrPower_Api::get_instance()->submit_schema();
?>
    <div id="message" class="updated fade"><p><strong><?php _e('All Indexed Pages Deleted!<br />'.esc_html($output), 'solr4wp') ?></strong></p></div>
<?php
}  else if (isset($_POST['s4wp_optimize']) and $_POST['s4wp_optimize']) {
    SolrPower_Api::get_instance()->optimize();
?>
    <div id="message" class="updated fade"><p><strong><?php _e('Index Optimized!', 'solr4wp') ?></strong></p></div>
<?php
} else if (isset($_POST['s4wp_init_blogs']) and $_POST['s4wp_init_blogs']) {
    SolrPower_Sync::get_instance()->copy_config_to_all_blogs();
} else if(isset($_POST['s4wp_query']) && $_POST['solrQuery']) {

  $qry      = filter_input(INPUT_POST,'solrQuery',FILTER_SANITIZE_STRING);
  $offset   = null;
  $count    = null;
  $fq       = null;
  $sortby   = null;
  $order    = null;
  $results  = SolrPower_Api::get_instance()->query($qry, $offset, $count, $fq, $sortby, $order);

  if(isset($results)) {
    $plugin_s4wp_settings = solr_options();
    $output_info  = $plugin_s4wp_settings['s4wp_output_info'];
    $data         = $results->getData();
    $response     = $data['response'];
    $header       = $data['responseHeader'];
    if ($output_info) {
        $out['hits'] = $response['numFound'];
        $out['qtime'] = sprintf(__("%.3f"), $header['QTime'] / 1000);
    } else {
        $out['hits'] = 0;
        $out['qtime'] = 0;
    }
  } else {
    $data = $results;
  }
  ?>
  <div id="message" class="updated fade"><p><strong>Solr Results for string "<?php echo esc_html($qry); ?>":</strong>
    <br />Hits: <?php echo esc_html($out['hits']); ?>
    <br />Query Time: <?php echo esc_html($out['qtime']); ?>
    </p></div>
<?php
}
$s4wp_settings = solr_options();

?>


<div class="wrap">
<h2><?php _e('Solr Power', 'solr4wp') ?></h2>


<h2 class="nav-tab-wrapper" id="solr-tabs">
	<a class="nav-tab nav-tab-active" id="solr_info-tab"
		   href="#top#solr_info">Info</a>
		<a class="nav-tab" id="solr_indexing-tab"
		   href="#top#solr_indexing">Indexing</a>
		<a class="nav-tab" id="solr_action-tab"
		   href="#top#solr_action">Actions</a>
		<a class="nav-tab" id="solr_query-tab" href="#top#solr_query">Query</a>
	</h2>

<div id="solr_info" class="solrtab active">
<?php
$server_ping=SolrPower_Api::get_instance()->ping_server();
?>
<div style="width:50%;float:left;">
<table class="widefat">
	<thead>
	<tr>
		<th colspan="2"><strong>Solr Configuration</strong></th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td>Ping Status:</td>
		<td><?php echo ($server_ping)? '<span style="color:green">Successful</span>' : '<span style="color:red">Failed</span>';?></td>
	</tr>
	<tr>
		<td>Solr Server IP address:</td>
		<td><?php echo esc_html( getenv( 'PANTHEON_INDEX_HOST' ) ); ?></td>
	</tr>
	<tr>
		<td>Solr Server Port:</td>
		<td><?php echo esc_html( getenv( 'PANTHEON_INDEX_PORT' ) ); ?></td>
	</tr>
	<tr>
		<td>Solr Server Path:</td>
		<td><?php echo esc_html( SolrPower_Api::get_instance()->compute_path() ); ?></td>
	</tr>
	</tbody>

</table>
	</div>
	<?php if ($server_ping){ ?>
	<div style="width:50%;float:left;">
		<table class="widefat">
			<thead>
			<tr>
				<th colspan="2"><strong>Indexing Stats by Post Type</strong></th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach (SolrPower_Api::get_instance()->index_stats() as $type=>$stat){
			?>
			<tr>
				<td><?php echo esc_html($type); ?>:</td>
				<td><?php echo absint($stat); ?></td>
			</tr>
			<?php } ?>
			</tbody>

		</table>
	</div>
	<?php } ?>
<br style="clear:both;">
</div>
<?php
if ( is_multisite() ) {
  $action='settings.php?page=solr-power';
} else {
  $action='options-general.php?page=solr-power';
}
include 'views/options/indexing.php';
include 'views/options/action.php';
include 'views/options/query.php';
?>



</div>

	<script>
		jQuery(document).ready(function(){
			jQuery( '#solr-tabs' ).find( 'a' ).click( function() {
				jQuery( '#solr-tabs' ).find( 'a' ).removeClass( 'nav-tab-active' );
				jQuery( '.solrtab' ).removeClass( 'active' );

				var id = jQuery( this ).attr( 'id' ).replace( '-tab', '' );
				jQuery( '#' + id ).addClass( 'active' );
				jQuery( this ).addClass( 'nav-tab-active' );
			}
		);
	
	// init
		var solrActiveTab = window.location.hash.replace( '#top#', '' );

		// default to first tab
		if ( solrActiveTab === '' || solrActiveTab === '#_=_' ) {
			solrActiveTab = jQuery( '.solrtab' ).attr( 'id' );
		}

		jQuery( '#' + solrActiveTab ).addClass( 'active' );
		jQuery( '#' + solrActiveTab + '-tab' ).addClass( 'nav-tab-active' );

		jQuery( '.nav-tab-active' ).click();
		});
		
		</script>
		<style>
			.solrtab{
				display:none;
			}
			.solrtab.active{
				display:block;
			}
			</style>