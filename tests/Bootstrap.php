<?php

use DOMPDFModuleTest\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\Loader\StandardAutoloader;

error_reporting(E_ALL | E_STRICT);

chdir(__DIR__);

$previousDir = '.';
while (!file_exists('vendor/autoload.php')) {
    $dir = dirname(getcwd());
    if ($previousDir === $dir) {
        throw new RuntimeException(
                'Unable to locate "vendor/autoload.php"'
        );
    }
    $previousDir = $dir;
    chdir($dir);
}

if (is_readable(__DIR__ . '/TestConfiguration.php')) {
    $configuration = include_once __DIR__ . '/TestConfiguration.php';
} else {
    $configuration = include_once __DIR__ . '/TestConfiguration.php.dist';
}

// Assumes PHP Composer autoloader w/compiled classmaps, etc.
require_once 'vendor/autoload.php';

$serviceManager = new ServiceManager(new ServiceManagerConfig($configuration['service_manager']));
$serviceManager->setService('ApplicationConfig', $configuration);
$serviceManager->setAllowOverride(true);

$moduleManager = $serviceManager->get('ModuleManager');
$moduleManager->loadModules();

TestCase::setServiceManager($serviceManager);
