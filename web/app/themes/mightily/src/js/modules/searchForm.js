var sf,
	searchForm = {

		settings: {
			form: jQuery('form.site-search'),
			open: jQuery('.btn.search'),
			close: jQuery('button.close'),
			hasLoaded: false,
			focusToFirstItem: false
		},

		init: function () {
			sf = this.settings;
			this.bindUIActions();
			// console.log('search form loaded!');
		},

		bindUIActions: function () {
			$(document).on('facetwp-refresh', function () {
				// console.log('FWP Refresh');
				//$('.loading-search').show();
				$('#search-results').addClass('loading');
				searchForm.setupFacets();
				searchForm.skipDisabled();
			});

			$(document).on('facetwp-loaded', function () {
				// console.log('FWP Loaded');
				//$('.loading-search').hide();
				$('#search-results').removeClass('loading');
				searchForm.setupFacets();
				searchForm.skipDisabled();
				searchForm.updateFocus();
				sf.hasLoaded = true;
			});

			$(document).on('click', 'a.facetwp-page', function(){
				sf.focusToFirstItem = true;	
			});

			$('#open-filters').on('click', function () {
				$('.search-filters-aside').toggleClass('active');
				$('.line-items').toggleClass('active');
			});
		
			$('#close-filters').on('click', function () {
				$('.search-filters-aside').removeClass('active');
				$('.line-items').removeClass('active');
			});

			if (sf.form.length > 0) {

				// Open search form
				sf.open.click(function (evt) {
					sf.form.addClass('open');
				});

				// Close search form
				sf.close.click(function (evt) {
					if (sf.form.hasClass('open')) {
						sf.form.removeClass('open');
					}
				});

			}	
		},
		setupFacets: function(){
			// If discontinued facet is there, move some options around
			if($('.facetwp-facet-product_discontinued').length > 0){
				$('.facetwp-facet-product_discontinued').find('[data-value="0"]').remove();
			}
			setTimeout(function(){
				// Override to fix aria labels
				$('.facetwp-page').each(function() {
					var ariaLabel = '';
					if($(this).text() == '<<'){
						ariaLabel = 'Previous Page';
					} else if($(this).text() == '>>') {
						ariaLabel = 'Next Page';
					} else if($(this).text() == '…') {
						ariaLabel = 'Ellipsis';
					} else {
						ariaLabel = 'Page ' + $(this).text();
					}
					if(ariaLabel != ''){
						$(this).attr('aria-label', ariaLabel);
					}
				});
				// Fix dots link that doesnt link anywhere
				if($('a.facetwp-page.dots').length > 0){
					var attrs = {};
					$('a.facetwp-page.dots').removeAttr('role');
					$.each($("a.facetwp-page.dots")[0].attributes, function(idx, attr) {
						attrs[attr.nodeName] = attr.nodeValue;
					});				
					$('a.facetwp-page.dots').replaceWith(function () {
						return $("<span />", attrs).append($(this).contents());
					});	
				}					
			}, 100);			
		},
		skipDisabled: function(){
			// If checkbox facet is disabled, set its tabindex so it cannot be focused
			setTimeout(function(){
				$('.facetwp-checkbox.disabled').attr('tabindex', '-1');
			}, 100);
		},
		updateFocus: function(){
			setTimeout(function(){
				if(sf.focusToFirstItem){
					$('div.item:first-of-type a').focus();
					sf.focusToFirstItem = false;
				}
			}, 100);
		}
	};
