<?php

/*
 * Show all the people related to a specific role for an FQ Account
 *
 * First we create the data structure we need, then we render it.
 *
 */

$FQs = array();

if (\APH\Flags::$ENABLE_QUOTA_USERS) {
    // Output different data charts depending on the user role.
    $eot = \APH\Roles::EOT;
    $ooa = \APH\Roles::OOA;
    if(is_user_role('teacher')) {
        $accounts = \APH\FQ::getAccountsForUser($current_user->ID);
        $FQs = \APH\FQ::getQuotaChartData($accounts, array($eot, $ooa));
        // print "<pre>" . json_encode($FQs, JSON_PRETTY_PRINT) . "</pre>"; // Export data structure.
    } else {
        $accounts = \APH\FQ::getAccountsForUser($current_user->ID);
        $FQs = \APH\FQ::getQuotaChartData($accounts);
        // print "<pre>" . json_encode($FQs, JSON_PRETTY_PRINT) . "</pre>"; // Export data structure.
    }
	
}

if (! empty($FQs)) : ?>
    <div class="order-history quota-users">
        <div class="layout list-of-items my-orders list-view">
            <div class="wrapper">
                <h1 class="h2">Quota Users</h1>

                <?php foreach ($FQs as $fq_id => $fq) : ?>

                    <div class="fq-user-custom-wrap">

                        <a class="jumptarget" id="users-<?php echo $fq->term_id ?>" name="users-<?php echo $fq->term_id ?>"></a>
                        <h2 class="h4 fq-name"><?php echo $fq->name ?></h2>
                        <div class="layout-options">
                            <a class="layout-button list-view btn active" href="#" data-view="list">List View</a>
                            <a class="layout-button btn" href="#" data-view="grid">Grid View</a>
                        </div>
                        <div class="line-items">
                            <?php foreach ($fq->members as $roleName => $users) : ?>
                                <?php $i = 1; foreach ($users as $user) : ?>
                                    <?php
                                        $x = $i + 1;
                                        $user_role = $roleName;
                                        $display_name = $user->display_name;
                                        $company = get_user_meta( $user->ID, 'billing_company', true );
                                        $email = $user->user_email;
                                        $phone = get_user_meta( $user->ID, 'billing_phone', true );
                                    ?>
                                    <div id="order-<?php echo $i; ?>" class="item">
                                        <div class="item-detail"><p>User Role: <span class="item-span"><?php echo $user_role; ?></span></p></div>
                                        <div class="item-detail"><p>Name: <span class="item-span"><?php echo $display_name; ?></span></p></div>
                                        <div class="item-detail"><p>Company: <span class="item-span"><?php echo $company; ?></span></p></div>
                                        <div class="item-detail"><p>Email: <span class="item-span"><?php echo $email; ?></span></p></div>
                                        <div class="item-detail"><p>Phone: <span class="item-span"><?php echo $phone; ?></span></p></div>
                                    </div>
                                    <?php $i ++; endforeach; // users for role ?>
                            <?php endforeach; ?>
                        </div>
                    
                    </div>
                
                <?php endforeach; // FQs ?>

            </div>
        </div>
    </div>
<?php endif; //FQ Account List ?>