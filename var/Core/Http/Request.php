<?php
/**
 * Created by TarBlog.
 * Date: 2020/4/3
 * Time: 10:55
 */

namespace Core\Http;

use Core\File;

class Request
{
    /**
     * $_GET
     *
     * @var array
     */
    private $_gets;

    /**
     * $_POST
     *
     * @var array
     */
    private $_posts;

    /**
     * $_FILES
     *
     * @var File[]
     */
    private $_files;

    /**
     * @var string
     */
    private $_pathInfo;

    /**
     * @var string
     */
    private $_requestUri;

    /**
     * @var string
     */
    private $_baseUrl;

    /**
     * @var string
     */
    private $_method;

    /**
     * @var Session
     */
    private $_session;

    /**
     * 初始化请求类
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->_gets = array_filter($_GET, function ($val) {
            return mb_check_encoding($val);
        });

        $this->_posts = array_filter($_POST, function ($val) {
            return mb_check_encoding($val);
        });

        foreach ($_FILES as $key => $val) {
            if (isset($val['name']) && is_array($val['name'])) {
                $data = [];
                $keys = array_keys($val);
                for ($i=0; $i<count($val['name']); $i++) {
                    $item = [];
                    foreach ($keys as $k) $item[$k] = $val[$k][$i];
                    $data = new File(true, $item);
                }
            } else {
                $data = new File(true, $val);
            }
            $this->_files[$key] = $data;
        }

        $this->_method = $_SERVER['REQUEST_METHOD'];

        $this->_session = $session;
    }

    /**
     * 获取请求地址
     *
     * @return string
     */
    public function getRequestUri()
    {
        // 缓存信息
        if (!empty($this->_requestUri)) {
            return $this->_requestUri;
        }

        //处理requestUri
        $requestUri = '/';

        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // check this first so IIS will catch
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (
            // IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
            isset($_SERVER['IIS_WasUrlRewritten'])
            && $_SERVER['IIS_WasUrlRewritten'] == '1'
            && isset($_SERVER['UNENCODED_URL'])
            && $_SERVER['UNENCODED_URL'] != ''
        ) {
            $requestUri = $_SERVER['UNENCODED_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
            $parts       = @parse_url($requestUri);

            if (isset($_SERVER['HTTP_HOST']) && strstr($requestUri, $_SERVER['HTTP_HOST'])) {
                if (false !== $parts) {
                    $requestUri  = (empty($parts['path']) ? '' : $parts['path'])
                        . ((empty($parts['query'])) ? '' : '?' . $parts['query']);
                }
            } elseif (!empty($_SERVER['QUERY_STRING']) && empty($parts['query'])) {
                // fix query missing
                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0, PHP as CGI
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        }

        return $this->_requestUri = (strlen($requestUri) == '1' ? $requestUri : substr($requestUri, 1));
    }

    /**
     * 获取根url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        // 缓存信息
        if (NULL !== $this->_baseUrl) {
            return $this->_baseUrl;
        }

        //处理baseUrl
        $filename = (isset($_SERVER['SCRIPT_FILENAME'])) ? basename($_SERVER['SCRIPT_FILENAME']) : '';

        if (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === $filename) {
            $baseUrl = $_SERVER['SCRIPT_NAME'];
        } elseif (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) === $filename) {
            $baseUrl = $_SERVER['PHP_SELF'];
        } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename) {
            $baseUrl = $_SERVER['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path    = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
            $file    = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
            $segs    = explode('/', trim($file, '/'));
            $segs    = array_reverse($segs);
            $index   = 0;
            $last    = count($segs);
            $baseUrl = '';
            do {
                $seg     = $segs[$index];
                $baseUrl = '/' . $seg . $baseUrl;
                ++$index;
            } while (($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos));
        }

        // Does the baseUrl have anything in common with the request_uri?
        $finalBaseUrl = NULL;
        $requestUri = $this->getRequestUri();

        if (0 === strpos($requestUri, $baseUrl)) {
            // full $baseUrl matches
            $finalBaseUrl = $baseUrl;
        } else if (0 === strpos($requestUri, dirname($baseUrl))) {
            // directory portion of $baseUrl matches
            $finalBaseUrl = rtrim(dirname($baseUrl), '/');
        } else if (strpos($requestUri, basename($baseUrl)) === false) { // 修复：0和false的区别是0存在，false不存在
            // no match whatsoever; set it blank
            $finalBaseUrl = '';
        } else if ((strlen($requestUri) >= strlen($baseUrl))
            && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0)))
        {
            // If using mod_rewrite or ISAPI_Rewrite strip the script filename
            // out of baseUrl. $pos !== 0 makes sure it is not matching a value
            // from PATH_INFO or QUERY_STRING
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        return ($this->_baseUrl = (NULL === $finalBaseUrl) ? rtrim($baseUrl, '/') : $finalBaseUrl);
    }

    /**
     * 获取当前PATH_INFO
     *
     * @param string $inputEncoding 输入编码
     * @param string $outputEncoding 输出编码
     * @return string
     */
    public function getPathInfo($inputEncoding = NULL, $outputEncoding = NULL)
    {
        // 缓存信息
        if (NULL !== $this->_pathInfo) {
            return $this->_pathInfo;
        }

        //参考Zend Framework对pathinfo的处理, 更好的兼容性
        $pathInfo = NULL;

        //处理requestUri
        $requestUri = $this->getRequestUri();
        $finalBaseUrl = $this->getBaseUrl();

        // Remove the query string from REQUEST_URI
        if (($pos = strpos($requestUri, '?')) !== false) { // 修复初始位为0时也视为不存在的问题
            $requestUri = substr($requestUri, 0, $pos);
        }

        if ((NULL !== $finalBaseUrl)
            && (false === ($pathInfo = substr($requestUri, strlen($finalBaseUrl)))))
        {
            // If substr() returns false then PATH_INFO is set to an empty string
            $pathInfo = '/';
        } elseif (NULL === $finalBaseUrl) {
            $pathInfo = $requestUri;
        }

        if (!empty($pathInfo)) {
            //针对iis的utf8编码做强制转换
            //参考http://docs.moodle.org/ja/%E5%A4%9A%E8%A8%80%E8%AA%9E%E5%AF%BE%E5%BF%9C%EF%BC%9A%E3%82%B5%E3%83%BC%E3%83%90%E3%81%AE%E8%A8%AD%E5%AE%9A
            if (!empty($inputEncoding) && !empty($outputEncoding) &&
                (stripos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false
                    || stripos($_SERVER['SERVER_SOFTWARE'], 'ExpressionDevServer') !== false)) {
                if (function_exists('mb_convert_encoding')) {
                    $pathInfo = mb_convert_encoding($pathInfo, $outputEncoding, $inputEncoding);
                } else if (function_exists('iconv')) {
                    $pathInfo = iconv($inputEncoding, $outputEncoding, $pathInfo);
                }
            }
        } else {
            $pathInfo = '/';
        }

        return ($this->_pathInfo = '/' . ltrim(urldecode($pathInfo), '/'));
    }

    /**
     * @return mixed
     */
    public function method()
    {
        return $this->_method;
    }

    public function isMethod($method)
    {
        return strtolower($this->_method) == strtolower($method);
    }

    public function get($key = null, $default = null)
    {
        if (is_null($key))
            return $this->_gets;

        return $this->_gets[$key] ?? $default;
    }

    public function post($key = null, $default = null)
    {
        if (is_null($key))
            return $this->_posts;

        return $this->_posts[$key] ?? $default;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->_gets) || array_key_exists($key, $this->_posts);
    }

    public function input($key, $default = null)
    {
        if (isset($this->_gets[$key]))
            return $this->_gets[$key];

        if (isset($this->_posts[$key]))
            return $this->_posts[$key];

        return $default;
    }

    public function all()
    {
        return array_merge($this->_gets, $this->_posts);
    }

    public function file($key = null)
    {
        if (is_null($key))
            return $this->_files;

        return $this->_files[$key] ?? null;
    }

    /**
     * @param string|null $key
     * @param string|null $default
     * @return Cookie|mixed
     */
    public function cookie($key = null, $default = null)
    {
        if (is_null($key)) return Cookie::class;

        return Cookie::get($key, $default);
    }

    /**
     * @param string|null $key
     * @param string|null $default
     * @return Session|mixed
     */
    public function session($key = null, $default = null)
    {
        if (is_null($key)) return $this->_session;

        return $this->_session->get($key, $default);
    }
}