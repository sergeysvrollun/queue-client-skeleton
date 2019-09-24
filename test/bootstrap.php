<?php

use Psr\Container\ContainerInterface;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';
/** @var ContainerInterface $container */
$container = require 'config/container.php';
