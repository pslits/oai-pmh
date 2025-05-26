<?php

/**
 * +--------------------------------------------------------------------------+
 * | This file is part of the OAI-PMH package.                                |
 * | @link https://github.com/pslits/oai-pmh                                  |
 * +--------------------------------------------------------------------------+
 * | (c) 2025 Paul Slits <paul.slits@gmail.com>                               |
 * | This source code is licensed under the MIT license found in the LICENSE  |
 * | file in the root directory of this source tree or at the following link: |
 * | @license MIT <https://opensource.org/licenses/MIT>                       |
 * +--------------------------------------------------------------------------+
 */

namespace OaiPmh\Domain;

class MetadataNamespace
{
    private NamespacePrefix $prefix;
    private AnyUri $uri;

    /**
     * Namespace constructor.
     * Initializes a new instance of the Namespace class.
     *
     * @param NamespacePrefix $prefix The namespace prefix used in XML elements.
     * @param AnyUri $uri The URI associated with the namespace.
     */
    public function __construct(NamespacePrefix $prefix, AnyUri $uri)
    {
        $this->prefix = $prefix;
        $this->uri = $uri;
    } // End of constructor

    /**
     * Get the namespace prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix->getPrefix();
    } // End of getPrefix

    /**
     * Get the URI associated with the namespace.
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri->getUri();
    } // End of getUri
}
