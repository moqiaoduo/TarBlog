<?php
/**
 * Created by TarBlog.
 * Date: 2020/4/3
 * Time: 19:34
 */

namespace Core\Plugin;

/**
 * 插件主体必须继承该抽象类，否则不会被识别为插件
 */
abstract class Plugin
{
    /**
     * 管理器
     *
     * @var Manager
     */
    protected $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * 注册钩子
     * 只有启用的插件会调用这个方法
     *
     * @return void
     */
    public abstract function register();

    /**
     * 激活插件前的动作
     * 返回false或空值会导致激活失败
     * 返回true则会正常启用插件
     * 注意，该方法仅在启用时调用一次，后续不会再调用
     *
     * @return bool
     */
    public function activating()
    {
        return true;
    }

    /**
     * 激活插件后的动作
     *
     * @return void
     */
    public function activated() {}

    /**
     * 禁用插件前的动作
     * 返回false或空值会导致禁用失败
     * 返回true则会正常禁用插件
     *
     * @return bool
     */
    public function deactivating()
    {
        return true;
    }

    /**
     * 禁用插件后的动作
     *
     * @return void
     */
    public function deactivated() {}
}