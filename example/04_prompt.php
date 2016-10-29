<?php
include_once __DIR__.'/../include.php';
$name=Shell::message()->prompt('Name?');

echo "\nName:`$name`\n";