<?php

declare(strict_types=1);

namespace Jnjxp\Http\Output;

use PHPUnit\Framework\TestCase;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class DiscoverTest extends TestCase
{

    public function testPoorlyBuiltResponder()
    {
        $responder = new class extends AbstractResponder {
            public function __construct() {
                //forget constructor injection
            }

            public function respond() {
                return $this->createResponse();
            }
        };

        $this->assertInstanceOf(
            ResponseInterface::class,
            $responder->respond()
        );
    }

    public function testPoorlyBuiltFileResponder()
    {
        $responder = new class extends FileResponder {
            public function __construct() {
                //forget constructor injection
            }

            public function stream() {
                return $this->createStream(__FILE__);
            }
        };

        $this->assertInstanceOf(
            StreamInterface::class,
            $responder->stream()
        );
    }


}
