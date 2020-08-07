<?php

return [
    // default cart instance
    "defaultInstance" => 'default',

    // tax percentage
    "tax" => 0,

    // default shopping cart session name
    'session_name' => 'shoppingcart_session',

    // database tables addon
    "addon" => "_shoppingcart",

    // optional 2 cols
    // set to null, if not needed
    "opt1" => "size",
    "opt2" => "color",

    // casts
    'casts' => [
        'opt1' => 'int',
        'opt2' => 'int'
    ],
];