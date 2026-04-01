<?php

// =========================================================================
// FIX META DATA ATTACHED TO SHIPMENT PACKAGES AND ITEMS THEREIN
// =========================================================================
function update_shipment_package_data($order) {

    // Create an array of packages and the items inside it. This is generated in the cart.
    $product_bundles = [];
    $x = 0;
    $y = 0;
    foreach (WC()->cart->get_shipping_packages() as $package) {
        foreach ($package['contents'] as $item_id => $values) {
            $product_bundles[$x][$item_id] = $values['data']->get_name() . ' &times; ' . $values['quantity'];
        }
        $x++;
    }

    // Loop through the shipping items. They should match up to the packages from the cart.
    foreach ($order->get_items('shipping') as $shippingitem) {
        // Get all meta data, loop through and find keys that start with "Package". Remove them.
        foreach ($shippingitem->get_meta_data() as $shippingitemmeta) {
            $shippingitemmetadata = $shippingitemmeta->get_data();
            $comparekey = substr($shippingitemmetadata['key'], 0, 7);
            if ($comparekey == 'Package') {
                $shippingitem->delete_meta_data($shippingitemmetadata['key']);
            }
        }

        // If there is no 'Items' meta, we need to set it.
        if (!$shippingitem->get_meta('Items')) {
            $shippingitem->add_meta_data('Items', implode(', ', $product_bundles[$y]), true);
        }

        $y++;
    }

    $order->save();
}
