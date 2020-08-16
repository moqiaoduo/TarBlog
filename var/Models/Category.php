<?php
/**
 * Created by tarblog.
 * Date: 2020/6/1
 * Time: 17:03
 */

namespace Models;

/**
 *
 * @property $mid
 * @property $name
 * @property $slug
 * @property $description
 * @property $count
 * @property $parent
 * @property $created_at
 * @property $updated_at
 */
class Category extends Meta
{
    public $link;

    public function __construct(array $data = [])
    {
        $data['type'] = 'category';

        parent::__construct($data);

        $this->link = route('category', $data);
    }
}