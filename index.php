<?php
require_once 'vendor/autoload.php'; // Assumes usage of Composer for autoloading

use Pslits\OaiPmh\Database;
use Pslits\OaiPmh\OAIController;
use Pslits\OaiPmh\OAIView;
use Pslits\OaiPmh\DublinCorePlugin;

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    // Log error and return a response
    error_log($e->getMessage());
    http_response_code(500);
    echo "Environment configuration error";
    exit;
}

// Set up error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', getenv('DISPLAY_ERRORS'));

$dsn = $_ENV['DATABASE_DSN'];
$username = $_ENV['DATABASE_USERNAME'];
$password = $_ENV['DATABASE_PASSWORD'];

// Instantiate the required components
try {
    $database = new Database([
        'dsn' => $_ENV['DATABASE_DSN'],
        'username' => $_ENV['DATABASE_USERNAME'],
        'password' => $_ENV['DATABASE_PASSWORD'],
    ]);
} catch (PDOException $e) {
    // Log error and return a response
    error_log($e->getMessage());
    http_response_code(500);
    echo "Database connection error";
    exit;
}

// Initialize Controller and View
$oaiController = new OAIController($database);
$oaiView = new OAIView();

// Determine the action based on the request
$action = $_GET['action'] ?? 'listRecords';

// Invoke action on controller and render the view
try {
    $data = $oaiController->$action();
    $parameters = [
        'metadataPrefix' => 'dc',
        'from' => null,
        'until' => null,
        'set' => null,
        'resumptionToken' => null,
    ];

    $metadataFormatPlugin = new DublinCorePlugin();

    //$oaiView->renderListRecords($data, $parameters, $metadataFormatPlugin);
} catch (Exception $e) {
    // Handle exception, log error and return a response
    error_log($e->getMessage());
    http_response_code(600);
    echo "Internal Server Error";
}
