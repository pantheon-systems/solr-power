<div id="solr_indexing" class="solrtab">
	<form method="post" action="options.php">
		<?php
		settings_fields( 'solr-power' );
		do_settings_sections( 'solr-power' );
		submit_button();
		?>
	</form>
</div>
