<?php

namespace Cable\Filesystem;


use Cable\Container\ArgumentException;
use Cable\Container\ExpectationException;
use Cable\Container\NotFoundException;
use Cable\Container\ServiceProvider;
use Cable\Filesystem\Interfaces\AwsDriverInterface;
use Cable\Filesystem\Interfaces\FtpDriverInterface;
use Cable\Filesystem\Interfaces\LocalDriverInterface;
use Cable\Filesystem\Interfaces\RackspaceInterface;
use Cable\Filesystem\Interfaces\S3DriverInterface;
use League\Flysystem\Config;

class StorageServiceProvider extends ServiceProvider
{

    /**
     * register new providers or something
     *
     * @return mixed
     */
    public function boot()
    {
    }

    /**
     * register the content
     *
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ExpectationException
     * @throws \ReflectionException
     *
     * @return mixed
     */
    public function register()
    {
        // save storage
         $this->getContainer()
             ->singleton('storage', function (){
                 $config = $this->getContainer()->make(Config::class);

                return  new Storage($config->get('storage'));
             });


         // save drivers
         $this->getContainer()->make('storage.local', function (){
            return $this->getContainer()->make('storage')->driver('local');
         });


        $this->getContainer()->make('storage.aws', function (){
            return $this->getContainer()->make('storage')->driver('aws');
        });

        $this->getContainer()->make('storage.s3', function (){
            return $this->getContainer()->make('storage')->driver('s3');
        });

        $this->getContainer()->make('storage.rackspace', function (){
            return $this->getContainer()->make('storage')->driver('rackspace');
        });

        $this->getContainer()->make('storage.ftp',function (){
            return $this->getContainer()->make('storage')->driver('ftp');
        });

        // add aliases

        $this->getContainer()
            ->alias(AwsDriverInterface::class, 'storage.aws')
            ->alias(S3DriverInterface::class, 'storage.s3')
            ->alias(RackspaceInterface::class, 'storage.rackspace')
            ->alias(LocalDriverInterface::class, 'storage.local')
            ->alias(FtpDriverInterface::class, 'storage.ftp');

    }
}
