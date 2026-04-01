<a href="#" role="button" class="btn" onclick="check_fq_balance()" style="display: block; margin: 0 auto; max-width: 250px;">View FQ Balances</a>
<div id="fq-balances" class="modal" aria-hidden="true">
    <div class="bg" tabindex="-1" data-micromodal-close>
        <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="fq-balances-title">
            <header>
                <h2 id="fq-balances-title" class="h4">FQ Balances</h2>
                <button class="close" aria-label="Close modal" data-micromodal-close onclick="reset_fq_balance()"></button>
            </header>
            <div id="fq-balances-content">
                <div id="fq-balances-init">
                    <p>Checking balances of your FQ accounts, please wait...</p>
                    <img id="save_address_loader" style="width: 32px; position: relative; top: 5px; display: block; margin: 0 auto;" src="<?php echo get_template_directory_uri(); ?>/app/assets/img/loader.gif" alt="Loading"/>
                </div>
                <div id="fq-balances-data" aria-live="polite" aria-busy="true"></div>
                <div id="fq-balances-error" style="display: none;">
                    <p>We're sorry, there was an error getting your FQ balances. Please contact support@aph.org for assistance.</p>
                </div>
            </div>
        </div>
    </div>
</div>
