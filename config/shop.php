<?php

return [

    'name' => env('SHOP_NAME', 'PACH WOMEN'),
    'tagline' => 'Women’s clothing, made to be worn every day.',

    'currency_symbol' => '₹',

    // Flat shipping fee, waived when the cart subtotal reaches the threshold.
    'shipping_fee' => (float) env('SHOP_SHIPPING_FEE', 80),
    'free_shipping_over' => (float) env('SHOP_FREE_SHIPPING_OVER', 1499),

    'cod_enabled' => (bool) env('SHOP_COD_ENABLED', true),

    // Messages in the black bar at the top of every page.
    'announcements' => [
        'Free shipping above {free_shipping_over}',
        'Cash on delivery available',
        'Shipping all over India',
    ],

    // Seconds each home slider banner stays on screen.
    'hero_interval' => 5.5,

    'contact' => [
        'phone' => env('SHOP_PHONE', '+91 81578 88128'),
        'whatsapp' => env('SHOP_WHATSAPP', '918157888128'),
        'email' => env('SHOP_EMAIL', 'hello@pachwomen.com'),
        'instagram' => 'https://www.instagram.com/pach_women/',
        'instagram_uae' => 'https://www.instagram.com/pach_women_uae/',
        'store' => 'Koshak, 1st floor, Marami Centre, Kakkamvelli, Nadapuram, Kerala',
        'store_map' => 'https://maps.app.goo.gl/P3JAHtxBcrEc4bme6',
    ],

    'razorpay' => [
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    'states' => [
        'Andaman and Nicobar Islands', 'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar',
        'Chandigarh', 'Chhattisgarh', 'Dadra and Nagar Haveli and Daman and Diu', 'Delhi', 'Goa',
        'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jammu and Kashmir', 'Jharkhand', 'Karnataka',
        'Kerala', 'Ladakh', 'Lakshadweep', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya',
        'Mizoram', 'Nagaland', 'Odisha', 'Puducherry', 'Punjab', 'Rajasthan', 'Sikkim',
        'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
    ],

];
