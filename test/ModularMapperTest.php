<?php

namespace Test\SeStep\ModularLeanMapper;


use PHPUnit\Framework\TestCase;
use SeStep\ModularLeanMapper\Exceptions\ModuleNotFound;
use SeStep\ModularLeanMapper\MapperModule;
use SeStep\ModularLeanMapper\ModularMapper;

class ModularMapperTest extends TestCase
{
    /** @var ModularMapper */
    private $service;

    public function setUp(): void
    {
        $modules = [
            'ind__' => 'App\Modules\Industry',
            'cmn__' => new MapperModule('Common\\Model', 'Common\\Services'),
            '' => 'App\Core',
        ];

        $this->service = new ModularMapper(new SimpleMapper(), $modules);
    }

    public function testGetTableByEntity()
    {
        $actual = $this->service->getTable('App\Core\Entity\Tractor');
        self::assertEquals('tractor', $actual);

        $actual = $this->service->getTable('App\Modules\Industry\Entity\Sprinkler');
        self::assertEquals('ind__sprinkler', $actual);
    }

    public function testGetTableByEntityUnrecognizedModule()
    {
        $this->expectException(ModuleNotFound::class);
        $this->service->getTable('App\Modules\Food\Entity\ChocolateBar');
    }

    public function testGetEntityClass()
    {
        $actual = $this->service->getEntityClass('cmn__UserGroup');
        self::assertEquals('Common\Model\UserGroup', $actual);

        $actual = $this->service->getEntityClass('ind__ShovelRack');
        self::assertEquals('App\Modules\Industry\Entity\ShovelRack', $actual);

        $actual = $this->service->getEntityClass('RegistrationLock');
        self::assertEquals('App\Core\Entity\RegistrationLock', $actual);
    }
}
