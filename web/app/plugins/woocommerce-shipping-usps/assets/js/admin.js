/* global usps_settings */
(function ($) {
    const usps_api_type_field_selector = '#woocommerce_usps_api_type';

    function usps_toggle_api_settings() {
        const api_type_value = $(usps_api_type_field_selector).val(),
            api_type_attr = 'data-usps_api_type',
            show_selector = '[' + api_type_attr + '="' + api_type_value + '"]',
            rows_to_show = $(show_selector).closest('tr'),
            rows_to_hide = $('[' + api_type_attr + ']:not(' + show_selector +
                ')').closest('tr');

        rows_to_show.show();
        rows_to_hide.hide();
    }

    usps_toggle_api_settings();

    $(document).on('change', usps_api_type_field_selector, function () {
        usps_toggle_api_settings();
    });

    // Services
    if (usps_settings.is_instance_settings) {
        function usps_toggle_media_mail_row() {
            var isChecked = false;
            $('input[name^="usps_service[D_MEDIA_MAIL]"][name$="[enabled]"]').each(function () {
                if ($(this).is(':checked')) {
                    isChecked = true;
                    return false;
                }
            });

            if (isChecked) {
                $('#media_mail_notice').show();
            } else {
                $('#media_mail_notice').hide();
            }
        }

        // Initial check when the page loads
        usps_toggle_media_mail_row();

        // Event listener for changes on the checkboxes
        $('input[name^="usps_service[D_MEDIA_MAIL]"][name$="[enabled]"]').change(function () {
            usps_toggle_media_mail_row();
        });
    }

    // Box Packing
    if (usps_settings.is_instance_settings) {
        jQuery('#woocommerce_usps_enable_standard_services').change(function () {
            if (jQuery(this).is(':checked')) {
                jQuery('#woocommerce_usps_mediamail_restriction').closest('tr').show();
                jQuery('#service_options, #packing_options').show();
                jQuery('#woocommerce_usps_packing_method').closest('tr').show();
                jQuery('#woocommerce_usps_packing_method').change();
            } else {
                jQuery('#woocommerce_usps_mediamail_restriction').closest('tr').hide();
                jQuery('#service_options, #packing_options').hide();
                jQuery('#woocommerce_usps_packing_method').closest('tr').hide();
            }
        }).change();

        jQuery('#woocommerce_usps_packing_method').change(function () {

            if (jQuery('#woocommerce_usps_enable_standard_services').is(':checked')) {

                if (jQuery(this).val() === 'box_packing') {
                    jQuery('#packing_options').show();
                    jQuery('#woocommerce_usps_unpacked_item_handling').closest('tr').show();
                } else {
                    jQuery('#packing_options').hide();
                    jQuery('#woocommerce_usps_unpacked_item_handling').closest('tr').hide();
                }

                if (jQuery(this).val() === 'weight') {
                    jQuery('#woocommerce_usps_max_weight').closest('tr').show();
                } else {
                    jQuery('#woocommerce_usps_max_weight').closest('tr').hide();
                }

            }

        }).change();

        jQuery('#woocommerce_usps_enable_flat_rate_boxes').change(function () {

            var tr_flat_rate_express_title = jQuery('#woocommerce_usps_flat_rate_express_title').closest('tr');
            var tr_flat_rate_priority_title = jQuery('#woocommerce_usps_flat_rate_priority_title').closest('tr');
            var tr_flat_rate_fee = jQuery('#woocommerce_usps_flat_rate_fee').closest('tr');

            if (jQuery(this).val() === 'yes') {
                tr_flat_rate_express_title.show();
                tr_flat_rate_priority_title.show();
                tr_flat_rate_fee.show();
            } else if (jQuery(this).val() === 'no') {
                tr_flat_rate_express_title.hide();
                tr_flat_rate_priority_title.hide();
                tr_flat_rate_fee.hide();
            } else if (jQuery(this).val() === 'priority') {
                tr_flat_rate_express_title.hide();
                tr_flat_rate_priority_title.show();
                tr_flat_rate_fee.show();
            } else if (jQuery(this).val() === 'express') {
                tr_flat_rate_express_title.show();
                tr_flat_rate_priority_title.hide();
                tr_flat_rate_fee.show();
            }

        }).change();

        var usps_boxes = jQuery('.usps_boxes');
        usps_boxes.find('.insert').click(function () {
            var $tbody = usps_boxes.find('tbody');
            var size = $tbody.find('tr').length;
            var code = '<tr class="new">\
							<td class="check-column"><input type="checkbox" /></td>\
							<td><input type="text" size="10" maxlength="150" name="boxes_name[' + size + ']" /></td>\
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="boxes_outer_length[' + size + ']" /></td>\
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="boxes_outer_width[' + size + ']" /></td>\
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="boxes_outer_height[' + size + ']" /></td>\
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="boxes_inner_length[' + size + ']" /></td>\
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="boxes_inner_width[' + size + ']" /></td>\
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="boxes_inner_height[' + size + ']" /></td>\
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="boxes_box_weight[' + size + ']" /></td>\
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="boxes_max_weight[' + size + ']" /></td>\
							<td><input type="checkbox" name="boxes_is_letter[' + size + ']" /></td>\
						</tr>';

            $tbody.append(code);

            return false;
        });

        usps_boxes.find('.remove').click(function () {
            var $tbody = usps_boxes.find('tbody');

            $tbody.find('.check-column input:checked').each(function () {
                jQuery(this).closest('tr').hide().find('input').val('');
            });

            return false;
        });

        // Ordering
        jQuery('.usps_services tbody').sortable({
            items: 'tr',
            cursor: 'move',
            axis: 'y',
            handle: '.sort',
            scrollSensitivity: 40,
            forcePlaceholderSize: true,
            helper: 'clone',
            opacity: 0.65,
            placeholder: 'wc-metabox-sortable-placeholder',
            start: function (event, ui) {
                ui.item.css('background-color', '#f6f6f6');
            },
            stop: function (event, ui) {
                ui.item.removeAttr('style');
                usps_services_row_indexes();
            }
        });

        function usps_services_row_indexes() {
            jQuery('.usps_services tbody tr').each(function (index, el) {
                jQuery('input.order', el).val(parseInt(jQuery(el).index('.usps_services tr')));
            });
        }

        jQuery('#woocommerce_usps_shippingrates').change(function () {
            if ('ALL' === jQuery(this).val()) {
                jQuery('[data-service_code="D_PARCEL_SELECT"]').hide();
                jQuery('.sub_services li.commercial').hide();
            } else {
                jQuery('[data-service_code="D_PARCEL_SELECT"]').show();
                jQuery('.sub_services li.commercial').show();
            }
        }).change();
    }

    if (usps_settings.is_usps_settings_page) {
        $('#woocommerce_usps_enable_flat_rate_box_weights').change(function () {

            if ($(this).is(':checked')) {
                $('#flat_rate_box_weights').show();
            } else {
                $('#flat_rate_box_weights').hide();
            }

        }).change();

        $('.flat_rate_box_weights .empty-weight').on('change', function () {
            var weight = $(this).val(),
                duplicate_size_str = $(this).data('duplicate_sizes'),
                duplicate_sizes = duplicate_size_str.split('|');

            $.each(duplicate_sizes, function (index, value) {
                $('input[name="flat_rate_box_weights[' + value + ']"]').val(weight);
            });

        }).change();

        // Migration notice dismiss handler.
        $('.wc-usps-migration-notice').on('click', '.notice-dismiss', function () {
            $.post(ajaxurl, {
                action: 'wc_usps_dismiss_migration_notice',
                nonce: usps_settings.migration_notice_nonce
            });
        });
    }
})(jQuery, usps_settings);
