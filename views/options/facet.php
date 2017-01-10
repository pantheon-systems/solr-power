<div id="solr_facet" class="solrtab">
	<div class="solr-power-subpage">
		<form method="post" action="options.php">
			<?php
			settings_fields( 'solr-power-facet' );
			echo '<div style="display:none !important;">';
			do_settings_sections( 'solr-power-index' );
			echo '</div>';
			do_settings_sections( 'solr-power-facet' );
			submit_button();
			?>
		</form>
	</div>
</div>