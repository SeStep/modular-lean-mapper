<?php declare(strict_types=1);


namespace SeStep\ModularLeanMapper;


use LeanMapper\Row;

class MapperModule
{
    /** @var string */
    private $entityNamespace;
    /** @var string */
    private $repositoryNamespace;

    public function __construct(string $entityNamespace, string $repositoryNamespace)
    {
        $this->entityNamespace = $entityNamespace;
        $this->repositoryNamespace = $repositoryNamespace;
    }

    public function containsRepo(string $repositoryClass): bool
    {
        return strpos($repositoryClass, $this->repositoryNamespace) !== false;
    }

    public function containsEntity(string $entityClass): bool
    {
        return strpos($entityClass, $this->entityNamespace) !== false;
    }

    public function getEntityClass(string $table, Row $row = null): ?string
    {
        return null;
    }

    /** @return string */
    public function getEntityNamespace(): string
    {
        return $this->entityNamespace;
    }

    /** @return string */
    public function getRepositoryNamespace(): string
    {
        return $this->repositoryNamespace;
    }



    public static function create(
        string $moduleNamespace,
        string $entityNamespace = 'Entity',
        string $repositoryNamespace = 'Repository'
    ): MapperModule {
        return new MapperModule("$moduleNamespace\\$entityNamespace", "$moduleNamespace\\$repositoryNamespace");
    }
}