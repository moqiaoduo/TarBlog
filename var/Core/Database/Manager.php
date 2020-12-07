<?php
/**
 * Created by TarBlog.
 * Date: 2020/4/2
 * Time: 19:48
 */

namespace Core\Database;

use Core\Database\Adapter\SQLBuildAdapter;
use PDO;

class Manager
{
    /**
     * 数据库设置
     *
     * @var array
     */
    protected $configs;

    /**
     * PDO 对象
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * 表前缀
     *
     * @var string
     */
    protected $prefix;

    /**
     * SQL构建适配器
     * 默认为MySQL
     *
     * @var SQLBuildAdapter
     */
    protected $adapter;

    /**
     * 支持的驱动对应适配列表
     *
     * @var array
     */
    protected $d2a = [
        'mysql' => 'Core\Database\Adapter\MySQLAdapter'
    ];

    /**
     * 初始化数据库对象
     *
     * @param array $configs
     */
    public function __construct(array $configs = [])
    {
        $this->config($configs);
    }

    public function config(array $configs)
    {
        $this->configs = $configs;
    }

    public function init()
    {
        $driver = $this->configs['driver'] ?? 'mysql';
        $host = $this->configs['host'] ?? '127.0.0.1';
        $port = $this->configs['port'] ?? '3306';
        $user = $this->configs['user'] ?? 'root';
        $password = $this->configs['password'] ?? '';
        $dbname = $this->configs['database'] ?? 'tarblog';
        $charset = $this->configs['charset'] ?? 'utf8';
        $this->prefix = $this->configs['prefix'] ?? '';

        $this->pdo = new PDO(
            "$driver:dbname=$dbname;host=$host;port=$port;charset=$charset",
            $user,
            $password,
            array(PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_TIMEOUT => 5));
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $adapter = $this->d2a[$driver];

        if (class_exists($adapter))
            $this->adapter = new $adapter;
    }

    public function transaction($callback)
    {
        try {
            $this->beginTransaction();
            $callback();
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
        }
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollback()
    {
        return $this->pdo->rollBack();
    }

    public function lastInsertId($name = null)
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * @param $sql
     * @param array $bindParams
     * @param bool $single
     * @return array|mixed|null
     */
    public function query($sql, $bindParams = [], $single = false)
    {
        $stmt = $this->pdo->prepare($sql);

        if ($stmt->execute($bindParams)) {
            if ($single)
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null; // 如果不做判断 返回的有可能是false

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($single) return null;

        return [];
    }

    public function exec($sql, $bindParams = [], $returnRowCount = false)
    {
        $stmt = $this->pdo->prepare($sql);

        $bool = $stmt->execute($bindParams);

        return $returnRowCount && $bool ? $stmt->rowCount() : $bool;
    }

    public function table($table, $as = '')
    {
        return new Query($this, $table, $as);
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * @param PDO $pdo
     */
    public function setPdo(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return SQLBuildAdapter
     */
    public function getAdapter(): SQLBuildAdapter
    {
        return $this->adapter;
    }
}