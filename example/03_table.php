<?php

include_once __DIR__.'/../include.php';

$data = [
    [
        'firstname' => 'Mollie',
        'surname' => 'Alvarez',
        'email' => 'molliealvarez@example.com',
    ],
    [
        'firstname' => 'Dianna',
        'surname' => 'Mcbride',
        'age' => 43,
        'email' => 'diannamcbride@example.com',
    ],
    [
        'firstname' => 'Bowen',
        'surname' => 'Kelley',
        'age' => 50,
        'email' => 'bowenkelley@example.com',
    ],
];


Shell::message()->textTable($data);