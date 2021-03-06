<?php
/**
 * Bu Dosya AnonymFramework'e ait bir dosyadır.
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 */

namespace Cable\Filesystem;

use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemNotFoundException;
use League\Flysystem\Rackspace\RackspaceAdapter;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Filesystem as FlySystem;
use League\Flysystem\NotSupportedException;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\FilesystemInterface;
use OpenCloud\Rackspace;
use Aws\S3\S3Client;

/**
 * Class Filesystem
 * @package Anonym\Filesystem
 */
class Storage
{

    /**
     * Ayarları tutar
     *
     * @var array
     */
    private $config = [
        'local' => ['root' => __DIR__]
    ];

    /**
     * @var array
     */
    private $driverList = [
        'local' => 'local',
        'aws' => 'aws',
        'ftp' => 'ftp',
        'rackspace' => 'rackspace'
    ];


    /**
     * Öntanımlı sürücüyü döndürür
     *
     * @var string
     */
    private $defaultDriver = 'local';


    /**
     * Storage constructor.
     * @param array $configs
     */
    public function __construct($configs = array())
    {
        if ( !empty($configs)) {
            $this->setConfig($configs);
        }

    }
    /**
     * Sürücü seçimi yapar
     *
     * @param null $driver
     * @return Adapter|bool
     */
    public function disk($driver = null)
    {
        return $this->driver($driver);
    }

    /**
     * Sürücü Seçimi yapar
     *
     * @param null $driver
     * @return Adapter|bool
     */
    public function driver($driver = null)
    {
        return $this->selectDriver($driver);
    }

    /**
     * add a new driver
     *
     * @param string $name
     * @param callable $driver
     * @return $this
     */
    public function add($name = '',callable $driver = null)
    {
        $this->driverList[$name] = $driver;
        return $this;
    }

    /**
     * @param string $add
     * @param callable $driver
     * @return Storage
     */
    public function extend($add,callable $driver)
    {
        return $this->add($add, $driver);
    }

    /**
     * Sürücü seçimi yapar
     *
     * @param null $driver
     * @return bool|FilesystemAdapter
     */
    public function selectDriver($driver = null)
    {
        if (null === $driver) {
            $driver = $this->getDefaultDriver();
        }

        $driver = $this->findDriver($driver);

        if ($driver instanceof FilesystemInterface) {
            return $this->adapter($driver);
        } else {
            throw new NotSupportedException(sprintf('%s sınıfınız desteklenen bir sürücü değil', get_class($driver)));
        }
    }

    /**
     * Sürücüyü bulur
     *
     * @param string $driver
     * @throws DriverNotFoundException
     * @return mixed
     */
    private function findDriver($driver = '')
    {
        if (is_string($driver) && isset($this->driverList[$driver])) {
            $driver = $this->driverList[$driver];

            if (is_callable($driver)) {
                $response = $driver();
            } else {
                $callableName = "create" . ucfirst($driver) . "Driver";
                $response = call_user_func([$this, $callableName]);
            }
        } else {
            throw new DriverNotFoundException(
                sprintf(
                    '%s adında bir sürücü bulunamadı',
                    $driver)
            )
            ;
        }


        if (!$response instanceof FilesystemInterface) {
            throw new FilesystemNotFoundException(sprintf('Your callback must return a %s instnace', 'League\Flysystem\FilesystemInterface'));
        }

        return $response;
    }


    /**
     * put the instance to adapter
     *
     * @param FilesystemInterface $response
     * @return Adapter
     */
    private function adapter($response)
    {
        return new Adapter($response);
    }

    /**
     * @return array
     */
    public function getDriverList()
    {
        return $this->driverList;
    }

    /**
     * @param array $driverList
     * @return Filesystem
     */
    public function setDriverList($driverList)
    {
        $this->driverList = $driverList;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->defaultDriver;
    }

    /**
     * @param string $defaultDriver
     * @return Filesystem
     */
    public function setDefaultDriver($defaultDriver)
    {
        $this->defaultDriver = $defaultDriver;
        return $this;
    }

    /**
     * Aws Sürücüsünü oluşturur
     *
     *
     * @return FlySystem
     */
    protected function createAwsDriver()
    {
        $configs = $this->getConfig()['aws'];
        $bucket = isset($configs['bucket']) ? $configs['bucket'] : 'AnonymFrameworkAwsBucket';
        return new FlySystem(new AwsS3Adapter(new S3Client($configs), $bucket));
    }

    /**
     * Yerel sürücüyü oluşturur
     *
     * @return FlySystem
     */
    protected function createLocalDriver()
    {
        return new FlySystem(new LocalAdapter($this->getConfig()['local']['root']));
    }

    /**
     * Ftp sürücünü oluşturur
     *
     * @return FlySystem
     */
    protected function createFtpDriver()
    {

        return new FlySystem(new FtpAdapter($this->getConfig()['ftp']));
    }

    /**
     *    Create an instance of the Rackspace driver.
     *
     * @return RackspaceAdapter
     * */
    protected function createRackspaceDriver()
    {

        $config = $this->getConfig()['rackspace'];
        $client = new Rackspace($config['endpoint'], [
            'username' => $config['username'], 'apiKey' => $config['key'],
        ]);


        return new Flysystem(
            new RackspaceAdapter($this->getRackspaceContainer($client, $config))
        );
    }

    /**
     * Rackspace container oluştrurur
     *
     * @param Rackspace $client
     * @param array $config
     * @return mixed
     */
    protected function getRackspaceContainer(Rackspace $client, array $config)
    {
        $urlType = $config['url_type'];
        $store = $client->objectStoreService('cloudFiles', $config['region'], $urlType);
        return $store->getContainer($config['container']);
    }


    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     * @return Filesystem
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param string $driver
     * @param string $rootPath
     * @return Storage
     */
    public function root($driver, $rootPath)
    {
        $this->config[$driver]['root'] = $rootPath;
    }

    /**
     * call the method from default driver
     *
     * @param string $name the name of method
     * @param array $args the parameters for method
     * @return mixed
     */
    public function __call($name, $args = [])
    {
        return call_user_func_array([$this->disk($this->getDefaultDriver()), $name], $args);
    }
}
