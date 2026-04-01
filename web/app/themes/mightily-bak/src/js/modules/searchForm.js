var sf,
	searchForm = {

		settings: {
			form: jQuery('form.site-search'),
			open: jQuery('.btn.search'),
			close: jQuery('button.close'),
			hasLoaded: false
		},

		init: function () {
			sf = this.settings;
			this.bindUIActions();
			console.log('search form loaded!');
		},

		bindUIActions: function () {
			$(document).on('facetwp-refresh', function () {
				console.log('FWP Refresh');

				//$('.loading-search').show();
				$('#search-results').addClass('loading');
			});

			$(document).on('facetwp-loaded', function () {
				console.log('FWP Loaded');

				//$('.loading-search').hide();
				$('#search-results').removeClass('loading');
				searchForm.setupFacets();
				sf.hasLoaded = true;
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
		}
	};
