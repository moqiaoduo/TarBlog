<?php
/**
 * Created by tarblog.
 * Date: 2020/5/28
 * Time: 23:57
 */

namespace Core\Container;

/**
 * 容器管理器类
 *
 * 由于创建独立容器不方便且容易出问题，
 * 故使用该类管理实例
 */
class Manager
{
    /**
     * 自己的实例
     *
     * @var self
     */
    private static $_instance;

    /**
     * 绑定
     *
     * @var array
     */
    private $_bindings = [];

    /**
     * 别名
     *
     * @var array
     */
    private $_alias = [];

    /**
     * 实例
     *
     * @var array
     */
    private $_instances = [];

    /**
     * 绑定自己为app实例
     */
    public function __construct()
    {
        $this->bidingInstance('app', $this);
    }

    /**
     * 获取容器管理类实例
     *
     * @return Manager
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * 添加名称绑定
     *
     * @param string $name
     * @param string $class
     */
    public function addContainerBinding($name, $class)
    {
        $this->_bindings[$name] = $class;

        $this->_alias[$class] = $name;
    }

    /**
     * 添加或更改指定名称实例
     *
     * @param string $name
     * @param object $instance
     */
    public function bidingInstance($name, $instance)
    {
        $this->_instances[$name] = $instance;
    }

    /**
     * 获取实例
     * 这里有自动注入机制，只要是系统设定的类，
     * 都会自动make并作为参数注入进去
     *
     * @param $name
     * @return mixed|null
     */
    public function make($name)
    {
        if (func_num_args() > 1) {
            $params = func_get_args();
            array_shift($params);
        } else {
            $params = [];
        }

        // 实际上，直接绑定对象和绑定类都能被容器管理器正确获取对象
        if (isset($this->_bindings[$name]) || !empty($this->_instances[$name])) {
            if (empty($this->_instances[$name])) { // 如未实例化，先实例化
                $class = $this->_bindings[$name];

                try {
                    // 使用反射探测构造函数
                    $reflect = new \ReflectionClass($class);
                    $constructor = $reflect->getConstructor();
                    $inject_params = [];
                    foreach ($constructor->getParameters() as $parameter) {
                        $type = (string) $parameter->getType();
                        if (is_null($type)) break; // 无类型直接跳过
                        if (array_key_exists($type, $this->_alias)) { // 假如已经存在某个绑定的类
                            $inject_params[] = $this->make($this->_alias[$type]); // 实例化后作为参数注入
                        } elseif ($type == self::class) { // 如果参数为管理类
                            $inject_params[] = $this; // 将管理类作为参数注入
                        } // 其他类型就不管
                    }
                    $params = array_merge($inject_params, $params); // 合并参数
                    $this->_instances[$name] = new $class(...$params);
                }  catch (\ReflectionException $e) {
                    return null;
                }

            }

            return $this->_instances[$name];
        }

        return null;
    }
}