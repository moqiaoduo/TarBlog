<?php
/**
 * Created by TarBlog.
 * Date: 2020/4/3
 * Time: 17:00
 */

namespace Core\Routing;

use Utils\Arr;

class Route
{
    /**
     * 路由名称
     *
     * @var string
     */
    private $name;

    /**
     * 路由Uri
     *
     * @var string
     */
    private $uri;

    /**
     * 路由动作
     *
     * @var string
     */
    private $action;

    /**
     * 支持多层路径的参数
     * 例如directory等
     * 需要路由进行设定
     *
     * @var array
     */
    private $multiDivParams = [];

    /**
     * 路由表达式控制
     *
     * @var array
     */
    private $wheres = [];

    public function __construct($uri = null, $action = null)
    {
        $this->uri = $uri;
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Route
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param mixed $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * 匹配参数
     *
     * @return array|mixed
     */
    public function getParamsFromPatternUri()
    {
        preg_match_all('/\{(.*?)\}/', $this->uri, $matches);
        return empty($matches) ? [] : $matches[1];
    }

    /**
     * 判断参数是否符合预设正则
     *
     * @param $param
     * @param $value
     * @return bool
     */
    public function ifParamsMatch($param, $value)
    {
        if (isset($this->wheres[$param])) {
            switch ($p = $this->wheres[$param]) {
                case '{cid}':
                    return is_numeric($value) && $value > 0;
                default:
                    preg_match('/' . $this->wheres[$param] . '/', $value, $match);
                    if (count($match) == 0) return false;
            }
        }
        return true;
    }

    /**
     * 设置支持多级目录的参数
     *
     * @param $params
     * @return $this|array
     */
    public function multiDiv($params = null)
    {
        if ($params === null) {
            return $this->multiDivParams;
        }

        $this->multiDivParams = array_merge($this->multiDivParams, Arr::wrap($params));

        return $this;
    }

    /**
     * 判断是否为支持多级目录的参数
     *
     * @param $param
     * @return bool
     */
    public function isMultiDivParam($param)
    {
        return in_array($param, $this->multiDivParams);
    }

    public function where($params)
    {
        $this->wheres = array_merge($this->wheres, $params);
    }
}