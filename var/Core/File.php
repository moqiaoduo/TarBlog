<?php
/**
 * Created by tarblog.
 * Date: 2020/8/4
 * Time: 16:52
 */

namespace Core;

class File
{
    private $name;

    private $originName;

    private $mime;

    private $path;

    private $relativePath;

    private $originPath;

    private $isUpload;

    private $err;

    private $size;

    public function __construct($isUpload, $options = [])
    {
        $this->isUpload = $isUpload;

        if (is_array($options)) {
            $this->name = $this->originName = $options['name'] ?? null;
            $this->path = $this->originPath = $options['tmp_name'] ?? null;
            $this->err = $options['error'] ?? null;
            if ($isUpload) {
                $this->size = $options['size'] ?? null;
                $this->mime = $options['type'] ?? null;
            } else {
                $this->size = filesize($this->path);
                $finfo = finfo_open(FILEINFO_MIME_TYPE); // 返回 mime 类型
                $this->mime = finfo_file($finfo, $this->path);
                finfo_close($finfo);
            }
        } else {
            $this->path = $this->originPath = $options;

            $this->name = $this->originName = basename($this->path);
        }
    }

    public function isValid()
    {
        if ($this->isUpload) {
            return !(is_null($this->originName) || is_null($this->originPath) || is_null($this->mime) ||
                $this->err != UPLOAD_ERR_OK || is_null($this->size));
        } else {
            return !(is_null($this->name) || is_null($this->path));
        }
    }

    public function getFileExt()
    {
        return substr(strrchr($this->name, '.'), 1);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOriginName()
    {
        return $this->originName;
    }

    public function getOriginNameWithoutExt()
    {
        return basename($this->originName, $this->getFileExt());
    }

    public function getErr()
    {
        return $this->err;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getFormatSize()
    {
        return format_size($this->size, true);
    }

    public function getMime()
    {
        return $this->mime;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getRelativePath($prefixPath = null)
    {
        if (is_null($this->relativePath)) {
            if (is_null($prefixPath)) $prefixPath = __ROOT_DIR__ . '/usr/upload/';
            $this->relativePath = substr($this->path, strlen($prefixPath));
        }

        return $this->relativePath;
    }

    public function move($path, $name = null)
    {
        if (!$this->isUpload || $this->err != UPLOAD_ERR_OK) return;

        if (is_null($name)) $name = $this->name;
        else $this->name = $name;

        $relativePath = $path . DIRECTORY_SEPARATOR . $name;

        $basePath = __ROOT_DIR__ . '/usr/upload/';

        if (!is_dir($basePath . $path)) mkdir($basePath . $path);

        move_uploaded_file($this->originPath, $this->path = $basePath . $relativePath);

        $this->relativePath = $relativePath;
    }

    public function __toString()
    {
        return $this->name;
    }
}