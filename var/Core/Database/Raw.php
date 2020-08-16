<?php
/**
 * Created by tarblog.
 * Date: 2020/5/23
 * Time: 22:29
 */

namespace Core\Database;

class Raw
{
    private $expression;

    /**
     * Raw constructor.
     * @param $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return mixed
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param mixed $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    public function __toString()
    {
        return $this->getExpression();
    }
}