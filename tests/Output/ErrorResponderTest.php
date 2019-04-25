<?php

declare(strict_types=1);

namespace Jnjxp\Http\Output;

use PHPUnit\Framework\TestCase;


use Exception;
use Fig\Http\Message\StatusCodeInterface as Code;

class ErrorResponderTest extends TestCase
{
    protected $responder;

    public function setup() : void
    {
        $this->responder = new ErrorResponder();
    }

    public function testQuietBody()
    {
        $exception = new Exception('foo');
        $this->responder->setMessage('message');
        $response = ($this->responder)($exception);
        $this->assertEquals('message', (string) $response->getBody());
        $this->assertEquals(
            Code::STATUS_INTERNAL_SERVER_ERROR,
            $response->getStatusCode()
        );
    }

    public function testDebugBody()
    {
        $exception = new class extends \Exception {
            public function __toString() {
                return 'string';
            }
        };
        $this->responder->setDebugMode(true);
        $response = ($this->responder)($exception);
        $this->assertEquals('string', (string) $response->getBody());
        $this->assertEquals(
            Code::STATUS_INTERNAL_SERVER_ERROR,
            $response->getStatusCode()
        );
    }

    public function testExceptionStatus()
    {
        $exception = new Exception('foo', Code::STATUS_NOT_FOUND);
        $this->responder->setMessage('message');
        $response = ($this->responder)($exception);
        $this->assertEquals('message', (string) $response->getBody());
        $this->assertEquals(
            Code::STATUS_NOT_FOUND,
            $response->getStatusCode()
        );
    }

}
