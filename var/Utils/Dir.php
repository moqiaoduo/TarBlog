<?php
/**
 * Created by tarblog.
 * Date: 2020/7/31
 * Time: 15:21
 */

namespace Utils;

/**
 * @method static bool hasDirs($path = '')
 * @method static int countDirs($path = '')
 * @method static \Core\Dir path($path)
 * @method static array getAllDirs($path = '')
 * @method static array getAllFiles($path = '')
 * @method static array getAllDirsAndFiles($path = '')
 *
 * @see \Core\Dir
 */
class Dir extends \Core\Facade
{
    /**
     * @inheritDoc
     */
    protected static function getFacadeInstanceAlias()
    {
        return 'dir';
    }
}