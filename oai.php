<?php
/*
 * Filename: oai.php
 * Project: oai-mph
 *
 * Author: Paul Slits
 *
 * Copyright (C) [Year] Paul Slits
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Special thanks to OpenAI's ChatGPT for providing guidance and assistance 
 * during the development process.
 */

require_once 'vendor/autoload.php';

use Pslits\OaiPmh\Database;
use Pslits\OaiPmh\OAIController;
use Pslits\OaiPmh\OAIView;
use Pslits\OaiPmh\DublinCorePlugin;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $database = new Database([
        'dsn' => $_ENV['DATABASE_DSN'],
        'username' => $_ENV['DATABASE_USERNAME'],
        'password' => $_ENV['DATABASE_PASSWORD'],
    ]);
    $oaiController = new OAIController($database);
    $oaiView = new OAIView();
    $metadataFormatPlugin = new DublinCorePlugin();

    $verb = $_GET['verb'] ?? null;
    if (!$verb) {
        $oaiView->renderError('badVerb', 'Verb is required');
        exit;
    }

    $metadataPrefix = $_GET['metadataPrefix'] ?? null;
    if (!$metadataPrefix) {
        $oaiView->renderError('badArgument', 'metadataPrefix is required');
        exit;
    }

    // Initialize controller and metadataFormatPlugin based on metadataPrefix or other parameters
    switch ($metadataPrefix) {
        case 'oai_dc':
            $oaiController = new OAIController($database);
            $metadataFormatPlugin = new DublinCorePlugin();
            break;
        // case 'other_format':
        //     $oaiController = new OtherOAIControllerPlugin($database);
        //     $metadataFormatPlugin = new OtherMetadataFormatPlugin();
        //     break;
        default:
            $oaiView->renderError('cannotDisseminateFormat', 'The metadata format provided is not supported');
            exit;
    }

    // Validate against allowed verbs and call corresponding controller method
    switch ($verb) {
        case 'ListRecords':
            // Validate required and optional parameters here
            $from = $_GET['from'] ?? null;
            $until = $_GET['until'] ?? null;
            $set = $_GET['set'] ?? null;
            $resumptionToken = $_GET['resumptionToken'] ?? null;

            // Perform detailed input validation/sanitization as per OAI-PMH specs
            $parameters = [
                'metadataPrefix' => $metadataPrefix,
                'from' => $from,
                'until' => $until,
                'set' => $set,
                'resumptionToken' => $resumptionToken,
            ];

            // List Records
            $response = $oaiController->listRecords($parameters);
            $oaiView->renderListRecords($response, $parameters, $metadataFormatPlugin);
            break;
        // Other verbs like GetRecord, Identify, ListIdentifiers, ListMetadataFormats should also be implemented here in a similar way.
        default:
            $oaiView->renderError('badVerb', 'Illegal OAI-PMH verb');
            break;
    }
} catch (Exception $e) {
    $oaiView->renderError('internalServerError', $e->getMessage());
}
