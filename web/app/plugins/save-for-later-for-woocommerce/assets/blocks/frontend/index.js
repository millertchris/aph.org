(() => {
	'use strict';

	var external_plugins = window["wp"]["plugins"];
	var external_element = window["wp"]["element"];
	var external_blocks = window["wp"]["blocks"];
	var external_blockEditor = window["wp"]["blockEditor"];
	var external_i18n = window["wp"]["i18n"];
	var external_data = window["wp"]["data"];
	var external_compose = window["wp"]["compose"];
	var external_components = window["wp"]["components"];
	var external_primitives = window["wp"]["primitives"];
	var external_wc_blocksCheckout = window["wc"]["blocksCheckout"];
	var external_wc_priceFormat = window["wc"]["priceFormat"];
	var external_wc_settings = window["wc"]["wcSettings"];

	function add_url_args(key, value, url) {
		var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
		var separator = url.indexOf('?') !== -1 ? "&" : "?";

		if (url.match(re)) {
			return url.replace(re, '$1' + key + "=" + value + '$2');
		} else {
			return url + separator + key + "=" + value;
		}
	};

	function remove_url_args(key, url) {
		var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");

		return (url.match(re)) ? url.replace(re, '&') : url;
	};

	var callBack = {
		cartFilters: {
			cartItemClass: function (v, e, a) {
				if (jQuery.inArray(a.cartItem.key, sfl_blocks_params.sfl_valid_cart_items) !== -1) {
					v = 'svs-item--' + a.cartItem.id + '--' + a.cartItem.key;
				}

				return v;
			},
		},
	};

	const render = () => {
		var setSVSLink = setInterval(function () {
			const cartHtml = document.querySelector('.wc-block-cart__main');

			if (cartHtml !== null) {
				const elementsWithBadge = document.querySelectorAll('[class*=svs-item]');

				elementsWithBadge.forEach(elementWithBadge => {
					const classList = Array.from(elementWithBadge.classList);
					const className = classList.filter(classList => classList.includes('svs-item')).shift();
					const itemID = className.split('--')[1];
					const itemKey = className.split('--')[2];
					const linkID = 'saveforlater_button--' + itemID;

					if (!elementWithBadge.querySelector('#' + linkID)) {
						var $url = add_url_args('sfl_product_id', itemID, sfl_blocks_params.add_sfl_cart_url);
						$url = add_url_args('sfl_cart_item_key', itemKey, $url);
						const link = document.createElement('a');
						link.id = linkID;
						link.className = 'sfl_cart_link svs-button wc-block-cart-item__remove-link'
						link.setAttribute('href', $url);
						link.innerHTML = "Save for Later";
						link.style = 'margin-right:5px';
						elementWithBadge.querySelector('.wc-block-cart-item__quantity').insertBefore(link, elementWithBadge.querySelector('.wc-block-cart-item__quantity').children[1]);
					}

					clearInterval(setSVSLink);
				});
			}
		}, 2000);

		setTimeout(function () {
			clearInterval(setSVSLink);
		}, 5000);
	}

	external_wc_blocksCheckout.registerCheckoutFilters('save-for-later-for-woocommerce', {
		cartItemClass: callBack.cartFilters.cartItemClass,
	});

	external_plugins.registerPlugin('save-for-later-for-woocommerce', {
		render,
		scope: 'woocommerce-checkout'
	});
})();