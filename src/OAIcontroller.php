<?php
/* +--------------------------------------------------------------------------+
 * | Filename: OAIController.php
 * | Author:   Paul Slits
 * | Project:  OAI-PMH
 * +--------------------------------------------------------------------------+
 * | Copyright (C) 2025 Paul Slits
 * |
 * | Permission is hereby granted, free of charge, to any person obtaining a
 * | copy of this software and associated documentation files (the "Software"),
 * | to deal in the Software without restriction, including without limitation
 * | the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * | and/or sell copies of the Software, and to permit persons to whom the
 * | Software is furnished to do so, subject to the following conditions:
 * |
 * | The above copyright notice and this permission notice shall be included in
 * | all copies or substantial portions of the Software.
 * |
 * | THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * | EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * | MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * | IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * | CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * | TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * | SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * +--------------------------------------------------------------------------+
 */

namespace Pslits\OaiPmh;

use \Firebase\JWT\JWT;

class OAIController
{

    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function listRecords($parameters)
    {

        $limit = 100;
        $offset = 0;

        try {
            if (isset($parameters['resumptionToken'])) {
                // Decode and verify resumption token.
                $token = JWT::decode($parameters['resumptionToken'], $this->secretKey, [$this->algorithm]);
                $offset = $token->offset;
            }
        } catch (\Exception $e) {
            // Handle invalid token here, for example, return an error response.
            return $this->formatErrorResponse('Invalid resumption token');
        }

        $records = $this->retrieveRecords($offset, $limit);

        if ($this->hasMoreRecords($offset, $limit)) {
            $nextOffset = $offset + $limit;
            $resumptionToken = $this->encodeResumptionToken($nextOffset);
        } else {
            $resumptionToken = null;
        }

        return $this->formatResponse($records, $resumptionToken);
    }

    private function encodeResumptionToken($offset)
    {
        $token = array(
            "offset" => $offset,
            "iat" => time(), // Issued at
            "exp" => time() + 3600 // Expires in 1 hour
        );
        return JWT::encode($token, $this->secretKey, $this->algorithm);
    }

    private function retrieveRecords($offset, $limit)
    {
        // Simulating retrieving records where Dublin Core values are part of the same record.
        $mockRecords = [
            [
                'identifier' => 'oai:example.org:record1',
                'datestamp' => '2023-09-29T12:00:00Z',
                'title' => 'Title of Record 1',
                'creator' => 'Author of Record 1',
                'subject' => 'Subject of Record 1',
                'description' => 'Description of Record 1',
                // ... Other Dublin Core fields for Record 1
            ],
            [
                'identifier' => 'oai:example.org:record2',
                'datestamp' => '2023-09-30T12:00:00Z',
                'title' => 'Title of Record 2',
                'creator' => 'Author of Record 2',
                'subject' => 'Subject of Record 2',
                'description' => 'Description of Record 2',
                // ... Other Dublin Core fields for Record 2
            ],
            // ... More mock records as needed.
        ];

        return $mockRecords;
    }

    private function hasMoreRecords($offset, $limit)
    {
        // Determine whether there are more records to be fetched.
    }

    private function formatResponse($records, $resumptionToken)
    {
        // Format and return the response with the records and the resumption token.
        return $records;
    }

    private function formatErrorResponse($message)
    {
        // Format and return an error response with the given message.
    }

    // Other OAI-PMH verbs implementation here
}
