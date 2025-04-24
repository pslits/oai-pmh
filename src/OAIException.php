<?php

/* +--------------------------------------------------------------------------+
 * | Filename: OAIException.php
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

use Exception;

/**
 * Class OAIException
 *
 * This class is responsible for handling OAI-PMH exceptions.
 */
class OAIException extends Exception
{
    private array $exceptionList = [];

    public function __construct(?string $errorCode = null, ?string $message = null)
    {
        parent::__construct("OAI validation error");

        if ($errorCode && $message) {
            $this->add($errorCode, $message);
        }
    }

    /**
     * Adds an error message to the exception list. The messages are grouped by error code.
     *
     * @param string $errorCode The OAI-PMH error code.
     * @param string $message The error message.
     */
    public function add(string $errorCode, string $message): void
    {
        $this->exceptionList[$errorCode][] = $message;
    }

    /**
     * Checks if there are any exceptions in the exception list.
     *
     * @return bool True if there are exceptions, false otherwise.
     */
    public function hasExceptions(): bool
    {
        return !empty($this->exceptionList);
    }

    /**
     * Returns the exception list.
     *
     * @return array The exception list, grouped by error code.
     */
    public function getExceptionList(): array
    {
        return $this->exceptionList;
    }
}
