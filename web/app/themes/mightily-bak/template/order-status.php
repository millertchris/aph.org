<?php
// Template name: Order Status

get_header(); ?>

<div class="interior-page">
	<div class="layout order-status">
		<div class="wrapper">
			<div class="row">
                <h1>Check order status</h1>
                <form id="order-status-form" class="order-status-form">
                    <div class="order-status-field order-status-field-email">
                        <label>
                            <span class="label-text">Email Address</span>
                            <input id="order-status-field-email-input" type="email" value="" placeholder="e.g., janedoe@myemail.com"/>
                        </label>                        
                    </div>                    
                    <div class="order-status-field order-status-field-number">
                        <label>
                            <span class="label-text">Order Number</span>
                            <input id="order-status-field-number-input" type="text" value="" placeholder="e.g., 123321"/>
                        </label>
                    </div>
                    <div class="order-status-field">
                        <button class="order-status-submit" onclick="">Submit</button>
                    </div>
                </form>
                <div id="order-status-result"></div>
                <div id="order-status-notice">
                    <p>If you have questions, you can contact the APH Customer Service team at <a href="mailto:cs@aph.org" title="Email APH Customer Service">cs@aph.org</a>, or 1-800-223-1839. Customer Service hours will continue as M-F 8:00 a.m. – 8:00 p.m. Eastern Standard Time.</p>
                </div>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>
