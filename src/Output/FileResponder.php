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
use Http\Discovery\Psr17FactoryDiscovery as Discover;
use Lmc\HttpConstants\Header;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;
use Psr\Http\Message\StreamInterface as Stream;
use SplFileInfo;

/**
 * FileResponder
 *
 * @category Output
 * @package  Jnjxp\Http
 * @author   Jake Johns <jake@jakejohns.net>
 * @license  https://jnj.mit-license.org/ MIT License
 * @link     https://jakejohns.net
 *
 * @see AbstractResponder
 */
class FileResponder extends AbstractResponder
{

    const DATE_FORMAT = 'D, d M Y H:i:s T';

    /**
     * StreamFactory
     *
     * @var StreamFactory
     *
     * @access protected
     */
    protected $streamFactory;

    /**
     * Accepts Range header?
     *
     * @var bool
     *
     * @access protected
     */
    protected $canServeBytes = true;

    /**
     * Response
     *
     * @var Response
     *
     * @access protected
     */
    protected $response;

    /**
     * Create a file responder
     *
     * @param ResponseFactory $responseFactory PSR-17 ResponseFactoryInterface
     * @param StreamFactory   $streamFactory   PSR-17 StreamFactoryInterface
     *
     * @return void
     *
     * @access public
     */
    public function __construct(
        ResponseFactory $responseFactory = null,
        StreamFactory $streamFactory = null
    ) {
        parent::__construct($responseFactory);
        $this->streamFactory = $streamFactory ?: Discover::findStreamFactory();
    }

    /**
     * Set if this respondes to range requests
     *
     * @param bool $can true if it can
     *
     * @return void
     *
     * @access public
     */
    public function setCanServeBytes(bool $can) : void
    {
        $this->canServeBytes = $can;
    }

    /**
     * Respond with a file
     *
     * @param SplFileInfo $file    File with which to respond
     * @param Request     $request Request for file
     *
     * @return Response
     *
     * @access public
     */
    public function respondWithFile(SplFileInfo $file, Request $request = null) : Response
    {
        if (! $file->isFile()) {
            return $this->fileNotFound();
        }

        $this->response = $this->createResponse();

        $this->withHeaders($file);
        $this->withBody($file);

        if ($request && $this->shouldAddRange($request)) {
            $this->withRange($request, $file);
        }

        return $this->response;
    }

    /**
     * File not found response
     *
     * @return Response
     *
     * @access protected
     */
    protected function fileNotFound() : Response
    {
        return $this->createResponse(Code::STATUS_NOT_FOUND);
    }

    /**
     * Add headers to response
     *
     * @param SplFileInfo $file File to respond with
     *
     * @return void
     *
     * @access protected
     */
    protected function withHeaders(SplFileInfo $file) : void
    {
        $headers = [
            Header::LAST_MODIFIED  => gmdate(self::DATE_FORMAT, $file->getMTime()),
            Header::CONTENT_LENGTH => (string) $file->getSize()
        ];

        $mime = mime_content_type((string) $file);

        if($mime) {
            $headers[Header::CONTENT_TYPE] = $mime;
        }

        if ($this->canServeBytes()) {
            $headers[Header::ACCEPT_RANGES]  = 'bytes';
        }

        foreach ($headers as $header => $value) {
            $this->response = $this->response->withHeader($header, $value);
        }
    }

    /**
     * Add Body to response
     *
     * @param SplFileInfo $file File to respond with
     *
     * @return void
     *
     * @access protected
     */
    protected function withBody(SplFileInfo $file) : void
    {
        $body = $this->createStream($file);
        $this->response = $this->response->withBody($body);
    }

    /**
     * Add Range headers to response
     *
     * @param Request     $request  Request for file
     * @param SplFileInfo $file     Requested File
     *
     * @return void
     *
     * @access protected
     */
    protected function withRange(Request $request, SplFileInfo $file) : void
    {
        $range = $this->newRequestRange($request, $file);
        $this->response = $range->applyHeaders($this->response);
    }

    /**
     * Create a new request range
     *
     * @param Request     $request  Request for file
     * @param SplFileInfo $file     Requested File
     *
     * @return RequestRange
     *
     * @access protected
     */
    protected function newRequestRange(Request $request, SplFileInfo $file) : RequestRange
    {
        return RequestRange::fromRequest($request, $file->getSize());
    }

    /**
     * Create a Stream
     *
     * @param mixed $body stream body
     *
     * @return Stream
     *
     * @access protected
     */
    protected function createStream($body = null) : Stream
    {
        if (! $this->streamFactory instanceof StreamFactory) {
            $this->streamFactory = Discover::findStreamFactory();
        }
        return $this->streamFactory->createStreamFromFile((string) $body);
    }

    /**
     * Should range headers be added?
     *
     * @param Request $request PSR7 Request
     *
     * @return bool
     *
     * @access protected
     */
    protected function shouldAddRange(Request $request) : bool
    {
        return (
            $this->canServeBytes()
            && $this->isRangeRequest($request)
        );
    }

    /**
     * Is this a range request?
     *
     * @param Request $request PSR7 Request
     *
     * @return bool
     *
     * @access protected
     */
    protected function isRangeRequest(Request $request) : bool
    {
        return RequestRange::isRangeRequest($request);
    }

    /**
     * Can we server bytes to range requests?
     *
     * @return bool
     *
     * @access protected
     */
    protected function canServeBytes() : bool
    {
        return $this->canServeBytes;
    }

}
