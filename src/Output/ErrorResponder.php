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

use Fig\Http\Message\StatusCodeInterface as Code;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\StreamInterface as stream;
use Throwable;

class ErrorResponder extends AbstractResponder
{
    /**
     * Is Debug Mode?
     *
     * @var bool
     *
     * @access protected
     */
    protected $debug = false;

    /**
     * Default non-debug message
     *
     * @var string
     *
     * @access protected
     */
    protected $message = 'An error occured';

    /**
     * Set Debug Mode
     *
     * @param bool $isDebug true if in debug mode
     *
     * @return void
     *
     * @access public
     */
    public function setDebugMode(bool $isDebug) : void
    {
        $this->debug = $isDebug;
    }

    /**
     * Set default message
     *
     * @param string $message non-debug message
     *
     * @return void
     *
     * @access public
     */
    public function setMessage(string $message) : void
    {
        $this->message = $message;
    }

    /**
     * Is Debug Mode?
     *
     * @return bool
     *
     * @access protected
     */
    protected function isDebugMode() : bool
    {
        return $this->debug;
    }

    /**
     * Create a response from a Throwable
     *
     * @param Throwable $error that was caught
     *
     * @return Response
     *
     * @access public
     */
    public function respondWithError(Throwable $error) : Response
    {
        $status   = $this->getStatusCode($error);
        $response = $this->createResponse($status);
        $body     = $response->getBody();
        $this->writeError($body, $error);
        return $response;
    }

    /**
     * Write error message to stream
     *
     * @param Stream    $stream response body
     * @param Throwable $error  thrown error
     *
     * @return void
     *
     * @access protected
     */
    protected function writeError(Stream $stream, Throwable $error) : void
    {
        if ($this->isDebugMode()) {
            $stream->write((string) $error);
            return;
        }

        $stream->write($this->message);
    }

    /**
     * Make easily callable
     *
     * @param Throwable $error Error
     *
     * @return Response
     *
     * @access public
     */
    public function __invoke(Throwable $error) : Response
    {
        return $this->respondWithError($error);
    }

    /**
     * Get status code based on Error code
     *
     * @param Throwable $error that was caught
     *
     * @return int
     *
     * @access protected
     */
    protected function getStatusCode(Throwable $error) : int
    {
        $status = $error->getCode();
        return ($status && $status >= 400 && $status < 600)
            ? $status
            : Code::STATUS_INTERNAL_SERVER_ERROR;
    }
}
