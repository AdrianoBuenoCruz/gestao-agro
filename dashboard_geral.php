<?php
require_once __DIR__ . '/app/bootstrap.php';
(new App\Controllers\ResourceController($pdo))->dispatch('dashboard');

