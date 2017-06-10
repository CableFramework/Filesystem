<?php
namespace Cable\Filesystem\Interfaces;

use League\Flysystem\FilesystemInterface as BaseInterface;

interface FilesystemInterface extends BaseInterface
{
    /**
     * set the chmod to the file
     *
     * @param string $path
     * @param int $mod
     * @return bool
     */
    public function chmod($path, $mod = 0777);

    /**
     * check the file is readable
     *
     * @param string $path
     * @return bool
     */
    public function isReadable($path);

    /**
     * check the file is writeable
     *
     * @param string $path
     * @return bool
     */
    public function isWriteable($path);

    /**
     * create a new file with file path
     *
     * @param string $path
     * @return bool
     */
    public function create($path = '');

    /**
     * check the file
     *
     * @param string $path
     * @return bool
     */
    public function exists($path = '');
}
