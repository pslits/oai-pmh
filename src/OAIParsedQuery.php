<?php

/* +--------------------------------------------------------------------------+
 * | Filename: OAIParsedQuery.php
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

class OAIParsedQuery
{
    /**
     *
     * @var array<array{key: string, value: string}> An array of key-value pairs parsed from the query string.
     */
    private array $pairs = [];

    /**
     * OAIParsedQuery constructor.
     *
     * @param string $queryString The query string to be parsed.
     *
     * @throws OAIException If the query string is too long.
     */
    public function __construct(string $queryString)
    {
        // protect against DoS attacks by limiting the size of the request
        if (strlen($queryString) > 1000) {
            throw new OAIException('badArgument', 'Request is too long');
        }

        foreach (explode('&', $queryString) as $pair) {
            if (trim($pair) === '') {
                continue;
            }

            [$key, $value] = explode('=', $pair, 2) + [null, null];
            $key = urldecode(trim($key ?? ''));
            $value = urldecode(trim($value ?? ''));
            $this->pairs[] = ['key' => $key, 'value' => $value];
        }
    }

    /**
     * Get the value of a specific key from the parsed query.
     *
     * @param string $key The key to search for.
     *
     * @return array<string> An array of values associated with the specified key.
     */
    public function getValuesByKey(string $key): array
    {
        return array_map(
            fn ($item) => $item['value'],
            array_filter($this->pairs, fn ($item) => $item['key'] === $key)
        );
    }

    /**
     * Get the first value associated with a specific key from the parsed query.
     *
     * @param string $key The key to search for.
     *
     * @return string|null The first value associated with the specified key, or null if not found.
     */
    public function getFirstValue(string $key): ?string
    {
        foreach ($this->pairs as $item) {
            if ($item['key'] === $key) {
                return $item['value'];
            }
        }
        return null;
    }

    /**
     * Get all keys from the parsed query.
     *
     * @return array<string> An array of keys from the parsed query.
     */
    public function getKeys(): array
    {
        return array_column($this->pairs, 'key');
    }

    /**
     * Count the occurrences of a specific key in the parsed query.
     *
     * @param string $key The key to count.
     *
     * @return int The number of occurrences of the specified key.
     */
    public function countKeyOccurrences(string $key): int
    {
        return count(array_filter($this->pairs, fn ($item) => $item['key'] === $key));
    }

    /**
     * Convert the parsed query to an array of key-value pairs.
     *
     * @return array<array{key: string, value: string}> An array of key-value pairs.
     */
    public function toArray(): array
    {
        return $this->pairs;
    }
}
