/* global sfl_frontend_params */

jQuery(function ($) {
	'use strict';

	var SFL_Frontend = {
		init: function () {
			$(document).on('click', '.sfl_pagination', this.sfl_list_pagination);
			$(document).on('click', '.sfl-remove', this.confirmRemoveItem);
			$(document).on('change', '.sfl_select_all', this.sfl_product_select_all);
			$(document).on('change', '.sfl_entry', this.sfl_each_entry_cb);
		},
		confirmRemoveItem(e) {
			if ( ! window.confirm( sfl_frontend_params.remove_from_sfl_list_msg )) {
				e.preventDefault();
			}
		},
		sfl_list_pagination: function (event) {
			event.preventDefault();
			var $this = $(event.currentTarget),
				table = $this.closest('table.sfl-list-table'),
				table_body = table.find('tbody'),
				current_page = $this.data('page');

			SFL_Frontend.block(table_body);

			var data = ({
				action: 'sfl_list_pagination',
				page_number: current_page,
				current_user: sfl_frontend_params.current_user,
				is_logged_in: sfl_frontend_params.is_logged_in,
				page_url: sfl_frontend_params.current_page_url,
				sfl_security: sfl_frontend_params.sfl_list_pagination_nonce,
			});
			$.post(sfl_frontend_params.ajaxurl, data, function (res) {
				if (true === res.success) {
					table_body.html(res.data.html);

					table.find('.sfl_pagination').removeClass('current');
					$($this).addClass('current');

					table.find('.sfl_next_pagination').attr('data-page', current_page + 1);
					table.find('.sfl_prev_pagination').attr('data-page', current_page - 1);
				} else {
					alert(res.data.error);
				}

				SFL_Frontend.unblock(table_body);
			}
			);
		},

		sfl_product_select_all: function (event) {
			event.preventDefault();
			var $this = $(event.currentTarget);

			if ($($this).is(':checked')) {
				$('.sfl-list-table').find('.sfl_entry').prop('checked', true);
			} else {
				$('.sfl-list-table').find('.sfl_entry').prop('checked', false);
			}
		},

		sfl_each_entry_cb: function (event) {
			event.preventDefault();
			var $this = $(event.currentTarget);

			if ($($this).is(':checked')) {
				var total_entry = $('.sfl-list-table').find('.sfl_entry').length;
				var checked_entry = $('.sfl-list-table').find('.sfl_entry').filter(':checked').length;

				if (total_entry === checked_entry) {
					$('.sfl_select_all').prop('checked', true);
				}

			} else {
				$('.sfl_select_all').prop('checked', false);
			}
		},

		block: function (id) {
			$(id).block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.7
				}
			});
		}, unblock: function (id) {
			$(id).unblock();
		},
	};
	SFL_Frontend.init();
});
