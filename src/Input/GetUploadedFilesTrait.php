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
 * @package   Jnjxp\HTTP
 * @author    Jake Johns <jake@jakejohns.net>
 * @copyright 2019 Jake Johns
 * @license   http://jnj.mit-license.org/2019 MIT License
 * @link      http://jakejohns.net
 */

declare(strict_types=1);

namespace Jnjxp\Http\Input;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface as UploadedFile;
use SplFileInfo;

/**
 * Trait: GetUploadedFilesTrait
 *
 * Convert UploadedFileInterface to SplFileInfo for domain input.
 * PSR-7 objects should not cross the boundry between the UI and domain
 */
trait GetUploadedFilesTrait
{

    /**
     * GetFilesFromRequest
     *
     * @param Request $request request
     *
     * @return array
     *
     * @access protected
     */
    protected function getFilesFromRequest(Request $request) : array
    {
        return $this->getFilesFromArray($request->getUploadedFiles());
    }

    /**
     * GetFilesFromArray
     *
     * @param array $data array of UploadedFileInterface
     *
     * @return array
     *
     * @access protected
     */
    protected function getFilesFromArray(array $data) : array
    {
        $files = [];
        foreach ($data as $key => $upload) {
            $files[$key] = is_array($upload)
                ? $this->getFilesFromArray($upload)
                : $this->getFileFromUpload($upload);
        }
        return $files;
    }

    /**
     * GetFileFromUpload
     *
     * @param UploadedFile $upload uploaded file
     *
     * @return SplFileInfo | null
     *
     * @access protected
     */
    protected function getFileFromUpload(UploadedFile $upload) : ?SplFileInfo
    {
        if ($upload->getError() !== UPLOAD_ERR_OK) {
            return null;
        }

        $uri = $upload->getStream()->getMetadata('uri');
        return new SplFileInfo($uri);
    }
}
