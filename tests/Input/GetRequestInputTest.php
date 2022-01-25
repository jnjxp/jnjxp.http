<?php

declare(strict_types=1);

namespace Jnjxp\Http\Input;

use PHPUnit\Framework\TestCase;

use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\UploadedFileFactory;
use Zend\Diactoros\Stream;
use SplFileInfo;


class GetRequestInputTest extends TestCase
{
    protected $request;
    protected $uploadFactory;

    public function setup() : void
    {
        $request = ServerRequestFactory::fromGlobals();
        $this->uploadFactory = new UploadedFileFactory();

        $files = [
            'foo' => $this->fakeUpload('foo'),
            'qix' => [
                'bar' => $this->fakeUpload('bar'),
                'baz' => $this->fakeUpload('baz'),
                'bing' => $this->fakeUpload('baz', true),
            ]
        ];

        $this->request = $request->withUploadedFiles($files)
            ->withAttribute('attr', 'attr');

        $this->getRequestInput = new GetRequestInput();
    }

    protected function fakeUpload($name, $fail = false)
    {
        return $this->uploadFactory
            ->createUploadedFile(
                new Stream(__DIR__ . '/files/' . $name),
                null,
                $fail ? UPLOAD_ERR_NO_FILE : UPLOAD_ERR_OK
            );
    }

    protected function fakeFile($name)
    {
        return new SplFileInfo(__DIR__ . '/files/' . $name);
    }

    public function testGetInput()
    {
        $expect = [
            [
                'foo' => $this->fakeFile('foo'),
                'qix' => [
                    'bar' => $this->fakeFile('bar'),
                    'baz' => $this->fakeFile('baz'),
                    'bing' => null
                ],
                'attr' => 'attr'
            ]
        ];
        $input = ($this->getRequestInput)($this->request);
        $this->assertEquals($expect, $input);
    }
}
