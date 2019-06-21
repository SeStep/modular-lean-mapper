<?php declare(strict_types=1);

namespace SeStep\ModularLeanMapper;

use InvalidArgumentException;
use LeanMapper\Caller;
use LeanMapper\IMapper;
use LeanMapper\Row;
use Nette\Caching\Cache;
use Nette\Caching\Storages\MemoryStorage;
use SeStep\ModularLeanMapper\Exceptions\ModuleNotFound;
use UnexpectedValueException;


final class ModularMapper implements IMapper
{
    /** @var IMapper */
    private $mapper;

    /** @var MapperModule[] */
    private $modules;

    /** @var Cache */
    private $cache;

    /**
     * ModularMapper constructor.
     * @param IMapper $mapper
     * @param MapperModule[]|string[] $modules
     * @param Cache|null $cache
     */
    public function __construct(IMapper $mapper, array $modules = [], Cache $cache = null)
    {
        $this->mapper = $mapper;
        $this->cache = $cache ?: new Cache(new MemoryStorage());
        $this->modules = [];

        foreach ($modules as $prefix => $module) {
            if (!is_string($prefix)) {
                throw new InvalidArgumentException("All modules must have a string prefix. '$prefix' given");
            }

            if ($module instanceof MapperModule) {
                $this->modules[$prefix] = $module;
                continue;
            }

            if (is_string($module)) {
                $this->modules[$prefix] = MapperModule::create($module);
                continue;
            }

            $type = is_object($module) ? get_class($module) : gettype($module);
            throw new UnexpectedValueException("Module must be either string or " . MapperModule::class . ". Got: $type");
        }
    }

    public function getTable($entityClass): string
    {
        return $this->cache->load('entityToTable-' . $entityClass, function () use ($entityClass) {
            $tablePrefix = null;

            foreach ($this->modules as $prefix => $module) {
                if (!$module->containsEntity($entityClass)) {
                    continue;
                }

                $tablePrefix = $prefix;
                break;
            }

            if (is_null($tablePrefix)) {
                throw new ModuleNotFound();
            }

            return $tablePrefix . $this->mapper->getTable($entityClass);
        });
    }

    public function getEntityClass($table, Row $row = null)
    {
        $module = $this->findModuleByTable($table, $nonPrefixedTable);

        if ($module) {
            if ($entityClass = $module->getEntityClass($table, $row)) {
                return $entityClass;
            }

            return $module->getEntityNamespace() . '\\' . $this->mapper->getEntityClass($nonPrefixedTable, $row);
        }

        return $this->mapper->getEntityClass($table, $row);
    }

    public function getTableByRepositoryClass($repositoryClass)
    {
        return $this->cache->load('repoToTable-' . $repositoryClass, function () use ($repositoryClass) {
            $tablePrefix = '';

            foreach ($this->modules as $prefix => $module) {
                if (!$module->containsRepo($repositoryClass)) {
                    continue;
                }

                $tablePrefix = $prefix;
                break;
            }

            return $tablePrefix . $this->mapper->getTableByRepositoryClass($repositoryClass);
        });
    }

    private function findModuleByTable(string $table, &$nonPrefixedTable = null): ?MapperModule
    {
        $key = $this->cache->load('tableToModuleKey-' . $table, function () use ($table) {
            foreach ($this->modules as $prefix => $module) {
                if (empty($prefix) || strpos($table, $prefix) === 0) {
                    return $prefix;
                }
            }
            return null;
        });

        if (!is_null($key)) {
            $nonPrefixedTable = substr($table, strlen($key));
            return $this->modules[$key];
        } else {
            return null;
        }
    }

    public function getRelationshipColumn($sourceTable, $targetTable)
    {
        return $this->mapper->getRelationshipColumn($sourceTable, $targetTable);
    }

    public function getPrimaryKey($table)
    {
        return $this->mapper->getPrimaryKey($table);
    }

    public function getColumn($entityClass, $field)
    {
        return $this->mapper->getColumn($entityClass, $field);
    }

    public function getEntityField($table, $column)
    {
        return $this->mapper->getEntityField($table, $column);
    }

    public function getRelationshipTable($sourceTable, $targetTable)
    {
        return $this->mapper->getRelationshipTable($sourceTable, $targetTable);
    }

    public function getImplicitFilters($entityClass, Caller $caller = null)
    {
        return $this->mapper->getImplicitFilters($entityClass, $caller);
    }
}
