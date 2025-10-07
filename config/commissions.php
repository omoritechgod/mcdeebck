<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Platform Commission Fees
    |--------------------------------------------------------------------------
    |
    | Define the percentage cut (as a number, not decimal) for each service
    | category. This allows us to centrally manage all commission rules.
    |
    */

    'ride_hailing' => 5,       // 5% of trip fare
    'food_delivery' => 5,      // 5% of order value
    'food' => 10,              // 10% of food order value (using 'food' key for consistency)
    'ecommerce' => 5,          // 5% of product price
    'service_apartments' => 10, // 10% of booking value
    'auto_maintenance' => 5,   // 5% of service cost
    'general_services' => 5,   // 5% of service cost

];
