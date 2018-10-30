<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
 
        "db" => [
            "host" => "localhost",
            "dbname" => "foremanbroker",
            "user" => "root",
            "pass" => "foreman"
        ],
        "foreman" => [
             "host" => "foreman",
             "user" => "admin",
             "pass" => "admin"
        ],
    ],
];
