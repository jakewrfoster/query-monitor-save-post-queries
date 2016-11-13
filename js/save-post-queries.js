'use strict';

jQuery( document ).ready( function( $ ) {
	$( '.all-save-post-queries-wrapper button' ).click( function() {
		$( this ).next( '.all-save-post-queries' ).slideToggle();
	});
} );
