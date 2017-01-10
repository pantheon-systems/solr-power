<div id="solr_indexing" class="solrtab">
	<div class="solr-power-subpage">
		<form method="post" action="options.php">
			<?php
			echo '<form method="post" action="options.php">';
			settings_fields( 'solr-power-index' );
			do_settings_sections( 'solr-power-index' );
			echo '<div style="display:none !important;">';
			do_settings_sections( 'solr-power-facet' );
			echo '</div>';
			submit_button();
			?>
		</form>
	</div>
</div>