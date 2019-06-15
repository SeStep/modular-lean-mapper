<?php

namespace SeStep\ModularLeanMapper;

use InvalidArgumentException;
use LeanMapper\Caller;
use LeanMapper\IMapper;
use LeanMapper\Row;
use SeStep\ModularLeanMapper\Exceptions\ModuleNotFound;


class ModularMapper implements IMapper
{
    /** @var IMapper */
    private $mapper;


    private $moduleTableNameGlue = '__';

    /** @var string */
    private $moduleNamespaceMask;
    /** @var string */
    private $moduleEntityMask;
    /** @var string */
    private $moduleRepositoryMask;

    private $modules;
    private $classToTableCache = [];

    public function __construct(IMapper $mapper, string $moduleNamespaceMask, array $modules = [])
    {
        if($moduleNamespaceMask{-1} != '\\') $moduleNamespaceMask .= '\\';

        $this->moduleNamespaceMask = $moduleNamespaceMask;
        $this->modules = $modules;

        $this->setModuleEntitiesNamespace('Model');
        $this->setModuleRepositoryNamespace('Repositories');

        $this->mapper = $mapper;
    }

    /**
     * @param string $entityNamespace - child namespace from the resolved module where the entities are
     */
    public function setModuleEntitiesNamespace(string $entityNamespace)
    {
        $this->moduleEntityMask = $this->moduleNamespaceMask . $entityNamespace . '\\';
    }

    /**
     * @param $repositoryNamespace - child namespace from the resolved module where the repositories are
     */
    public function setModuleRepositoryNamespace($repositoryNamespace)
    {
        $this->moduleRepositoryMask = $this->moduleNamespaceMask . $repositoryNamespace . '\\';
    }

    public function getTable($entityClass): string
    {
        if (!isset($this->classToTableCache[$entityClass])) {
            $modulePattern = str_replace('\\', '\\\\', $this->moduleEntityMask);
            $modulePattern = str_replace('<module>', '(\\w+)', $modulePattern);

            $module = null;

            $matches = [];
            if (preg_match("/$modulePattern/", $entityClass, $matches)) {
                $module = $this->tablePrefixByAppModule($matches[1]);
            }


            $table = ($module ? $module . $this->moduleTableNameGlue : '') . $this->mapper->getTable($entityClass);
            $this->classToTableCache[$entityClass] = $table;
        }


        return $this->classToTableCache[$entityClass];
    }

    public function getEntityClass($table, Row $row = null)
    {
        $parts = explode($this->moduleTableNameGlue, $table, 2);
        if (count($parts) === 2) {
            $module = $this->appModuleByTablePrefix($parts[0]);
            $table = $parts[1];

            $entityClass = ucfirst($this->mapper->getEntityClass($table, $row));
            $entityClass = str_replace('<module>', $module, $this->moduleEntityMask) . $entityClass;
        } else {
            $entityClass = $this->mapper->getEntityClass($table, $row);
        }

        return $entityClass;
    }

    public function getTableByRepositoryClass($repositoryClass)
    {
        $modulePattern = str_replace('\\', '\\\\', $this->moduleRepositoryMask);
        $modulePattern = str_replace('<module>', '(\\w+)', $modulePattern);

        $matches = [];
        $str = '#(^' . $modulePattern . '.*?)?([a-z0-9]+)repository$#i';
        if (!preg_match($str, $repositoryClass, $matches)) {
            throw new InvalidArgumentException("Cannot determine table name for class. '$repositoryClass'");
        }

        $module = $matches[2];


        $table = $this->mapper->getTableByRepositoryClass($repositoryClass);
        if ($module) {
            $table = $this->tablePrefixByAppModule($module) . $this->moduleTableNameGlue . $table;
        }

        return $table;
    }


    private function tablePrefixByAppModule(string $module): string
    {
        foreach ($this->modules as $tm => $am) {
            if ($am == $module) {
                return $tm;
            }
        }

        throw new ModuleNotFound("Module '$module' is not recognized. Did you register it via constructor?");
    }

    private function appModuleByTablePrefix(string $module): string
    {
        if (!isset($this->modules[$module])) {
            throw new ModuleNotFound("Table prefix '$module' does not exist. Did you register it via constructor?");
        }

        return $this->modules[$module];
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
