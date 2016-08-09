jQuery( document ).ready( function () {
	jQuery( '#solr-tabs' ).find( 'a' ).click( function () {
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
} );