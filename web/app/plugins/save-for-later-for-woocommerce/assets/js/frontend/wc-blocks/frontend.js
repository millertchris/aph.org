var el = React.createElement;
var Fragment = wp.element.Fragment;
var registerPlugin = wp.plugins.registerPlugin;
var moreIcon = React.createElement( 'svg' );

import {useSelect,dispatch} from '@wordpress/data';

const addProductInList = async(cartItemKey) =>{
	let formData = new FormData();
	formData.append('action', ywsfl_args.actions.add );
	formData.append( 'security',ywsfl_args.nonce.add );
	formData.append( 'save_for_later', cartItemKey );
	formData.append( 'context', 'ywsfl-cart-block' );

	try {
		const response = await axios.post(ywsfl_args.ajaxURL, formData);
		return response;
	} catch (error) {
		console.log(error);
	}
}
const render = () => {
	const cartItems = useSelect((select) => select('wc/store/cart').getCartData().items);
	console.log(cartItems);
	useEffect(() => {
		setTimeout(function () {
			const cartHtml = document.querySelector('.wc-block-cart__main');
			if ( cartHtml !== null) {
				const elementsWithBadge = document.querySelectorAll('[class*=ywsfl-cart-key]');
				elementsWithBadge.forEach(elementWithBadge => {
					const classList = Array.from(elementWithBadge.classList);
					const className = classList.filter(classList => classList.includes('ywsfl-cart-key')).shift();
					const itemID = className.split('--')[1];
					const linkID = 'saveforlater_button--'+itemID;
					if (!elementWithBadge.querySelector('#' + linkID)) {
						const item  = cartItems.filter(cartItem => cartItem.id === parseInt(itemID)).shift();
						const link = document.createElement('button');
						link.id = linkID;
						link.className = 'ywsfl-add-cart-button wc-block-cart-item__remove-link'
						link.setAttribute( 'data-saveforlater',item.key );
						link.innerHTML = ywsfl_args.buttonLabel;
						link.style = 'margin-right:5px';
						link.addEventListener('click',(event)=>{
							event.preventDefault();
							const keyToAdd = event.target.dataset.saveforlater;
							addProductInList(keyToAdd).then(response=>{
								return response.data;
							}).then(data=>{

								document.getElementById('ywsfl_general_content').innerHTML= data.template;
								apiFetch({
									path: addQueryArgs('/wc/store/v1/cart/remove-item', {
										key: keyToAdd,
									}),
								method: 'POST',
								cache: 'no-store',
								}).then((result) => {
									dispatch('wc/store/cart').receiveCart(result);
								}).catch((error) => {
									dispatch('wc/store/cart').receiveError(error);
								});
							});
						});
						elementWithBadge.querySelector('.wc-block-cart-item__quantity').insertBefore(link, elementWithBadge.querySelector('.wc-block-cart-item__quantity').children[1]);
					}
				});
			}
		}, 500);
	}, [cartItems]);
};

registerPlugin( 'sfl-save-cart',{
	render,
	scope:'woocommerce-checkout'
});