<?php

declare(strict_types=1);

namespace Jnjxp\Http;

use PHPUnit\Framework\TestCase;

use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Zend\Diactoros;


/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContianerTest extends TestCase
{
    protected $container;

    protected $cfgContainer;

    protected $provides = [
        RequestFactoryInterface::class,
        ResponseFactoryInterface::class,
        ServerRequestFactoryInterface::class,
        StreamFactoryInterface::class,
        UploadedFileFactoryInterface::class,
        UriFactoryInterface::class,
        ServerRequestCreatorInterface::class,
    ];

    public function setup() : void
    {
        $this->container = new Container();

        $this->cfgContainer = new Container(
            Diactoros\RequestFactory::class,
            Diactoros\ResponseFactory::class,
            Diactoros\ServerRequestFactory::class,
            Diactoros\StreamFactory::class,
            Diactoros\UploadedFileFactory::class,
            Diactoros\UriFactory::class,
        );
    }

    public function interfaceProvider()
    {
        foreach ($this->provides as $interface) {
            yield [$interface];
        }
    }

    /**
     * @dataProvider interfaceProvider
     */
    public function testProvidesInterfaces($interface)
    {
        $this->assertInstanceOf(
            $interface,
            $this->container->get($interface)
        );
    }

    /**
     * @dataProvider interfaceProvider
     */
    public function testProvidesLazy($interface)
    {
        $lazy = $this->container->lazy($interface);
        $this->assertInstanceOf($interface, $lazy());
    }

    /**
     * @dataProvider interfaceProvider
     */
    public function testprovidesConfiged($interface)
    {
        $this->assertInstanceOf(
            $interface,
            $this->cfgContainer->get($interface)
        );
    }

    public function testGetInvalid()
    {
        $this->expectException(
            \Psr\Container\NotFoundExceptionInterface::class
        );
        $this->container->get('foo');
    }

    public function testLazyFail()
    {
        $this->expectException(
            \Psr\Container\NotFoundExceptionInterface::class
        );
        $this->container->lazy('foo');
    }

    public function testInvalidConfig()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->cfgContainer = new Container(Container::class);
    }

    public function testProvides()
    {
        $this->assertEquals(
            $this->container->provides(),
            $this->provides
        );
    }

}
