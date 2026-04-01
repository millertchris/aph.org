var lf,
	LayoutFilter = {
		settings: {
			mainWindow: jQuery(window),
			menuToggle: '',
			filterGroups: jQuery('.filter.active'),
			textFilters: jQuery('.text-filter'),
			textSubmitButton: jQuery('input.text-filter-button'),
			selectFilters: jQuery('.select-filter')
		},
		init: function () {
			lf = this.settings;
			this.bindUIActions();
			console.log('LayoutFilter loaded!');
		},
		bindUIActions: function () {
			lf.filterGroups.each(function () {
				var filterGroup = jQuery(this);
				// changing the filter search to work with the search button for accessibility purposes
				if (filterGroup.find('.text-filter').length > 0) {
					var textFilter = filterGroup.find('.text-filter');
					lf.textSubmitButton.on('click', function (evt) {
						evt.preventDefault();
						LayoutFilter.filterData(filterGroup);
					});
					// textFilter.keydown(function(evt){
					// 	if(evt.which == 13){
					// 		evt.preventDefault();
					// 	}
					// 	});
					// textFilter.keyup(function(evt){
					// 	evt.preventDefault();
					// 	// console.log('text change');
					// 	LayoutFilter.filterData(filterGroup);
					// 	});
				}
				if (filterGroup.find('.select-filter').length > 0) {
					var selectFilter = filterGroup.find('.select-filter');
					selectFilter.change(function () {
						// console.log('select change');
						LayoutFilter.filterData(filterGroup);
					});
				}
				// Create the temp element that is used to hide the omitted results
				filterGroup.next().after('<ul id="filter-temp"></ul>');
				// Add a data attribute for sorting later
				filterGroup.next().find('li,tbody tr').each(function (index, element) {
					jQuery(this).data('sort', index);
					jQuery(this).attr('data-sort', index);
				});
			});
		},
		filterData: function (ele) {
			// ele returns jquery object of filter div that contains all filter fields
			var textValue = ele.find('.text-filter').val();
			var selectActive = false;
			var selectElement = ele.find('.select-filter');
			var selectValue = '';
			var count = 0;
			var resultsContainer = ele.next();
			var tempContainer = ele.next().next();
			if (resultsContainer.hasClass('wl-tab-wrap')) {
				resultsContainer = resultsContainer.find('.bx--data-table tbody');
			}
			console.log(tempContainer);
			// Move all elements from results container to temp container
			resultsContainer.find('li,tr.cart_table_item').each(function () {
				var thisItem = jQuery(this);
				thisItem.appendTo(tempContainer);
			});
			// if the select element is present
			if (selectElement.length > 0) {
				var selectTaxonomy = selectElement.data('taxonomy');
				if (selectElement.val() != 'all') {
					selectActive = true;
					selectValue = selectElement.val();
				}
			}
			tempContainer.find('.no-results').hide();
			// Loop through the list elements from temp container
			tempContainer.find('li,tr.cart_table_item').each(function () {
				var thisItem = jQuery(this);
				var thisItemData = thisItem.data(selectTaxonomy),
					thisItemData = "" + thisItemData;
				if (selectActive) {
					// If the list item does not contain the text phrase fade it out
					if (thisItem.text().search(new RegExp(textValue, "i")) < 0 || thisItemData.search(new RegExp(selectValue, "i")) < 0) {
						// thisItem.fadeOut();
						// Show the list item if the phrase matches and increase the count by 1
					} else {
						thisItem.prependTo(resultsContainer);
						count++;
					}
				} else {
					// If the list item does not contain the text phrase fade it out
					if (thisItem.text().search(new RegExp(textValue, "i")) < 0) {
						// thisItem.fadeOut();
						// Show the list item if the phrase matches and increase the count by 1
					} else {
						thisItem.prependTo(resultsContainer);
						count++;
					}
				}
			});
			if (count === 0) {
				tempContainer.find('.no-results').appendTo(resultsContainer);
				resultsContainer.find('.no-results').show();
			}
			// Sort by data-sort attribute
			resultsContainer.find('li, tbody tr').sort(function (a, b) {
				return +a.dataset.sort - +b.dataset.sort;
			}).appendTo(resultsContainer);
		}
	}
