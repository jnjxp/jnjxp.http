<?php
/**
 * Jnjxp HTTP Utilities
 *
 * PHP version 7
 *
 * Copyright (C) 2019 Jake Johns
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 *
 * @category  Output
 * @package   Jnjxp\Http
 * @author    Jake Johns <jake@jakejohns.net>
 * @copyright 2019 Jake Johns
 * @license   http://jnj.mit-license.org/2019 MIT License
 * @link      http://jakejohns.net
 */

declare(strict_types=1);

namespace Jnjxp\Http\Output;

use Http\Discovery\Psr17FactoryDiscovery as Discover;
use Fig\Http\Message\StatusCodeInterface as Code;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;

abstract class AbstractResponder
{
    /**
     * Response factory
     *
     * @var ResponseFactory
     *
     * @access private
     */
    private $responseFactory;

    /**
     * __construct
     *
     * @param ResponseFactory $responseFactory PSR-17 Response Factory
     *
     * @access public
     */
    public function __construct(ResponseFactory $responseFactory = null)
    {
        $this->responseFactory = $responseFactory
            ?: Discover::findResponseFactory();
    }

    /**
     * Create a Response
     *
     * @param int    $code         HTTP Status code
     * @param string $reasonPhrase Reason phrase
     *
     * @return Response
     *
     * @access protected
     */
    protected function createResponse(
        int $code = Code::STATUS_OK,
        string $reasonPhrase = ''
    ): Response {
        if (! $this->responseFactory instanceof ResponseFactory) {
            $this->responseFactory = Discover::findResponseFactory();
        }
        return $this->responseFactory->createResponse($code, $reasonPhrase);
    }
}
