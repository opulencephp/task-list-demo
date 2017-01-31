<?php
namespace Project\Infrastructure\Repositories\DataMappers;

use Opulence\Orm\DataMappers\SqlDataMapper;
use Opulence\Orm\OrmException;
use PDO;
use Project\Infrastructure\Task;

class TaskSqlDataMapper extends SqlDataMapper
{

    /**
     * Adds an entity to the database
     *
     * @param Task $task The entity to add
     * @throws OrmException Thrown if the entity couldn't be added
     */
    public function add($task)
    {
        $statement = $this->writeConnection->prepare('INSERT INTO tasks (id, text) VALUES (:id, :text)');
        $statement->bindValues([
            'id' => [$task->getId, PDO::PARAM_INT],
            'text' => $task->getText
        ]);
        $statement->execute();
    }

    /**
     * Deletes an entity
     *
     * @param Task $task The entity to delete
     * @throws OrmException Thrown if the entity couldn't be deleted
     */
    public function delete($task)
    {
        $statement = $this->writeConnection->prepare('DELETE FROM tasks WHERE id = :id');
        $statement->bindValues([
            'id' => [$task->getId, PDO::PARAM_INT]
        ]);
        $statement->execute();
    }

    /**
     * Gets all the entities
     *
     * @return Task[] The list of all the entities
     */
    public function getAll(): array
    {
        return $this->read('SELECT id, text FROM tasks', [], self::VALUE_TYPE_ARRAY);
    }

    /**
     * Gets the entity with the input Id
     *
     * @param int|string $id The Id of the entity we're searching for
     * @return Task The entity with the input Id
     * @throws OrmException Thrown if there was no entity with the input Id
     */
    public function getById($id)
    {
        return $this->read('SELECT id, text FROM tasks WHERE id = :id', ['id' => [$id, PDO::PARAM_INT]], self::VALUE_TYPE_ENTITY);
    }

    /**
     * Saves any changes made to an entity
     *
     * @param Task $task The entity to save
     * @throws OrmException Thrown if the entity couldn't be saved
     */
    public function update($task)
    {
        $statement = $this->writeConnection->prepare('UPDATE tasks SET text = :text WHERE id = :id');
        $statement->bindValues([
            'id' => [$task->getId(), PDO::PARAM_INT],
            'text' => $task->getText()
        ]);
        $statement->execute();
    }

    /**
     * Loads an entity from a hash of data
     *
     * @param array $hash The hash of data to load the entity from
     * @return Task The entity
     */
    protected function loadEntity(array $hash)
    {
        return new Task((int)$hash["id"], $hash["text"]);
    }
}
