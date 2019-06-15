<?php

namespace Test\SeStep\ModularLeanMapper;


use PHPUnit\Framework\TestCase;
use SeStep\ModularLeanMapper\Exceptions\ModuleNotFound;
use SeStep\ModularLeanMapper\ModularMapper;

class ModularUnderscoreMapperTest extends TestCase
{
    /** @var ModularMapper */
    private $service;

    public function setUp(): void
    {
        $modules = [
            'ind' => 'Industry',
            'cmn' => 'Common',
        ];

        $this->service = new ModularMapper(new SimpleMapper(), 'App\\Modules\\<module>\\', $modules);
    }

    public function testGetTableByEntity()
    {
        $actual = $this->service->getTable('App\Core\Model\Tractor');
        self::assertEquals('tractor', $actual);

        $actual = $this->service->getTable('App\Modules\Industry\Model\Sprinkler');
        self::assertEquals('ind__sprinkler', $actual);
    }

    public function testGetTableByEntityUnrecognizedModule()
    {
        $this->expectException(ModuleNotFound::class);
        $this->service->getTable('App\Modules\Food\Model\ChocolateBar');
    }

    public function testGetEntityClass()
    {
        $actual = $this->service->getEntityClass('cmn__UserGroup');
        self::assertEquals('App\Modules\Common\Model\UserGroup', $actual);

        $actual = $this->service->getEntityClass('ind__ShovelRack');
        self::assertEquals('App\Modules\Industry\Model\ShovelRack', $actual);
    }
}
