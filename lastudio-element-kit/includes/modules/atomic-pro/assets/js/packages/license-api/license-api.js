/**
 * LA-Studio Kit
 *
 * Exposes the same API as the Pro package so the ported editor packages work, and reports the
 * features provided by LA-Studio Kit as available.
 */
this.elementorV2 = this.elementorV2 || {};
(function( exports ) {
	'use strict';

	var FEATURES = [ 'atomic-custom-css', 'atomic-custom-attributes', 'display-conditions', 'size-variable', 'pro-interactions', 'atomic-loop' ];

	function queryResult( data ) {
		return { data: data, isLoading: false, isFetching: false, isFetched: true, isPending: false, isSuccess: true, isError: false, error: null, status: 'success' };
	}

	exports.fetchTierFeatures = function() {
		return Promise.resolve( FEATURES.slice() );
	};
	exports.fetchLicenseStatus = function() {
		// `true` means "expired" in the Pro package.
		return Promise.resolve( false );
	};
	exports.useTierFeatures = function() {
		return queryResult( FEATURES.slice() );
	};
	exports.useHasFeature = function( featureName ) {
		return queryResult( FEATURES.indexOf( featureName ) !== -1 );
	};
	exports.useLicenseStatus = function() {
		return queryResult( false );
	};
	exports.useIsLicenseExpired = function() {
		return queryResult( false );
	};
})( this.elementorV2.licenseApi = this.elementorV2.licenseApi || {} );
