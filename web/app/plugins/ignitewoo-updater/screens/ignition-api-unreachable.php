<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php
echo '<div style="border-left: 4px solid #dc3232;margin: 15px 0 15px;background:#ffffff;padding: 1px 12px;">' . wpautop( sprintf( __( 'There seems to be an error reaching the IgniteWoo API at this time. Please try again later. Should this error persist, please %submit a support request%s on our web site.', 'ignition-updater' ), '<a href="' . esc_url( 'https://ignitewoo.com/contact-us/?utm_source=helper' ) . '" target="_blank">', '</a>' ) ) . '</div>' . "\n";
?>
