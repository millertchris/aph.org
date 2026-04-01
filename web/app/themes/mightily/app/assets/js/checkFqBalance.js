/*!
 * dosomething
 * Fiercely quick and opinionated front-ends
 * https://HosseinKarami.github.io/fastshell
 * @author Hossein Karami
 * @version 1.0.5
 * Copyright 2023. MIT licensed.
 */
function reset_fq_balance(){
    jQuery('#fq-balances-init').show();
    jQuery('#fq-balances-error').hide();
    jQuery('#fq-balances-data').empty();
    jQuery('#fq-balances-data').attr('aria-busy', 'true');
}
function check_fq_balance(event) {
    event = event || window.event;
    event.preventDefault();

    MicroModal.show('fq-balances', {
        onClose: reset_fq_balance()
    });

    var FqLabels = {
        'Error': 'Error: ',
        'FqAccountID': 'FQ Account ID: ',
        'fq_account_balance': 'FQ Account Balance: ',
        'fq_available_funding': 'FQ Available Funding: ',
        'fq_outstanding_balance': 'FQ Outstanding Balance: ',
        'fq_overspent': 'FQ Overspent: '
    };

    jQuery.ajax({
        url: fq_obj.ajaxurl, // or fq_obj.ajaxurl if using on frontend
        data: {
            'action': 'check_fq_balance',
            'security': fq_obj.fq_ajax_nonce
        },
        success: function (data) {
            if(data == 'FALSE'){
                jQuery('#fq-balances-error').show();
            } else {
                jQuery('#fq-balances-data').attr('aria-busy', 'false');
                jQuery.each(JSON.parse(data), function( indexa, valuea ) {
                    var dataHtml = '';
                    var fQformatted = parseInt(valuea['FqAccountID']);
                    if(fQformatted < 1000){
                        fQformatted = String(fQformatted).padStart(3, '0');
                    }
                    if(valuea['Error'] == ''){
                        dataHtml += '<div class="fq-accounts">';
                        dataHtml += '<h3 class="h6" style="margin: 0px 0px 15px 0px;">FQ ' + fQformatted + '</h3>';
						dataHtml += '<span>Current Fiscal Year Allocation: $' + valuea['fq_yearAllocations'].toLocaleString("en", { minimumFractionDigits: 2 }) + '</span><br />';
                       // dataHtml += '<span>Completed Order Balance: $' + valuea['fq_account_balance'].toLocaleString("en", { minimumFractionDigits: 2 }) + '</span><br />';
                       // dataHtml += '<span>Outstanding Order Balance: $' + valuea['fq_outstanding_balance'].toLocaleString("en", { minimumFractionDigits: 2 }) + '</span><br />';
                        dataHtml += '<strong>Current Available Funding:</strong><br />';
                        dataHtml += '<span class="h4">$' + valuea['fq_available_funding'].toLocaleString("en", { minimumFractionDigits: 2 }) + '</span><br />';
                        dataHtml += '<span>Overspent towards next fiscal year: $' + valuea['fq_overspent'].toLocaleString("en", { minimumFractionDigits: 2 }) + '</span><br />';
                        dataHtml += '</div>';



                        // jQuery.each(valuea, function( indexb, valueb ) {
                        //     console.log(valuea);
                        //     if(indexb != 'Error'){
                        //         if(indexb == 'FqAccountID'){
                        //             dataHtml += '<h3 class="h6">FQ ' + valueb + '</h3>';
                        //         } else {
                        //             dataHtml += '<span>' + FqLabels[indexb] + valueb + '</span><br />';
                        //         }
                        //     }
                        // });
                    } else {
                        dataHtml += '<div class="fq-accounts">';
                        dataHtml += '<h3 class="h6" style="margin: 0px 0px 15px 0px;">FQ ' + fQformatted + '</h3>';
                        dataHtml += '<span>' + FqLabels['Error'] + 'We could not fetch data for this account.</span>';
                        dataHtml += '</div>';
                    }
                    jQuery('#fq-balances-data').append(dataHtml);
                });
            }
        },
        error: function (errorThrown) {
            jQuery('#fq-balances-error').show();
        },
        complete: function(){
            jQuery('#fq-balances-init').hide();
        }
    });

}