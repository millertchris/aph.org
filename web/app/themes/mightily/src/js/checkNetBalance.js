function reset_net_balance(){
    jQuery('#net-balances-init').show();
    jQuery('#net-balances-error').hide();
    jQuery('#net-balances-data').empty();
    jQuery('#net-balances-data').attr('aria-busy', 'true');
}
function check_net_balance(event) {
    event = event || window.event;
    event.preventDefault();

    MicroModal.show('net-balances', {
        onClose: reset_net_balance()
    });

    var netLabels = {
        'Error': 'Error: ',
        'NetAccountID': 'Net Account ID: ',
        'net_account_balance': 'Net Account Balance: ',
        'net_available_funding': 'Net Available Funding: ',
        'net_outstanding_balance': 'Net Outstanding Balance: ',
        'net_overspent': 'Net Overspent: '
    };

    jQuery.ajax({
        url: net_obj.ajaxurl, // or net_obj.ajaxurl if using on frontend
        data: {
            'action': 'check_net_balance',
            'security': net_obj.net_ajax_nonce
        },
        success: function (data) {
            if(!data || data == 'FALSE' || data == '' || $data === NULL){
                jQuery('#net-balances-error').show();
            } else {
                jQuery('#net-balances-data').attr('aria-busy', 'false');
                jQuery.each(JSON.parse(data), function( indexa, valuea ) {
                    var dataHtml = '';
                    var netFormatted = parseInt(valuea['NetAccountID']);
                    if(netFormatted < 1000){
                        netFormatted = String(netFormatted).padStart(3, '0');
                    }
                    if(valuea['Error'] == ''){
                        dataHtml += '<div class="net-accounts">';
                        dataHtml += '<h3 class="h6" style="margin: 0px 0px 15px 0px;">Net ' + netFormatted + '</h3>';
                        dataHtml += '<span>Account Balance: $' + valuea['net_account_balance'] + '</span><br />';
                        dataHtml += '<span>Outstanding Order Balance: $' + valuea['net_outstanding_balance'] + '</span><br />';
                        dataHtml += '<strong>Current Available Funding:</strong><br />';
                        dataHtml += '<span class="h4">$' + valuea['net_available_funding'] + '</span><br />';
                        dataHtml += '<span>Overspent towards next fiscal year: $' + valuea['net_overspent'] + '</span>';
                        dataHtml += '</div>';
                    } else {
                        dataHtml += '<div class="net-accounts">';
                        dataHtml += '<h3 class="h6" style="margin: 0px 0px 15px 0px;">Net ' + netFormatted + '</h3>';
                        dataHtml += '<span>' + netLabels['Error'] + 'We could not fetch data for this account.</span>';
                        dataHtml += '</div>';
                    }
                    jQuery('#net-balances-data').append(dataHtml);
                });
            }
        },
        error: function (errorThrown) {
            jQuery('#net-balances-error').show();
        },
        complete: function(){
            jQuery('#net-balances-init').hide();
        }
    });

}