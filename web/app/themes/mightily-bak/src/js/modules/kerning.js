var mk,
	manualKern = {
		settings: {
			kernEls: $('.manual-kern'),
		},
		init: function () {
			mk = this.settings;
			console.log('manual kern loaded!');
			this.runDuplication();
		},
		runSpans: function ($this) {
			// Build an array of letters
			var charArray = $this.text().split('');
			// Remove all characters from this element
			$this.empty();
			// Loop through the character array and add them back this element with <span> tags.
			$.each(charArray, function (index, value) {
				$this.append('<span class="char-' + index + ' char-' + value + '">' + value + '</span>');
			});
		},
		runDuplication: function () {
			mk.kernEls.each(function () {
				var $this = $(this);
				var $clone = $this.clone();
				$clone.addClass('screen-reader-text');
				$clone.removeClass('manual-kern');
				$clone.insertBefore($(this));
				$this.attr('aria-hidden', 'true');
				$this.addClass($this.text().toLowerCase().replace(/[\W_]+/g, '').substring(10, 0));
				manualKern.runSpans($this);
			});
		},
	};
