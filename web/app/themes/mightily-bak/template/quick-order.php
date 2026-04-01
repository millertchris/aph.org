<?php

// Template name: Quick Order
get_header();


?>

<div class="interior-page">

    <section id="cn-quick-order" class="layout basic-content">
        <div class="content">
            <div class="wrapper">
                <div class="col">
                    <h1>Quick Order</h1>
                    <p>To Quick Order, search by Catalog Number, Product Name or key word and then select the Add to List button. Select the Add to Cart button once you are finished shopping.</p>
                    <p><strong>Note:</strong> Product eligibility for Federal Quota funds and Backorder eligibility will be determined once you add your shipping items to the cart. Products will be removed from the cart if they do not meet the eligibility requirements.</p>
                    <form id="cn-quick-order-form" class="form-horizontal" action="/add-to-cart-cn">
                        <div id="items" class="form-group">
                            <div class="form-row">
                                <div class="form-col product-cn-col">
                                    <label>
                                        Catalog Number
                                        <input class="product-cn-input" name="product-cn" type="text" placeholder="Enter catalog number here">
                                    </label>
                                </div>
                                <div class="form-col product-qty-col">
                                    <label>
                                        Quantity
                                        <input class="product-qty-input" name="product-qty" type="number" value="1">
                                    </label>
                                </div>
                                <div class="form-col product-remove" style="display:none;">
                                    <button class="delete btn">Remove Row</button>
                                </div>
                            </div>
                        </div>
                        <input id="add-to-cart-cn-hidden" type="hidden" value="" name="cn"/>
                        <input id="add-to-cart-qty-hidden" type="hidden" value="" name="qty"/>
                    </form>
                    <button id="add" class="btn add-more" type="button">Add row</button> <button id="cn-add-to-cart" class="btn">Add to Cart</button>
                </div>
            </div>
        </div>
    </section>

</div>
<script>
    jQuery(document).ready(function() {
        var rowTemplate = jQuery('#cn-quick-order .form-row').first().clone();
        
        //when the Add Field button is clicked
        jQuery("#add").click(function(e) {
            jQuery(".delete").fadeIn("1500");
            //Append a new row of code to the "#items" div
            var rowTemplate = jQuery('#cn-quick-order .form-row').first().clone();
            rowTemplate.find('.product-cn-input').val('');
            rowTemplate.find('.product-qty-input').val('1');
            rowTemplate.find('.product-remove').show();
            rowTemplate.appendTo("#cn-quick-order #items");
            // Focus on last row input.
            jQuery('#cn-quick-order .form-row').last().find('.product-cn-input').focus();
        });
        jQuery("body").on("click", ".delete", function(e) {
            jQuery(this).parents('.form-row').remove();
        });
        jQuery("#cn-add-to-cart").click(function(e){
            e.preventDefault();
            jQuery('#add-to-cart-cn-hidden').val(function(){
                var cn_string = '';
                jQuery('#cn-quick-order .product-cn-input').each(function(){
                    cn_string += ',' + jQuery(this).val();
                });
                return cn_string.substr(1);
            });
            jQuery('#add-to-cart-qty-hidden').val(function(){
                var qty_string = '';
                jQuery('#cn-quick-order .product-qty-input').each(function(){
                    qty_string += ',' + jQuery(this).val();
                });
                return qty_string.substr(1);
            });            
            jQuery("#cn-quick-order-form").submit();
        });
    });
</script>
<?php get_footer(); ?>
