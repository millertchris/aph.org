<?php

/**
 * Shows how to display the balance as well as teh users.
 *
 * 1. get the data structure - a list of all accounts and associated users.
 * 2. Display the list
 */


$FQs = \APH\FQ::getQuotaChartData();
// print "<pre>" . json_encode($FQs, JSON_PRETTY_PRINT) . "</pre>"; // Export data structure.

?>
<h2>FQ Quota Accounts and Users</h2>
    <div class="order-history">
        <div class="layout list-of-items my-orders list-view">
            <div class="wrapper">
            <h1 class="h2">Quota Users</h1>
            <?php foreach ($FQs as $fq_id => $fq) { ?>
                <a name="users-<?php echo $fq->term_id ?>"></a>
                <h2><?php echo $fq->name ?></h2>
                <pre>

<?php // print_r($fq);
print "
                    \$balance = $fq->balance;
                    \$outstanding = $fq->outstanding; 
                    \$available = $fq->available;
                    \$overspent = $fq->overspent; 
                    
"; ?>
                </pre>


	            <?php foreach ($fq->members as $roleName => $users)
	                if (count($users) > 0)
                    { ?>
                    <h3><?php echo "$fq->name $roleName"; ?></h3>
<?php /*
                    <div class="layout-options">
                        <a class="layout-button list-view btn active" href="#" data-view="list">List View</a>
                        <a class="layout-button btn" href="#" data-view="grid">Grid View</a>
                    </div>
 */ ?>
                    <div class="line-items">
                        <?php

                        $i = 1;
                        foreach ($users as $display_user) {

                            $display_name = $display_user->display_name;
                            $company = get_user_meta( $display_user->ID, 'billing_company', true );
                            $email = $display_user->user_email;
                            $phone = get_user_meta( $display_user->ID, 'billing_phone', true );

                            ?>
                            <div id="order-<?php echo $i; ?>" class="item">
                                <div class="item-detail"><p>Name: <span
                                                class="item-span"><?php echo $display_name ?></span></p></div>
                                <div class="item-detail"><p>Company: <span
                                                class="item-span"><?php echo $company ?></span></p></div>
                                <div class="item-detail"><p>Email: <span
                                                class="item-span"><?php echo $email ?></span></p></div>
                                <div class="item-detail"><p>Phone: <span
                                                class="item-span"><?php echo $phone ?></span></p></div>
                            </div>
                            <?php
                            $i ++;
                        } // users for role ?>
                    </div>

            <?php
                } // roles
            } // FQs
            ?>

            </div>

        </div>
</div>
