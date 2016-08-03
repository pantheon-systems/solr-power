<div id="solr_indexing"  class="solrtab">
	<form method="post" action="<?php echo $action; ?>#top#solr_indexing">
<h3><?php _e('Indexing Options', 'solr4wp') ?></h3>
<table class="form-table">
    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Index Pages', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_index_pages]" value="1" <?php checked($s4wp_settings['s4wp_index_pages'],1); ?> /></td>
        <th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Index Posts', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_index_posts]" value="1" <?php checked($s4wp_settings['s4wp_index_posts'],1); ?> /></td>
    </tr>

    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Remove Page on Delete', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_delete_page]" value="1" <?php checked($s4wp_settings['s4wp_delete_page'],1); ?> /></td>
        <th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Remove Post on Delete', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_delete_post]" value="1" <?php checked($s4wp_settings['s4wp_delete_post'],1); ?> /></td>
    </tr>

    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Remove Page on Status Change', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_private_page]" value="1" <?php checked($s4wp_settings['s4wp_private_page'],1); ?> /></td>
        <th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Remove Post on Status Change', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_private_post]" value="1" <?php checked($s4wp_settings['s4wp_private_post'],1); ?> /></td>
    </tr>

    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Index Comments', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_index_comments]" value="1" <?php checked($s4wp_settings['s4wp_index_comments'],1); ?> /></td>
    </tr>

    <?php
    //is this a multisite installation
    if (is_multisite() && is_main_site()) {
    ?>

    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Index all Sites', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_index_all_sites]" value="1" <?php checked($s4wp_settings['s4wp_index_all_sites'],1); ?> /></td>
    </tr>
    <?php
    }
    ?>
    <?php // @todo drop-down combo box off all custom fields ?>
    <tr valign="top">
        <th scope="row"><?php _e('Index custom fields (comma separated names list)') ?></th>
        <td><input type="text" name="settings[s4wp_index_custom_fields]" value="<?php print(implode(',',$s4wp_settings['s4wp_index_custom_fields'])); ?>" /></td>
    </tr>
    <?php
    // @todo drop-down combo box off all pages & posts?>
    <tr valign="top">
        <th scope="row"><?php _e('Excludes Posts or Pages (comma separated ids list)') ?></th>
        <td><input type="text" name="settings[s4wp_exclude_pages]" value="<?php print(implode(',',$s4wp_settings['s4wp_exclude_pages'])); ?>" /></td>
    </tr>
</table>
<h3><?php _e('Result Options', 'solr4wp') ?></h3>
<table class="form-table">
    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Output Result Info', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_output_info]" value="1" <?php checked($s4wp_settings['s4wp_output_info'],1); ?> /></td>
        <th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Output Result Pager', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_output_pager]" value="1" <?php checked($s4wp_settings['s4wp_output_pager'],1); ?> /></td>
    </tr>

    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Output Facets', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_output_facets]" value="1" <?php checked($s4wp_settings['s4wp_output_facets'],1); ?> /></td>
        <th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Category Facet as Taxonomy', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_cat_as_taxo]" value="1" <?php checked($s4wp_settings['s4wp_cat_as_taxo'],1); ?> /></td>
    </tr>

    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Categories as Facet', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_categories]" value="1" <?php checked($s4wp_settings['s4wp_facet_on_categories'],1); ?> /></td>
        <th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Tags as Facet', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_tags]" value="1" <?php checked($s4wp_settings['s4wp_facet_on_tags'],1); ?> /></td>
    </tr>

    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Author as Facet', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_author]" value="1" <?php checked($s4wp_settings['s4wp_facet_on_author'],1); ?> /></td>
        <th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Type as Facet', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_type]" value="1" <?php checked($s4wp_settings['s4wp_facet_on_type'],1); ?> /></td>
    </tr>

     <tr valign="top">
         <th scope="row" style="width:200px;"><?php _e('Taxonomy as Facet', 'solr4wp') ?></th>
         <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_taxonomy]" value="1" <?php checked($s4wp_settings['s4wp_facet_on_taxonomy'],1); ?> /></td>
      </tr>

    <tr valign="top">
        <th scope="row"><?php _e('Custom fields as Facet (comma separated ordered names list)') ?></th>
        <td><input type="text" name="settings[s4wp_facet_on_custom_fields]" value="<?php print(esc_attr(implode(',',$s4wp_settings['s4wp_facet_on_custom_fields']))); ?>" /></td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php _e('Default Search Operator', 'solr4wp') ?></th>
        <td>
          <?php
		  $and="";
		  $or="";
          if(isset($s4wp_settings['s4wp_default_operator']) && $s4wp_settings['s4wp_default_operator'] == "And" ) {
            $and = 'checked';
          } else {
            $or = 'checked';
          }
          ?>
          Or <input type="radio" name="settings[s4wp_default_operator]" value="OR" <?php echo $or; ?>> And <input type="radio" name="settings[s4wp_default_operator]" value="AND" <?php echo $and; ?>>
          </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php _e('Number of Results Per Page', 'solr4wp') ?></th>
        <td><input type="text" name="settings[s4wp_num_results]" value="<?php /*_e($s4wp_settings['s4wp_num_results'], 'solr4wp');*/ ?>10" readonly/></td>
    </tr>

    <tr valign="top" style="display: none; ">
        <th scope="row"><?php _e('Max Number of Tags to Display', 'solr4wp') ?></th>
        <td><input type="text" name="settings[s4wp_max_display_tags]" value="<?php _e(esc_attr($s4wp_settings['s4wp_max_display_tags']), 'solr4wp'); ?>" /></td>
    </tr>
	<tr valign="top" >
        <th scope="row"><?php esc_html_e( 'Default Sort', 'solr4wp' ) ?></th>
        <td>
			<select name="settings[s4wp_default_sort]">
				<option value="score" <?php selected( 'score', $s4wp_settings[ 's4wp_default_sort' ], true ); ?>>Score</option>
				<option value="displaydate" <?php selected( 'displaydate', $s4wp_settings[ 's4wp_default_sort' ], true ); ?>>Date</option>
			</select>
		</td>
    </tr>
</table>
<hr />

<?php settings_fields('s4w-options-group'); ?>

<p class="submit">
<input type="hidden" name="action" value="update" />
<input id="settingsbutton" type="submit" class="button-primary" value="<?php _e('Save Changes', 'solr4wp') ?>" />
</p>

</form>

</div>
