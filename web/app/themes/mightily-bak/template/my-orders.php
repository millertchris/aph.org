<?php
// Template name: My Orders
get_header();

$my_orders = \APH\OrderHistory::get_my_orders();
?>
<div class="interior-page">

    <div class="profile">

        <?php if ($my_orders) { ?>

            <div class="order-history">
                <?php
                // Change button text for Role - Teacher
                $userRole = get_current_user_role();
                $orderText = '';
                // echo $userRole;
                if ($userRole === 'role-teacher') {
                    $orderText = 'Request';
                } else {
                    $orderText = 'Order';
                }
                ?>
                <div class="layout list-of-items my-orders list-view">
                    <div class="wrapper">
                        <a class="btn back-to-profile" href="/profile"><i class="fa fa-chevron-left"></i> Back to Profile</a>
                        <h1 class="h2">My <?php echo $orderText; ?>s</h1>
                        <p style="margin-bottom: 30px;">Certain purchases such as braille and large print books, device repairs, and digital downloads may not appear on your order dashboard. Please contact Customer Service for assistance with these purchases.</p>
                        <!-- <div class="layout-options">
                            <a class="layout-button list-view btn active" href="#" data-view="list">List View</a>
                            <a class="layout-button btn" href="#" data-view="grid">Grid View</a>
                        </div> -->
                        <form class="filter active filter-search" role="search" _lpchecked="1">
                            <div class="input-wrapper text-input-field">
                                <!-- Loop through categories to create a select list to filter with -->
                                <!-- <select class="select-filter" name="">
                                    <option value="test">Test</option>
                                </select> -->
                                <label class="filter-label" for="text-filter">Start your search</label>
                                <input type="text" class="text-filter" id="text-filter" name="text-filter" value="" placeholder="Start your Search">
                            </div>
                            <input class="text-filter-button" type="submit" value="Search">
                        </form>                        
                        <ul class="line-items">
                            <?php
                            $i = 1;
                            foreach ($my_orders as $order):
                                $x = $i + 1;

                                $quota = $order->get_meta('_fq_account_name');
                                if (empty($quota))
                                    $quota = 'none';
                                ?>
                                <li id="order-<?php echo $i; ?>" class="item">
                                    <div class="item-detail fq-account">
                                        <h1 class="item-name h3 label" tabindex="0">Quota Acct: <span class="item-span"> <?php echo $quota; ?></span></h1>

                                    </div>
                                    <div class="item-number medium">
                                        <a href="<?php echo esc_url($order->get_view_order_url()); ?>"><h2 class="item-name h3 label" tabindex="0">View <?php echo $orderText; ?> Number: <?php echo $order->get_id(); ?></h2></a>

                                        <!-- <a href="<?php // echo esc_url($order->get_view_order_url()); ?>" class="edit-item"><i class="fas fa-eye" aria-hidden="true"></i> View order</a> -->
                                    </div>
                                    <div class="item-detail"><p>Date: <span class="item-span"><?php echo $order->get_date_created()->format('m/d/Y'); ?></span></p></div>
                                    <div class="item-detail"><p>PO number: <span class="item-span"><?php echo $order->get_meta('PO Number'); ?></span></p></div>
                                    <div class="item-detail"><p>Status: <span class="item-span"><?php echo wc_get_order_status_name($order->get_status()); ?></span></p></div>
                                    <div class="item-detail"><p><?php echo $orderText; ?> Total: <span class="item-span total"><?php echo $order->get_formatted_order_total(); ?></span></p></div>
                                    <div class="item-detail"><p>Track Shipment: <span class="item-span">none</span></p></div>
                                    <!-- <div class="item-number small">
                                        <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="edit-item"><i class="fas fa-eye" aria-hidden="true"></i> View order</a>
                                    </div> -->
                                </li>
                                <?php
                                $i++;
                            endforeach;
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div class="wrapper">
                <div class="row">
                    <div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">		
                        No order has been made yet.	
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<?php
get_footer();
?>
