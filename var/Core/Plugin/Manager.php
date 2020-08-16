<?php
/**
 * Created by TarBlog.
 * Date: 2020/4/3
 * Time: 19:35
 */

namespace Core\Plugin;

use Utils\Dir;
use Utils\PHPComment;
use Utils\Str;

class Manager
{
    /**
     * 所有已注册的插件处理器
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * 所有插件信息
     *
     * @var array
     */
    protected $info = [];

    /**
     * 激活的插件
     *
     * @var array
     */
    protected $ena_plugins;

    /**
     * @var boolean
     */
    protected $_plugged;

    public function __construct()
    {
        $this->scanActivePlugins();
    }

    private function scanActivePlugins()
    {
        $this->ena_plugins = (array)unserialize(get_option('ena_plugins', 'a:0:{}'));

        foreach ($this->ena_plugins as $plugin) {
            $class = Str::toCamel($plugin . '_plugin');

            if (!class_exists($class)) continue; // 会自动引入，所以只要检测一下

            $plugin = new $class($this);
            /* @var Plugin $plugin */
            $plugin->register();
        }
    }

    public function isEnable($plugin)
    {
        return in_array($plugin, $this->ena_plugins);
    }

    public function getPluginInfo($plugin)
    {
        if (empty($this->info))
            $this->scanAllPlugins();

        return $this->info[$plugin];
    }

    public function register($hookName, $callable)
    {
        if (!is_callable($callable)) return false;

        $this->handlers[$hookName][] = $callable;

        return true;
    }

    /**
     * 扫描插件
     *
     * @param bool $force
     * @return array
     * @throws
     */
    public function scanAllPlugins(bool $force = false)
    {
        if (!$force && !empty($this->info))
            return $this->info;

        foreach (Dir::getAllDirs(__PLUGIN_DIR__) as $dir) {
            $class = Str::toCamel($dir . '_plugin');

            if (!class_exists($class)) continue;

            $reflect = new \ReflectionClass($class);

            $info = PHPComment::parse($reflect->getDocComment());

            if (empty($info['package'])) continue;

            $this->info[$dir] = $info;
        }

        return $this->info;
    }

    public function trigger(&$var)
    {
        $var = false;

        $this->_plugged = &$var;

        return $this;
    }

    /**
     * 触发钩子
     * 结果以数组形式返回
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $hasRun = false;

        $return = [];

        $hooks = $this->handlers[$name] ?? [];

        foreach ((array)$hooks as $callable) {
            if (is_callable($callable)) {
                $return[] = call_user_func($callable, ...$arguments);
                $hasRun = true;
            }
        }

        $this->_plugged = $hasRun;

        return $return;
    }
}