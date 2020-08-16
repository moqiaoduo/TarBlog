<?php
/**
 * Created by tarblog.
 * Date: 2020/7/31
 * Time: 15:16
 */

namespace Core;

use DirectoryIterator;

class Dir
{
    /**
     * @var DirectoryIterator
     */
    private $directoryItr;

    public function __construct($path = '')
    {
        $this->path($path);
    }

    public function path($path)
    {
        $this->directoryItr = new DirectoryIterator(__ROOT_DIR__ .
            (substr($path, 0, 1) == '/' ? $path : '/' . $path));

        return $this;
    }

    public function getAllDirs($path = '')
    {
        $dirs = [];

        if (!empty($path)) $this->path($path);

        foreach ($this->directoryItr as $fileInfo) {
            if (!$fileInfo->isDot() && $fileInfo->isDir())
                $dirs[] = $fileInfo->getFilename();
        }

        return $dirs;
    }

    public function getAllFiles($path = '')
    {
        $dirs = [];

        if (!empty($path)) $this->path($path);

        foreach ($this->directoryItr as $fileInfo) {
            if (!$fileInfo->isDot() && $fileInfo->isFile())
                $dirs[] = $fileInfo->getFilename();
        }

        return $dirs;
    }

    public function getAllDirsAndFiles($path = '')
    {
        $files = [];

        if (!empty($path)) $this->path($path);

        foreach ($this->directoryItr as $fileInfo) {
            if (!$fileInfo->isDot())
                $files[] = $fileInfo->getFilename();
        }

        return $files;
    }

    public function countDirs($path = '')
    {
        if (!empty($path)) $this->path($path);

        return count($this->getAllDirs());
    }

    public function hasDirs($path = '')
    {
        if (!empty($path)) $this->path($path);

        return $this->countDirs() > 0;
    }
}