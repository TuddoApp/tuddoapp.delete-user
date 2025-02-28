<?php

date_default_timezone_set('UTC');

$app = require __DIR__ . '/bootstrap.php';

// Load route files
require __DIR__ . '/routes/index.php';
require __DIR__ . '/routes/request-delete.php';
require __DIR__ . '/routes/delete-account.php';

$app->run();