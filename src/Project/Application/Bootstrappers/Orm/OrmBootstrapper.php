<?php
namespace Project\Application\Bootstrappers\Orm;

use Opulence\Databases\ConnectionPools\ConnectionPool;
use Opulence\Databases\IConnection;
use Opulence\Ioc\Bootstrappers\Bootstrapper;
use Opulence\Ioc\Bootstrappers\ILazyBootstrapper;
use Opulence\Ioc\IContainer;
use Opulence\Ioc\IocException;
use Opulence\Orm\ChangeTracking\ChangeTracker;
use Opulence\Orm\ChangeTracking\IChangeTracker;
use Opulence\Orm\EntityRegistry;
use Opulence\Orm\Ids\Accessors\IdAccessorRegistry;
use Opulence\Orm\Ids\Accessors\IIdAccessorRegistry;
use Opulence\Orm\Ids\Generators\IdGeneratorRegistry;
use Opulence\Orm\Ids\Generators\IIdGeneratorRegistry;
use Opulence\Orm\Ids\Generators\IntSequenceIdGenerator;
use Opulence\Orm\IUnitOfWork;
use Opulence\Orm\Repositories\IRepository;
use Opulence\Orm\Repositories\Repository;
use Opulence\Orm\UnitOfWork;
use Project\Application\Http\Controllers\Tasks;
use Project\Infrastructure\Tasks\Repositories\DataMappers\TaskSqlDataMapper;
use Project\Infrastructure\Tasks\Task;
use RuntimeException;

/**
 * Defines the ORM bootstrapper
 */
class OrmBootstrapper extends Bootstrapper implements ILazyBootstrapper
{
    /**
     * @inheritdoc
     */
    public function getBindings() : array
    {
        return [
            IChangeTracker::class,
            IIdAccessorRegistry::class,
            IIdGeneratorRegistry::class,
            IUnitOfWork::class,
            [IRepository::class => Tasks::class]
        ];
    }

    /**
     * @inheritdoc
     */
    public function registerBindings(IContainer $container)
    {
        try {
            $idAccessorRegistry = new IdAccessorRegistry();
            $idGeneratorRegistry = new IdGeneratorRegistry();
            $this->registerIdAccessors($idAccessorRegistry);
            $this->registerIdGenerators($idGeneratorRegistry);
            $changeTracker = new ChangeTracker();
            $entityRegistry = new EntityRegistry($idAccessorRegistry, $changeTracker);
            $unitOfWork = new UnitOfWork(
                $entityRegistry,
                $idAccessorRegistry,
                $idGeneratorRegistry,
                $changeTracker,
                $container->resolve(IConnection::class)
            );
            $this->bindRepositories($container, $unitOfWork);
            $container->bindInstance(IIdAccessorRegistry::class, $idAccessorRegistry);
            $container->bindInstance(IIdGeneratorRegistry::class, $idGeneratorRegistry);
            $container->bindInstance(IChangeTracker::class, $changeTracker);
            $container->bindInstance(IUnitOfWork::class, $unitOfWork);
        } catch (IocException $ex) {
            throw new RuntimeException('Failed to register ORM bindings', 0, $ex);
        }
    }

    /**
     * Binds repositories to the container
     *
     * @param IContainer $container The container to bind to
     * @param IUnitOfWork $unitOfWork The unit of work to use in repositories
     * @throws IocException Thrown if there was an error resolving the connection pool
     * @throws RuntimeException Thrown if there was an error getting the connections from the pool
     */
    private function bindRepositories(IContainer $container, IUnitOfWork $unitOfWork)
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = $container->resolve(ConnectionPool::class);
        $dataMapper = new TaskSqlDataMapper(
            $connectionPool->getReadConnection(),
            $connectionPool->getWriteConnection()
        );
        $repository = new Repository(Task::class, $dataMapper, $unitOfWork);

        // Bind this just for the task controller
        $container->for(Tasks::class, function (IContainer $container) use ($repository) {
            $container->bindInstance(IRepository::class, $repository);
        });
    }

    /**
     * Registers Id getters/setters for classes managed by the unit of work
     *
     * @param IIdAccessorRegistry $idAccessorRegistry The Id accessor registry
     */
    private function registerIdAccessors(IIdAccessorRegistry $idAccessorRegistry)
    {
        // Register your Id getters/setters for classes that will be managed by the unit of work
    }

    /**
     * Registers Id generators for classes managed by the unit of work
     *
     * @param IIdGeneratorRegistry $idGeneratorRegistry The Id generator registry
     */
    private function registerIdGenerators(IIdGeneratorRegistry $idGeneratorRegistry)
    {
        $idGeneratorRegistry->registerIdGenerator(Task::class, new IntSequenceIdGenerator('id_seq'));
    }
}
