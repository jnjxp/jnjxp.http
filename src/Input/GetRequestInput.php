<?php
/**
 * Jnjxp HTTP
 *
 * PHP version 7
 *
 * Copyright (C) 2019 Jake Johns
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 *
 * @category  Input
 * @package   Jnjxp\Http
 * @author    Jake Johns <jake@jakejohns.net>
 * @copyright 2019 Jake Johns
 * @license   http://jnj.mit-license.org/2019 MIT License
 * @link      http://jakejohns.net
 */

declare(strict_types=1);

namespace Jnjxp\Http\Input;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Marshal input from Request
 */
class GetRequestInput
{
    use GetUploadedFilesTrait;

    /**
     * Get unified array of input
     *
     * @param Request $request PSR7 Request
     *
     * @return array An array with one element: the array of combined Request
     * parameters and attributes.
     *
     * @access public
     */
    public function __invoke(Request $request) : array
    {
        return [
            array_replace(
                (array) $request->getQueryParams(),
                (array) $request->getParsedBody(),
                (array) $this->getFilesFromRequest($request),
                (array) $request->getCookieParams(),
                (array) $request->getAttributes()
            )
        ];
    }
}
