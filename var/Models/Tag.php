<?php
/**
 * Created by tarblog.
 * Date: 2020/6/7
 * Time: 20:25
 */

namespace Models;

/**
 * @property $mid
 * @property $name
 * @property $slug
 * @property $description
 * @property $count
 * @property $created_at
 * @property $updated_at
 */
class Tag extends Meta
{
    public function __construct(array $data = [])
    {
        $data['type'] = 'tag';

        parent::__construct($data);
    }
}