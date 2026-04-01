<a href="#" role="button" class="btn" onclick="check_net_balance()" style="display: block; margin: 0 auto; max-width: 250px;">View Net Balances</a>
<div id="net-balances" class="modal" aria-hidden="true">
    <div class="bg" tabindex="-1" data-micromodal-close>
        <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="net-balances-title">
            <header>
                <h2 id="net-balances-title" class="h4">Net Balances</h2>
                <button class="close" aria-label="Close modal" data-micromodal-close onclick="reset_net_balance()"></button>
            </header>
            <div id="net-balances-content">
                <div id="net-balances-init">
                    <p>Checking balances of your Net accounts, please wait...</p>
                    <img id="save_address_loader" style="width: 32px; position: relative; top: 5px; display: block; margin: 0 auto;" src="<?php echo get_template_directory_uri(); ?>/app/assets/img/loader.gif" alt="Loading"/>
                </div>
                <div id="net-balances-data" aria-live="polite" aria-busy="true"></div>
                <div id="net-balances-error" style="display: none;">
                    <p>We're sorry, there was an error getting your Net balances. Please contact support@aph.org for assistance.</p>
                </div>
            </div>
        </div>
    </div>
</div>
