<?php
namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\HttpOptions;

abstract class ApiTestCase extends KernelTestCase
{
    //hemos hecho override del metodo browser porque esta clase hereda de KernelTestCase
    protected function browser(array $options = [], array $server = [])
    {
        return $this->baseKernelBrowser($options, $server)
            ->setDefaultHttpOptions(
                HttpOptions::create()
                    ->withHeader('Accept', 'application/ld+json')

            );
    }
}