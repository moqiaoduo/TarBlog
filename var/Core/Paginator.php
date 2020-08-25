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

    /**
     * 是否有上一页
     *
     * @return bool
     */
    public function hasPrev()
    {
        return $this->current > 1;
    }

    /**
     * 是否有下一页
     *
     * @return bool
     */
    public function hasNext()
    {
        return $this->current < $this->getTotalPage();
    }

    /**
     * 自动合成query参数
     *
     * @param $params
     * @return array
     */
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

        $total_page = $this->getTotalPage();
        $curr = $this->current;
        if ($total_page <= 1 || $curr > $total_page || $curr < 1) return; // 页数不足一页，没必要显示

        $mp = $params['more_pages'];

        /**
         * 思路：
         * 1. 先生成两边，若其中一边有越界部分，先考虑移到另一边（优先保证不会越界）。
         * 2. 再根据边缘，判断是否还可以再扩展，根据实际情况进行扩展
         */
        $pre_left_pages = [];
        $pre_right_pages = [];

        $left_pages = [];
        $right_pages = [];

        for ($i = $curr - $mp; $i < $curr; $i++)
            $pre_left_pages[] = $i;

        for ($i = $curr + 1; $i <= $curr + $mp; $i++)
            $pre_right_pages[] = $i;

        foreach ($pre_left_pages as $pre_left_page) {
            if ($pre_left_page > 0) {
                $left_pages[] = $pre_left_page;
            } else {
                if (count($pre_right_pages) > 0 && ($tmp_page = end($pre_right_pages)) < $total_page) {
                    $pre_right_pages[] = $tmp_page + 1;
                }
            }
        }

        foreach ($pre_right_pages as $pre_right_page) {
            if ($pre_right_page <= $total_page) {
                $right_pages[] = $pre_right_page;
            } else {
                if (count($left_pages) > 0 && reset($left_pages) > 1) {
                    array_unshift($left_pages, current($left_pages) - 1);
                }
            }
        }

        reset($left_pages);
        end($right_pages);

        if (($tmp_page = current($left_pages)) > 1) {
            if ($tmp_page > 3) {
                array_unshift($left_pages, '-');
            } elseif ($tmp_page > 2) {
                array_unshift($left_pages, 2);
            }
            array_unshift($left_pages, 1);
        }

        if (count($right_pages) > 0 && ($tmp_page = end($right_pages)) < $total_page) {
            if ($tmp_page + 2 < $total_page) {
                $right_pages[] = '-';
            } elseif ($tmp_page + 1 < $total_page) {
                $right_pages[] = $tmp_page + 1;
            }
            $right_pages[] = $total_page;
        }

        $pages = array_merge($left_pages, [$curr], $right_pages); // 最后进行合并

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
                            <a href="<?php echo $query . $params['query_name'] . '=' . $page ?>"><?php echo $page ?></a>
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