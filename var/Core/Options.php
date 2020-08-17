<?php
/**
 * Created by TarBlog.
 * Date: 2020/4/3
 * Time: 11:29
 */

namespace Core;

use Core\Database\Manager as Database;

/**
 * 通过->和[]方式获取数据的区别：
 * 通过->只能获取uid=0（系统）的配置
 * 通过[]直接指定uid，例如：
 * $options[0]['siteName']，
 * $options[1]['addon_setting']
 * 其实是两种不同获取数据的方式
 * 当然，实际都是通过get方法来取的
 */
class Options implements \ArrayAccess
{
    /**
     * options表缓存记录
     *
     * @var array
     */
    protected $options = [];

    /**
     * 待写入数据库的项
     *
     * @var array
     */
    protected $need_write = [];

    /**
     * 数据库组件
     *
     * @var Database
     */
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function get($key, $default = null, $uid = 0)
    {
        // 读缓存
        if (isset($this->options[$uid]) && array_key_exists($key, $this->options[$uid])) {
            return $this->options[$uid][$key];
        }

        // 读数据库
        $data = $this->db->table('options')->where('user', $uid)
            ->where('name', $key)->first();
        if (is_null($data)) return $default;
        return $data['value'];
    }

    public function gets($keys, $uid = 0)
    {
        // 直接从数据库读入，不读缓存，且不写入缓存
        return $this->db->table('options')->where('user', $uid)
            ->whereIn('name', $keys)->pluck('value', 'name');
    }

    public function set($key, $value, $uid = 0, $saveNow = false)
    {
        $this->options[$uid][$key] = $value;

        if ($saveNow)
            $this->save([$uid => [$key]]);
        else
            isset($this->need_write[$uid]) && in_array($key, $this->need_write[$uid]) ?:
                $this->need_write[$uid][] = $key;
    }

    public function save($keys = [])
    {
        if (empty($keys))
            $keys = $this->need_write;

        foreach ($keys as $uid => $ks) {
            foreach ($ks as $key) {
                $value = $this->options[$uid][$key];
                if ($this->db->table('options')->where('name', $key)
                    ->where('user', $uid)->exists())
                    $this->db->table('options')->where('name', $key)
                        ->where('user', $uid)->update(['value' => $value]);
                else
                    $this->db->table('options')->insert(['name' => $key, 'value' => $value, 'user' => $uid]);
            }
        }
    }

    public function title()
    {
        echo $this->get('siteName', 'TarBlog');
    }

    public function siteUrl($ext = '')
    {
        echo $this->get('siteUrl') . (substr($ext, 0, 1) == '/' ? $ext : '/' . $ext);
    }

    /**
     * 输出后台路径
     */
    public function adminUrl()
    {
        $this->siteUrl(__ADMIN_DIR__);
    }

    /**
     * 获取个人档案地址（后台）
     */
    public function profileUrl()
    {
        $this->siteUrl(__ADMIN_DIR__ . 'user-editor.php');
    }

    /**
     * 登录URL
     */
    public function loginUrl()
    {
        $this->siteUrl(__ADMIN_DIR__ . 'login.php');
    }

    /**
     * 登出URL
     */
    public function logoutUrl()
    {
        $this->siteUrl(__ADMIN_DIR__ . 'logout.php');
    }

    /**
     * 注册URL
     */
    public function registerUrl()
    {
        $this->siteUrl(__ADMIN_DIR__ . 'register.php');
    }

    /**
     * 魔术方法，用于通过访问成员变量的方式读取option
     *
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    public function __call($name, $arguments)
    {
        echo $this->get($name, ...$arguments);
    }

    /**
     * 判断某一组options是否存在
     * 这里只能取缓存来判断，并不会访问数据库
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->options[$offset]);
    }

    /**
     * 获取某一组options
     * 这里也是只能读缓存
     *
     * @param mixed $offset
     * @return array|mixed
     */
    public function offsetGet($offset)
    {
        return $this->options[$offset] ?? [];
    }

    public function offsetSet($offset, $value)
    {
        // No Action
    }

    public function offsetUnset($offset)
    {
        // No Action
    }
}