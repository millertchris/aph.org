// Import SCSS entry file so that webpack picks up changes
import './index.scss';

import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
 
const addProductGroup = ( filters ) => {
    return [
        {
            label: __( 'Product Group', 'woocommerce-aph-reports' ),
            staticParams: [],
            param: 'product_group',
            showFilters: () => true,
            defaultValue: 'default',
            filters: [ ...( wcSettings.productGroups || [] ) ],
        },
        ...filters,
    ];
};
 
addFilter(
    'woocommerce_admin_orders_report_filters',
    'woocommerce-aph-reports',
    addProductGroup
);