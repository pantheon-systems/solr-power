<div id="solr_indexing" class="solrtab">
	<form method="post" action="<?php echo esc_url( $action ); ?>#top#solr_indexing">
		<?php wp_nonce_field( 'solr_action', 'solr_update' ); ?>
		<h3><?php esc_html_e( 'Indexing Options', 'solr-for-wordpress-on-pantheon' ) ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"
				    style="width:200px;"><?php esc_html_e( 'Index Pages', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_index_pages]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_index_pages'], 1 ); ?> />
				</td>
				<th scope="row"
				    style="width:200px;float:left;margin-left:20px;"><?php esc_html_e( 'Index Posts', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_index_posts]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_index_posts'], 1 ); ?> />
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"
				    style="width:200px;"><?php esc_html_e( 'Remove Page on Delete', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_delete_page]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_delete_page'], 1 ); ?> />
				</td>
				<th scope="row"
				    style="width:200px;float:left;margin-left:20px;"><?php esc_html_e( 'Remove Post on Delete', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_delete_post]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_delete_post'], 1 ); ?> />
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"
				    style="width:200px;"><?php esc_html_e( 'Remove Page on Status Change', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_private_page]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_private_page'], 1 ); ?> />
				</td>
				<th scope="row"
				    style="width:200px;float:left;margin-left:20px;"><?php esc_html_e( 'Remove Post on Status Change', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_private_post]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_private_post'], 1 ); ?> />
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"
				    style="width:200px;"><?php esc_html_e( 'Index Comments', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_index_comments]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_index_comments'], 1 ); ?> />
				</td>
			</tr>

			<?php
			//is this a multisite installation
			if ( is_multisite() && is_main_site() ) {
				?>

				<tr valign="top">
					<th scope="row"
					    style="width:200px;"><?php esc_html_e( 'Index all Sites', 'solr-for-wordpress-on-pantheon' ) ?></th>
					<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_index_all_sites]"
					                                          value="1" <?php checked( $s4wp_settings['s4wp_index_all_sites'], 1 ); ?> />
					</td>
				</tr>
				<?php
			}
			?>
			<?php // @todo drop-down combo box off all custom fields ?>
			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Index custom fields (comma separated names list)', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td><input type="text" name="settings[s4wp_index_custom_fields]"
				           value="<?php echo esc_attr( SolrPower_Options::get_instance()->filter_list2str( $s4wp_settings['s4wp_index_custom_fields'] ) ); ?>"/>
				</td>
			</tr>
			<?php
			// @todo drop-down combo box off all pages & posts?>
			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Excludes Posts or Pages (comma separated ids list)', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td><input type="text" name="settings[s4wp_exclude_pages]"
				           value="<?php echo esc_attr( SolrPower_Options::get_instance()->filter_list2str( $s4wp_settings['s4wp_exclude_pages'] ) ); ?>"/>
				</td>
			</tr>
		</table>
		<h3><?php esc_html_e( 'Result Options', 'solr-for-wordpress-on-pantheon' ) ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"
				    style="width:200px;"><?php esc_html_e( 'Output Result Info', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_output_info]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_output_info'], 1 ); ?> />
				</td>
				<th scope="row"
				    style="width:200px;float:left;margin-left:20px;"><?php esc_html_e( 'Output Result Pager', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_output_pager]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_output_pager'], 1 ); ?> />
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"
				    style="width:200px;"><?php esc_html_e( 'Output Facets', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_output_facets]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_output_facets'], 1 ); ?> />
				</td>
				<th scope="row"
				    style="width:200px;float:left;margin-left:20px;"><?php esc_html_e( 'Category Facet as Taxonomy', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_cat_as_taxo]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_cat_as_taxo'], 1 ); ?> />
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"
				    style="width:200px;"><?php esc_html_e( 'Categories as Facet', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_categories]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_facet_on_categories'], 1 ); ?> />
				</td>
				<th scope="row"
				    style="width:200px;float:left;margin-left:20px;"><?php esc_html_e( 'Tags as Facet', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_tags]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_facet_on_tags'], 1 ); ?> />
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"
				    style="width:200px;"><?php esc_html_e( 'Author as Facet', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_author]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_facet_on_author'], 1 ); ?> />
				</td>
				<th scope="row"
				    style="width:200px;float:left;margin-left:20px;"><?php esc_html_e( 'Type as Facet', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_type]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_facet_on_type'], 1 ); ?> />
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"
				    style="width:200px;"><?php esc_html_e( 'Taxonomy as Facet', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td style="width:10px;float:left;"><input type="checkbox" name="settings[s4wp_facet_on_taxonomy]"
				                                          value="1" <?php checked( $s4wp_settings['s4wp_facet_on_taxonomy'], 1 ); ?> />
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Custom fields as Facet (comma separated ordered names list)', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td><input type="text" name="settings[s4wp_facet_on_custom_fields]"
				           value="<?php echo esc_attr( SolrPower_Options::get_instance()->filter_list2str( $s4wp_settings['s4wp_facet_on_custom_fields'] ) ); ?>"/>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Default Search Operator', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td>
					<?php
					$and = "";
					$or  = "";
					if ( ! isset( $s4wp_settings['s4wp_default_operator'] ) ) {
						$s4wp_settings['s4wp_default_operator'] = 'Or';
					}
					?>
					Or <input type="radio" name="settings[s4wp_default_operator]"
					          value="Or" <?php checked( $s4wp_settings['s4wp_default_operator'], 'Or' ); ?>> And
					<input type="radio" name="settings[s4wp_default_operator]"
					       value="And" <?php checked( $s4wp_settings['s4wp_default_operator'], 'And' ); ?>>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Number of Results Per Page', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td><input type="text" name="settings[s4wp_num_results]"
				           value="<?php echo absint( get_option( 'posts_per_page' ) ); ?>" readonly/><br>
					<small><?php esc_html_e( 'This is set in Settings->Reading.', 'solr-for-wordpress-on-pantheon' ); ?></small>
				</td>
			</tr>

			<tr valign="top" style="display: none; ">
				<th scope="row"><?php esc_html_e( 'Max Number of Tags to Display', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td><input type="text" name="settings[s4wp_max_display_tags]"
				           value="<?php esc_attr( $s4wp_settings['s4wp_max_display_tags'] ); ?>"/>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Default Sort', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td>
					<select name="settings[s4wp_default_sort]">
						<option value="score" <?php selected( 'score', $s4wp_settings['s4wp_default_sort'], true ); ?>>
							Score
						</option>
						<option
							value="displaydate" <?php selected( 'displaydate', $s4wp_settings['s4wp_default_sort'], true ); ?>>
							Date
						</option>
					</select>
				</td>
			</tr>
		</table>
		<hr/>

		<?php settings_fields( 's4w-options-group' ); ?>

		<p class="submit">
			<input type="hidden" name="action" value="update"/>
			<input id="settingsbutton" type="submit" class="button-primary"
			       value="<?php esc_html_e( 'Save Changes', 'solr-for-wordpress-on-pantheon' ) ?>"/>
		</p>

	</form>

</div>
