<?php


namespace Test\SeStep\ModularLeanMapper;


use PHPUnit\Framework\TestCase;
use SeStep\ModularLeanMapper\MapperModule;

class MapperModuleTest extends TestCase
{
    public function testContainsEntity()
    {
        $module = new MapperModule('App\Entity', 'App\Repo');

        $this->assertTrue($module->containsEntity('App\Entity\Role'));
        $this->assertFalse($module->containsEntity('App\Subfolder\Entity\Role'));
    }

    public function testContainsRepo()
    {
        $module = new MapperModule('App\Entity', 'App\Repo');

        $this->assertTrue($module->containsRepo('App\Repository\RoleRepo'));
        $this->assertFalse($module->containsRepo('App\Subfolder\Repository\RoleRepo'));
    }

}