var ss,
	slickSlide = {

		settings: {
            sliderXLargeStyle1: document.querySelectorAll('.slider.x-large.style-1 .slide-list'),
            sliderXLargeStyle2: document.querySelectorAll('.slider.x-large.style-2 .slide-list'),
			sliderLarge: document.querySelectorAll('.slider.large .slide-list'),
			sliderMedium: document.querySelectorAll('.slider.medium .slide-list'),
			sliderSmall: document.querySelectorAll('.slider.small .slide-list'),
            sliderTimeline: document.querySelectorAll('.slider.timeline .slide-list'),
            sliderProducts: document.querySelectorAll('.product-carousel'),
            sliderProductVideos: document.querySelectorAll('.product-videos-slider')
		},

		init: function() {
			ss = this.settings;
			this.bindUIActions();
			// console.log('slickSlide loaded!');
		},

		bindUIActions: function() {

            for (var i = 0; i < ss.sliderTimeline.length; i++) {
                $('.slider.timeline .slide-list').on('init', function(){
                    // Move the buttons to the start of the slide element
                    $(this).find('.slick-next').prependTo($(this)).attr('aria-label', 'Next Carousel Slide');
                    $(this).find('.slick-prev').prependTo($(this)).attr('aria-label', 'Previous Carousel Slide');

                    var liveregion = document.createElement('div');
                    liveregion.setAttribute('aria-live', 'polite');
                    liveregion.setAttribute('aria-atomic', 'true');
                    liveregion.setAttribute('class', 'liveregion visually-hidden');
                    this.appendChild(liveregion);                    
                });
                $('.slider.timeline .slide-list').on('afterChange', function(event, slick, currentSlide, nextSlide){
                    var currentSlideInt = parseInt(currentSlide)+1;
                    $(this).find('.liveregion').text('Slide ' + currentSlideInt + ' of ' + slick.slideCount + ' ' + $(this).find('.slick-current .title').text());
                });                 
                $('.slider.timeline .slide-list').not('.slick-initialized').slick({
                    // accessibility: true,
                    speed: 400,
                    arrows: true,
                    infinite: false,
                    adaptiveHeight: true,
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    cssEase: 'linear',
                    responsive: [
                        {
                            breakpoint: 960,
                            settings: {
                                slidesToShow: 2
                            }
                        },
                        {
                            breakpoint: 680,
                            settings: {
                                slidesToShow: 1
                            }
                        },
                    ]
                });
            }

            for (var i = 0; i < ss.sliderSmall.length; i++) {
                $('.slider.small .slide-list').on('init', function(){
                    // Move the buttons to the start of the slide element
                    $(this).find('.slick-next').prependTo($(this)).attr('aria-label', 'Next Carousel Slide');
                    $(this).find('.slick-prev').prependTo($(this)).attr('aria-label', 'Previous Carousel Slide');

                    var liveregion = document.createElement('div');
                    liveregion.setAttribute('aria-live', 'polite');
                    liveregion.setAttribute('aria-atomic', 'true');
                    liveregion.setAttribute('class', 'liveregion visually-hidden');
                    this.appendChild(liveregion);                    
                });
                $('.slider.small .slide-list').on('afterChange', function(event, slick, currentSlide, nextSlide){
                    var currentSlideInt = parseInt(currentSlide)+1;
                    $(this).find('.liveregion').text('Slide ' + currentSlideInt + ' of ' + slick.slideCount + ' ' + $(this).find('.slick-current .title').text());
                });                 
                $('.slider.small .slide-list').not('.slick-initialized').slick({
                    // accessibility: true,
                    speed: 400,
                    arrows: true,
                    infinite: true,
                    adaptiveHeight: true,
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    cssEase: 'linear',
                    responsive: [
                        {
                            breakpoint: 960,
                            settings: {
                                slidesToShow: 1
                            }
                        },
                    ]
                });
            }

            for (var i = 0; i < ss.sliderMedium.length; i++) {
                $('.slider.medium .slide-list').on('init', function(){
                    // Move the buttons to the start of the slide element
                    $(this).find('.slick-next').prependTo($(this)).attr('aria-label', 'Next Carousel Slide');
                    $(this).find('.slick-prev').prependTo($(this)).attr('aria-label', 'Previous Carousel Slide');

                    var liveregion = document.createElement('div');
                    liveregion.setAttribute('aria-live', 'polite');
                    liveregion.setAttribute('aria-atomic', 'true');
                    liveregion.setAttribute('class', 'liveregion visually-hidden');
                    this.appendChild(liveregion);                    
                });
                $('.slider.medium .slide-list').on('afterChange', function(event, slick, currentSlide, nextSlide){
                    var currentSlideInt = parseInt(currentSlide)+1;
                    $(this).find('.liveregion').text('Slide ' + currentSlideInt + ' of ' + slick.slideCount + ' ' + $(this).find('.slick-current .title').text());
                });                
                $('.slider.medium .slide-list').not('.slick-initialized').slick({
                    // accessibility: true,
                    speed: 400,
                    arrows: true,
                    infinite: true,
                    adaptiveHeight: true,
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    cssEase: 'linear',
                    responsive: [
                        {
                            breakpoint: 960,
                            settings: {
                                slidesToShow: 2
                            }
                        },
                        {
                            breakpoint: 680,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        },
                    ]
                });
            }

            for (var i = 0; i < ss.sliderLarge.length; i++) {
                $('.slider.large .slide-list').on('init', function(){
                    // Move the buttons to the start of the slide element
                    $(this).find('.slick-next').prependTo($(this)).attr('aria-label', 'Next Carousel Slide');
                    $(this).find('.slick-prev').prependTo($(this)).attr('aria-label', 'Previous Carousel Slide');

                    var liveregion = document.createElement('div');
                    liveregion.setAttribute('aria-live', 'polite');
                    liveregion.setAttribute('aria-atomic', 'true');
                    liveregion.setAttribute('class', 'liveregion visually-hidden');
                    this.appendChild(liveregion);                    
                });
                $('.slider.large .slide-list').on('afterChange', function(event, slick, currentSlide, nextSlide){
                    var currentSlideInt = parseInt(currentSlide)+1;
                    $(this).find('.liveregion').text('Slide ' + currentSlideInt + ' of ' + slick.slideCount + ' ' + $(this).find('.slick-current .title').text());
                });                  
                $('.slider.large .slide-list').not('.slick-initialized').slick({
                    // accessibility: true,
                    speed: 400,
                    arrows: true,
                    infinite: true,
                    adaptiveHeight: true,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    cssEase: 'linear',
                });
            }

            for (var i = 0; i < ss.sliderXLargeStyle1.length; i++) {
                $('.slider.x-large.style-1').on('init', function(){
                    // console.log($(this));
                    // $(this).find('.slick-list').attr('role', 'region');
                    // $(this).find('.slick-list').attr('aria-live', 'polite');
                    $(this).find('.slick-next').prependTo($(this).find('.slick-slider')).attr('aria-label', 'Next Carousel Slide');
                    $(this).find('.slick-prev').prependTo($(this).find('.slick-slider')).attr('aria-label', 'Previous Carousel Slide');

                    var liveregion = document.createElement('div');
                    liveregion.setAttribute('aria-live', 'polite');
                    liveregion.setAttribute('aria-atomic', 'true');
                    liveregion.setAttribute('class', 'liveregion visually-hidden');
                    this.appendChild(liveregion);                    
                });
                $('.slider.x-large.style-1').on('afterChange', function(event, slick, currentSlide, nextSlide){
                    var currentSlideInt = parseInt(currentSlide)+1;
                    $(this).find('.liveregion').text('Slide ' + currentSlideInt + ' of ' + slick.slideCount + ' ' + $(this).find('.slick-current .title').text());
                });
                $('.slider.x-large.style-1 .slide-list').not('.slick-initialized').slick({
                    // accessibility: true,
                    speed: 400,
                    arrows: true,
                    infinite: true,
                    adaptiveHeight: true,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    cssEase: 'linear'
                });
            }

            for (var i = 0; i < ss.sliderXLargeStyle2.length; i++) {
                $('.slider.x-large.style-2').on('init', function(){
                    // console.log($(this));
                    // $(this).find('.slick-list').attr('role', 'region');
                    // $(this).find('.slick-list').attr('aria-live', 'polite');
                    $(this).find('.slick-next').prependTo($(this).find('.slick-slider')).attr('aria-label', 'Next Carousel Slide');
                    $(this).find('.slick-prev').prependTo($(this).find('.slick-slider')).attr('aria-label', 'Previous Carousel Slide');

                    var liveregion = document.createElement('div');
                    liveregion.setAttribute('aria-live', 'polite');
                    liveregion.setAttribute('aria-atomic', 'true');
                    liveregion.setAttribute('class', 'liveregion visually-hidden');
                    this.appendChild(liveregion);                    
                });
                $('.slider.x-large.style-2').on('afterChange', function(event, slick, currentSlide, nextSlide){
                    var currentSlideInt = parseInt(currentSlide)+1;
                    $(this).find('.liveregion').text('Slide ' + currentSlideInt + ' of ' + slick.slideCount + ' ' + $(this).find('.slick-current .title').text());
                });
                $('.slider.x-large.style-2 .slide-list').not('.slick-initialized').slick({
                    // accessibility: true,
                    speed: 400,
                    arrows: true,
                    infinite: true,
                    adaptiveHeight: true,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    cssEase: 'linear',
                    fade: true
                });
            }

            for (var i = 0; i < ss.sliderProducts.length; i++) {
                $($('.product-carousel .line-items').get(i)).on('init breakpoint', function(){
                    //alert('wooorks');
                    slickSlide.forceEqualHeights($($('.product-carousel .line-items').get(i)));
                });                
                $($('.product-carousel .line-items').get(i)).slick({
                    // accessibility: true,
                    speed: 400,
                    arrows: true,
                    infinite: true,
                    adaptiveHeight: false,
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    cssEase: 'linear',
                    responsive: [
                        {
                            breakpoint: 1100,
                            settings: {
                                slidesToShow: 2
                            }
                        },
                        {
                            breakpoint: 680,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        },
                    ]                    
                });
            }
            $('.product-videos-slider').on('beforeChange', function(event, slick, currentSlide, nextSlide){
                // Pause all iframes when the slider changes.
                $('.vimeo-iframe').each(function(){
                    var vimeoPlayer = new Vimeo.Player(this);
                    vimeoPlayer.pause();
                });
            });
            $('.product-videos-slider').slick({
                // accessibility: true,
                speed: 400,
                arrows: false,
                infinite: true,
                adaptiveHeight: false,
                slidesToShow: 1,
                slidesToScroll: 1,
                cssEase: 'linear',
                asNavFor: '.product-videos-slider-nav'                   
            });
            $('.product-videos-slider-nav').slick({
                // accessibility: true,
                speed: 400,
                arrows: true,
                infinite: true,
                adaptiveHeight: false,
                slidesToShow: 3,
                slidesToScroll: 1,
                cssEase: 'linear',
                asNavFor: '.product-videos-slider',
                centerMode: true,
                centerPadding: 0,
                focusOnSelect: true                                
            });                                   
        },
        forceEqualHeights: function(lineItems){
            var slideItems = lineItems.find('.slick-slide .item');

            var elementHeights = slideItems.map(function() {
                return $(this).height();
            }).get();
            
            // Math.max takes a variable number of arguments
            // `apply` is equivalent to passing each height as an argument
            var maxHeight = Math.max.apply(null, elementHeights);
        
            // Set each height to the max height
            slideItems.height(maxHeight);

        }

	};
