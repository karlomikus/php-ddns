<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Console\Application;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

$application = new Application();
$application->add(new Kami\UpdateARecord());
$application->run();
