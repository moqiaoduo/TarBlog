<?php
/**
 * Created by tarblog.
 * Date: 2020/6/2
 * Time: 1:00
 */

namespace Core\Http;

class Session
{
    private $data;

    private $flash = [];

    private $old_flash;

    public function __construct()
    {
        $this->reload();
    }

    public function reload()
    {
        session_start();
        $this->old_flash = $_SESSION['__flash__'] ?? [];
        unset($_SESSION['__flash__']);
        $this->data = $_SESSION;
        session_commit();
    }

    public function save()
    {
        session_start();
        $_SESSION = array_merge($this->data, ['__flash__' => $this->flash]);
        session_commit();
    }

    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->data) ?
            $this->data[$key] :
            (array_key_exists($key, $this->flash) ?
                $this->flash[$key] :
                (array_key_exists($key, $this->old_flash) ? $this->old_flash[$key] : $default));
    }

    public function set($key, $value, $saveNow = false)
    {
        $this->data[$key] = $value;

        if ($saveNow) {
            session_start();
            $_SESSION[$key] = $value;
            session_commit();
        }
    }

    public function pop($key, $default = null)
    {
        $data = $this->get($key, $default);

        unset($this->data[$key]);

        return $data;
    }

    public function flash($key, $value)
    {
        $this->flash[$key] = $value;
    }

    public function delete($key)
    {
        unset($this->data[$key]);
    }
}