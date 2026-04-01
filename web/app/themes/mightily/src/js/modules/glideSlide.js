var gs,
	glideSlide = {

		settings: {
			sliderXLarge: document.querySelectorAll('.slider.x-large .glide'),
			sliderLarge: document.querySelectorAll('.slider.large .glide'),
			sliderMedium: document.querySelectorAll('.slider.medium .glide'),
			sliderSmall: document.querySelectorAll('.slider.small .glide'),
			sliderProducts: document.querySelectorAll('.slider.products .glide'),
			sliderTimeline: document.querySelectorAll('.slider.timeline .glide'),
			sliderTimelineBtnLeft: document.querySelectorAll('.slider.timeline .glide__arrow--left'),
			sliderTimelineBtnRight: document.querySelectorAll('.slider.timeline .glide__arrow--right'),
			sliderTimelineSlides: document.querySelectorAll('.slider.timeline .glide__slide')
			// sliderXLarge: $('.slider.x-large .glide'),
			// sliderLarge: $('.slider.large .glide'),
			// sliderMedium: $('.slider.medium .glide'),
			// sliderSmall: $('.slider.small .glide'),
			// sliderProducts: $('.slider.products .glide'),
			// sliderTimeline: $('.slider.timeline .glide'),
			// sliderTimelineBtnLeft: $('.slider.timeline .glide__arrow--left'),
			// sliderTimelineBtnRight: $('.slider.timeline .glide__arrow--right'),
			// sliderTimelineSlides: $('.slider.timeline .glide__slide')
		},

		init: function() {
			gs = this.settings;
			this.bindUIActions();
			// console.log('glideSlide loaded!');
		},

		bindUIActions: function() {

			function adaptiveHeight(ele) {
				ele.on('build.after', function() {
					var slideHeight = $('.glide__slide--active').outerHeight();
					var glideTrack = $('.glide__track').outerHeight();
					if (slideHeight != glideTrack) {
						var newHeight = slideHeight;
						$('.glide__track').css('height', newHeight);
					}
					// console.log('Glide.build active slide height: ' + slideHeight);
				});

				ele.on('run.after', function() {
					var slideHeight = $('.glide__slide--active').outerHeight();
					var glideTrack = $('.glide__track').outerHeight();
					if (slideHeight != glideTrack) {
						var newHeight = slideHeight;
						$('.glide__track').css('height', newHeight);
					}
					// console.log('Glide.run active slide height: ' + slideHeight);
				});
			}

			for (var i = 0; i < gs.sliderTimeline.length; i++) {
				// console.log(i); // index
				// console.log(gs.sliderTimeline[i]); // value
				var sliderTimeline = new Glide(gs.sliderTimeline[i], {
					// type: 'carousel',
					perView: 3,
					gap: 0,
					breakpoints: {
						1215: {
							perView: 3
						},
						960: {
							perView: 2
						},
						680: {
							perView: 1
						}
					}
				});
				adaptiveHeight(sliderTimeline);
				sliderTimeline.mount();

				for (var x = 0; x < gs.sliderTimelineSlides.length; x++) {
					// Looping through all of the slide for the timeline
				}
					// Substracting one from the variable to match the length number
				x = x - 1;
				sliderTimeline.on(['move.after'], function() {
					// Hiding the left button if the user is on the first slide
					// and hiding the right button if the user is on the last slide
					if (sliderTimeline.index == 0) {
						gs.sliderTimelineBtnLeft[i].style.display = 'none';
					} else {
						gs.sliderTimelineBtnLeft[i].style.display = 'block';
					}

					if (sliderTimeline.index == x) {
						gs.sliderTimelineBtnRight[i].style.display = 'none';
					} else {
						gs.sliderTimelineBtnRight[i].style.display = 'block';
					}
				})
			}

			for (var i = 0; i < gs.sliderXLarge.length; i++) {
				// console.log(i); // index
				// console.log(gs.sliderLarge[i]); // value

				var sliderXLarge = new Glide(gs.sliderXLarge[i], {
					type: 'carousel',
					perView: 1
				});

				adaptiveHeight(sliderXLarge);

				sliderXLarge.mount();
			}

			for (var i = 0; i < gs.sliderLarge.length; i++) {
				// console.log(i); // index
				// console.log(gs.sliderLarge[i]); // value

				var sliderLarge = new Glide(gs.sliderLarge[i], {
					type: 'carousel',
					perView: 1
				});
				adaptiveHeight(sliderLarge);
				sliderLarge.mount();
			}

			for (var i = 0; i < gs.sliderMedium.length; i++) {
				var sliderMedium = new Glide(gs.sliderMedium[i], {
					type: 'carousel',
					perView: 3,
					gap: 60,
					breakpoints: {
						960: {
							perView: 2
						},
						680: {
							perView: 1
						}
					}
				});
				adaptiveHeight(sliderMedium);
				sliderMedium.mount();
			}

			for (var i = 0; i < gs.sliderSmall.length; i++) {
				var sliderSmall = new Glide(gs.sliderSmall[i], {
					type: 'carousel',
					perView: 2,
					gap: 100,
					breakpoints: {
						960: {
							perView: 1
						}
					}
				});
				adaptiveHeight(sliderSmall);
				sliderSmall.mount();
			}
		}

	};
