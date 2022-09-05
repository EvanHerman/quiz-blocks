( function ( $ ) {
	'use strict';

	function tabs() {
		const $container = $( '.nav-tab-wrapper' );
		const $tabs = $container.find( '.nav-tab' );
		const $panes = $( '.gt-tab-pane' );

		$container.on(
			'click',
			'.nav-tab',
			function ( e ) {
				e.preventDefault();

				$tabs.removeClass( 'nav-tab-active' );
				$( this ).addClass( 'nav-tab-active' );

				$panes.removeClass( 'gt-is-active' );
				$panes.filter( $( this ).attr( 'href' ) ).addClass( 'gt-is-active' );
			}
		);
	}

	// Auto activate tabs when DOM ready.
	$( tabs );
} ( jQuery ) );
