<?php
/**
 * Created by tarblog.
 * Date: 2020/5/31
 * Time: 22:45
 */

namespace Core;

use Utils\URLGenerator;

/**
 * 支持迭代，因此可以直接使用foreach
 */
class Paginator implements \Iterator
{
    /**
     * 当前页数据
     *
     * @var array
     */
    private $data;

    /**
     * 当前页数
     *
     * @var int
     */
    private $current;

    /**
     * 每页记录数
     *
     * @var int
     */
    private $perPage;

    /**
     * 总记录数
     *
     * @var int
     */
    private $total;

    /**
     * 构造分页类
     *
     * @param array $data
     * @param int $current
     * @param int $perPage
     * @param int $total
     */
    public function __construct($data = [], $current = 1, $perPage = 15, $total = 0)
    {
        $this->data = $data;
        $this->current = $current;
        $this->perPage = $perPage;
        $this->total = $total;
    }

    /**
     * 获取数据
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * 获取当前页数
     *
     * @return int
     */
    public function getCurrent(): int
    {
        return $this->current;
    }

    /**
     * 获取每页条数
     *
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * 获取总记录数
     *
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * 获取总页数
     *
     * @return int
     */
    public function getTotalPage()
    {
        return ceil($this->total / $this->perPage);
    }

    public function hasPrev()
    {
        return $this->current > 1;
    }

    public function hasNext()
    {
        return $this->current < $this->getTotalPage();
    }

    private function combineParameters($params)
    {
        return array_merge(app('request')->get(), $params);
    }

    /**
     * 生成分页视图
     * 直接显示，不会返回值
     *
     * @param array $params 参数，请看代码
     */
    public function view($params = [])
    {
        // 默认参数
        $params = array_merge([
            'more_pages' => 1,          // 由当前页作为中间，左右两边显示多少个页数，默认的1就是 1 ... 3 4 5 ... 7 这样的形式
            'query_name' => 'page',     // 分页get参数的名称
            'text' => '...',            // 省略文字
            'back' => '上一页',         // 上一页文字
            'forward' => '下一页',      // 下一页文字
            'extra_query' => [],        // 额外的query内容，若URL query有就不用手动添加了
            'custom_tpl' => null,       // 自定义模板，相对网站根目录
        ], $params);

        $query_params = $this->combineParameters($params['extra_query']);

        unset($query_params[$params['query_name']]);

        $query = URLGenerator::array2query($query_params, '?', '&');

        // ------- 该段代码为调试所得，并不是用理论去推算的，如有bug请尽快指出 ------
        $total_page = $this->getTotalPage();
        if ($total_page <= 1) return ; // 页数不足一页，没必要显示
        $curr = $this->current;
        $pages = [];
        $mp = $params['more_pages'];
        $mp_2 = 2 * $mp;

        if ($total_page - $curr >= $mp_2) {
            if ($curr == 1) { // 第一页
                for ($page = 1; $page <= $curr + $mp_2; $page++)
                    $pages[] = $page;
            } elseif ($curr > $mp_2) {
                $pages = [1];
                if ($curr - $mp >= 3)
                    $pages[] = '-';
                for ($page = $curr - $mp; $page <= $curr + $mp; $page++)
                    $pages[] = $page;
            } elseif ($curr == $mp_2) {
                for ($page = 1; $page <= $curr + $mp; $page++)
                    $pages[] = $page;
            } else {
                for ($page = 1; $page <= min($total_page, $mp_2 + 1); $page++)
                    $pages[] = $page;
            }

            if ($total_page - $curr > $mp_2)
                $pages = array_merge($pages, ['-', $total_page]);
            elseif ($total_page > $curr)
                $pages[] = $total_page;
        } else {
            if ($curr - $mp >= 3) {
                $pages = [1, '-'];
            } elseif ($curr > 1) {
                for ($page = 1; $page <= min($total_page, $mp); $page++)
                    $pages[] = $page;
            }

            if ($curr >= $total_page - $mp + 1 && $total_page - $mp_2 > 0) {
                for ($page = max($total_page - $mp_2, 2); $page <= $total_page; $page++)
                    $pages[] = $page;
            } elseif ($curr - $mp > 1) {
                for ($page = $curr - $mp; $page <= $total_page; $page++)
                    $pages[] = $page;
            } else {
                for ($page = $curr; $page <= $total_page; $page++)
                    $pages[] = $page;
            }
        }
        // ------- 该段代码为调试所得，并不是用理论去推算的，如有bug请尽快指出 ------

        // 上面那么复杂的代码，都是为了下面的简洁而做出的牺牲
        if (is_null($custom_tpl = $params['custom_tpl'])):
        ?>
        <ul class="paginator">
            <li>
                <a href="<?php echo $curr == 1 ? 'javascript:;' : $query . $params['query_name'] . '=' . ($curr - 1) ?>">
                    <?php echo $params['back'] ?>
                </a>
            </li>
            <?php foreach ($pages as $page): ?>
                <li<?php echo $curr == $page ? ' class="active" ' : '' ?>>
                    <?php if ($page == '-'): ?>
                        ...
                    <?php elseif ($curr == $page):
                        echo $page;
                    else: ?>
                        <a href="<?php echo $query . $params['query_name'] .'=' . $page ?>"><?php echo $page ?></a>
                    <?php endif ?>
                </li>
            <?php endforeach ?>
            <li>
                <a href="<?php echo $curr == $total_page ? 'javascript:;' :
                    $query . $params['query_name'] . '=' . ($curr + 1) ?>">
                    <?php echo $params['forward'] ?>
                </a>
            </li>
        </ul>
        <?php else:
            include __ROOT_DIR__ . '/' . $custom_tpl;
        endif;
    }

    // ------------迭代器实现 start---------------
    public function current()
    {
        return current($this->data);
    }

    public function next()
    {
        return next($this->data);
    }

    public function key()
    {
        return key($this->data);
    }

    public function valid()
    {
        return key($this->data) !== null;
    }

    public function rewind()
    {
        return reset($this->data);
    }
    // ------------迭代器实现 end---------------
}