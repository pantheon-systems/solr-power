<div id="solr_query" class="solrtab">
	<form method="post" action="<?php echo $action; ?>#top#solr_query">
<h3><?php _e('Solr Query', 'solr4wp') ?></h3>
<table class="form-table">
  <tr valign="top">
      <th scope="row"><?php _e('Words or phrases to search for.', 'solr4wp') ?></th>
      <td><textarea rows="3" cols="50" name="solrQuery"></textarea>
      </td>
  </tr>
  <tr valign="top">
      <th scope="row"><?php _e('Submit Query', 'solr4wp') ?></th>
      <td><input type="submit" class="button-primary" name="s4wp_query" value="<?php _e('Execute', 'solr4wp') ?>" />
      </td>
  </tr>
</table>
	</form>
</div>