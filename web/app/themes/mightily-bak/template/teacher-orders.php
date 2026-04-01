<?php
// Template name: Teacher Orders
get_header();

$teacher_orders = \APH\OrderHistory::get_teacher_orders();
?>
<div class="interior-page">

    <div class="profile">

        <?php if ($teacher_orders) { ?>
            <div class="order-history">
                <?php
                // Change button text for Role - Teacher
                $orderText = 'Order';
                ?>
                <div class="layout list-of-items teacher-orders list-view">
                    <div class="wrapper">
                        <a class="btn back-to-profile" href="/profile"><i class="fa fa-chevron-left"></i> Back to Profile</a>
                        <h1 class="h2">Teacher Orders</h1>
                        <p style="margin-bottom: 30px;">Certain purchases such as braille and large print books, device repairs, and digital downloads may not appear on your order dashboard. Please contact Customer Service for assistance with these purchases.</p>
                        <!-- <div class="layout-options">
                            <a class="layout-button list-view btn active" href="#" data-view="list">List View</a>
                            <a class="layout-button btn grid-view" href="#" data-view="grid">Grid View</a>

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
                            usort($teacher_orders, function($a, $b) {
                                return strcmp($b->get_date_created(), $a->get_date_created());
                            });
                            foreach ($teacher_orders as $order):
                                $x = $i + 1;
                                $quota = $order->get_meta('_fq_account_name');
                                if (empty($quota))
                                    $quota = 'none';
                                ?>
                                <li id="order-<?php echo $i; ?>" class="item">
                                    <div class="item-detail"><p>Quota Acct: <span class="item-span"><?php echo $quota; ?></span></p></div>
                                    <div class="item-number medium">
                                        <h1 class="h3 label item-name" tabindex="0"><?php echo $orderText; ?> Number: <?php echo $order->get_id(); ?></h1>
                                        <?php if ($order->get_status() == 'pending') : ?>
                                            <?php $params = array('wc_order_id' => base64_encode($order->get_id()), 'wc_order_method' => 'approve'); ?>
                                            <div class="order-links">
                                                <a class="edit-item order-edit" href="<?php echo esc_url($order->get_view_order_url()); ?>"><i class="far fa-eye" aria-hidden="true"></i> View order</a><span aria-hidden="true"> | </span>
                                                <a class="edit-item order-edit" href="<?php echo $order->get_edit_order_url(); ?>" onclick="window.open(this.href, 'mywin',
                                                                           'left=20,top=20,width=1024,height=640,toolbar=1,resizable=0');
                                                                   return false;"><i class="fas fa-edit" aria-hidden="true"></i> Edit Order</a>
                                            </div>
                                        <?php else : ?>
                                            <a class="edit-item order-edit" href="<?php echo esc_url($order->get_view_order_url()); ?>"><i class="far fa-eye" aria-hidden="true"></i> View order</a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-detail"><p>Date: <span class="item-span"><?php echo $order->get_date_created()->format('m/d/Y'); ?></span></p></div>
                                    <div class="item-detail"><p>PO number: <span class="item-span"><?php echo $order->get_meta('PO Number'); ?></span></p></div>
                                    <?php
                                    $user_info = get_userdata($order->get_customer_id());
                                    ?>
                                    <div class="item-detail"><p>Name: <span class="item-span"><?php echo $user_info->first_name; ?> <?php echo $user_info->last_name; ?></span></p></div>
                                    <div class="item-detail"><p>Status: <span class="item-span"><?php echo wc_get_order_status_name($order->get_status()); ?></span></p></div>
                                    <div class="item-detail"><p><?php echo $orderText; ?> Total: <span class="item-span total"><?php echo $order->get_formatted_order_total(); ?></span></p></div>
                                    <div class="item-number small">
                                        <?php if ($order->get_status() == 'pending') : ?>
                                            <?php $params = array('wc_order_id' => base64_encode($order->get_id()), 'wc_order_method' => 'approve'); ?>
                                            <div class="order-links">
                                                <a class="edit-item order-edit" href="<?php echo esc_url($order->get_view_order_url()); ?>"><i class="far fa-eye" aria-hidden="true"></i> View order</a><span>|</span>
                                                <a class="edit-item order-edit" href="<?php echo $order->get_edit_order_url(); ?>" onclick="window.open(this.href, 'mywin',
                                                                           'left=20,top=20,width=1024,height=640,toolbar=1,resizable=0');
                                                                   return false;"><i class="fas fa-edit" aria-hidden="true"></i> Edit Order</a>
                                            </div>
                                        <?php else : ?>
                                            <a class="edit-item order-edit" href="<?php echo esc_url($order->get_view_order_url()); ?>"><i class="far fa-eye" aria-hidden="true"></i> View order</a>
                                        <?php endif; ?>
                                    </div>
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
