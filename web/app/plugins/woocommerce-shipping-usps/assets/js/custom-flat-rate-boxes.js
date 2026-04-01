/* global usps_custom_flat_rate_boxes_settings */
( function () {
	'use strict';

	var toggle = document.getElementById( 'woocommerce_usps_enable_custom_flat_rate_boxes' );
	var wrapper = document.getElementById( 'custom_flat_rate_box_options' );

	if ( toggle && wrapper ) {
		var toggleCustomFlatRateBoxes = function () {
			wrapper.style.display = toggle.checked ? '' : 'none';
		};
		toggleCustomFlatRateBoxes();
		toggle.addEventListener( 'change', toggleCustomFlatRateBoxes );
	}

	var table = document.querySelector( '.usps_custom_flat_rate_boxes' );
	if ( ! table ) {
		return;
	}

	var flatRateBoxes = usps_custom_flat_rate_boxes_settings.flat_rate_boxes || {};

	/**
	 * Build grouped <option> elements for the flat rate type select.
	 */
	function buildTypeOptionsHTML() {
		var groups = {};

		Object.keys( flatRateBoxes ).forEach( function ( code ) {
			var box = flatRateBoxes[ code ];
			var isDomestic = code.charAt( 0 ) === 'd';
			var group;

			if ( isDomestic && box.service === 'priority' ) {
				group = 'Domestic Priority Mail';
			} else if ( isDomestic && box.service === 'express' ) {
				group = 'Domestic Priority Mail Express';
			} else if ( ! isDomestic && box.service === 'priority' ) {
				group = 'International Priority Mail';
			} else {
				group = 'International Priority Mail Express';
			}

			if ( ! groups[ group ] ) {
				groups[ group ] = [];
			}
			groups[ group ].push( { code: code, name: box.name + ( isDomestic ? '' : ' (intl.)' ) } );
		} );

		var html = '';
		Object.keys( groups ).forEach( function ( group ) {
			html += '<optgroup label="' + group + '">';
			groups[ group ].forEach( function ( opt ) {
				html += '<option value="' + opt.code + '">' + opt.name + '</option>';
			} );
			html += '</optgroup>';
		} );

		return html;
	}

	var typeOptionsHTML = buildTypeOptionsHTML();
	var nextRowIndex = table.querySelector( 'tbody' ).querySelectorAll( 'tr' ).length;

	/**
	 * Handle "Add Custom Flat Rate Box" button.
	 */
	table.querySelector( '.insert' ).addEventListener( 'click', function ( e ) {
		e.preventDefault();

		var tbody = table.querySelector( 'tbody' );
		var size = nextRowIndex++;

		var tr = document.createElement( 'tr' );
		tr.className = 'new';
		tr.innerHTML =
			'<td class="check-column"><input type="checkbox" /></td>' +
			'<td><input type="text" size="10" maxlength="150" name="custom_flat_rate_boxes_name[' + size + ']" /></td>' +
			'<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="custom_flat_rate_boxes_length[' + size + ']" /></td>' +
			'<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="custom_flat_rate_boxes_width[' + size + ']" /></td>' +
			'<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="custom_flat_rate_boxes_height[' + size + ']" /></td>' +
			'<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="custom_flat_rate_boxes_box_weight[' + size + ']" /></td>' +
			'<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="custom_flat_rate_boxes_max_weight[' + size + ']" /></td>' +
			'<td><select name="custom_flat_rate_boxes_flat_rate_type[' + size + ']" class="custom-flat-rate-type-select">' + typeOptionsHTML + '</select></td>';

		tbody.appendChild( tr );
	} );

	/**
	 * Handle "Select all" checkbox in thead.
	 */
	var selectAll = table.querySelector( 'thead .check-column input[type="checkbox"]' );
	if ( selectAll ) {
		selectAll.addEventListener( 'change', function () {
			var checked = selectAll.checked;
			table.querySelectorAll( 'tbody .check-column input[type="checkbox"]' ).forEach( function ( cb ) {
				cb.checked = checked;
			} );
		} );
	}

	/**
	 * Handle "Remove selected box(es)" button.
	 */
	table.querySelector( '.remove' ).addEventListener( 'click', function ( e ) {
		e.preventDefault();

		var tbody = table.querySelector( 'tbody' );
		tbody.querySelectorAll( '.check-column input:checked' ).forEach( function ( checkbox ) {
			checkbox.closest( 'tr' ).remove();
		} );

		if ( selectAll ) {
			selectAll.checked = false;
		}
	} );

	/**
	 * Pre-fill dimensions when a flat rate type is selected (only if fields are empty).
	 */
	table.addEventListener( 'change', function ( e ) {
		if ( ! e.target.classList.contains( 'custom-flat-rate-type-select' ) ) {
			return;
		}

		var box = flatRateBoxes[ e.target.value ];
		if ( ! box ) {
			return;
		}

		var row = e.target.closest( 'tr' );
		var fields = {
			length: box.length,
			width: box.width,
			height: box.height,
			box_weight: box.weight,
			max_weight: box.max_weight,
		};

		Object.keys( fields ).forEach( function ( field ) {
			var input = row.querySelector( 'input[name^="custom_flat_rate_boxes_' + field + '"]' );
			if ( input && ! input.value ) {
				input.value = fields[ field ];
			}
		} );
	} );
} )();
