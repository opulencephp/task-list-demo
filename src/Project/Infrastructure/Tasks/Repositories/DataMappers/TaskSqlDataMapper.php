<?php
namespace Project\Infrastructure\Tasks\Repositories\DataMappers;

use Opulence\Orm\DataMappers\SqlDataMapper;
use Opulence\Orm\OrmException;
use PDO;
use PDOException;
use Project\Infrastructure\Tasks\Task;

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
        try {
            $statement = $this->writeConnection->prepare('INSERT INTO tasks (text) VALUES (:text)');
            $statement->bindValues([
                'text' => $task->getText()
            ]);
            $statement->execute();
        } catch (PDOException $ex) {
            throw new OrmException('Failed to add task', 0, $ex);
        }
    }

    /**
     * Deletes an entity
     *
     * @param Task $task The entity to delete
     * @throws OrmException Thrown if the entity couldn't be deleted
     */
    public function delete($task)
    {
        try {
            $statement = $this->writeConnection->prepare('DELETE FROM tasks WHERE id = :id');
            $statement->bindValues([
                'id' => [$task->getId(), PDO::PARAM_INT]
            ]);
            $statement->execute();
        } catch (PDOException $ex) {
            throw new OrmException('Failed to delete task', 0, $ex);
        }
    }

    /**
     * Gets all the entities
     *
     * @return Task[] The list of all the entities
     * @throws OrmException Thrown if you're expecting expecting a single result, but there wasn't one
     */
    public function getAll() : array
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
        return $this->read(
            'SELECT id, text FROM tasks WHERE id = :id',
            ['id' => [$id, PDO::PARAM_INT]],
            self::VALUE_TYPE_ENTITY
        );
    }

    /**
     * Saves any changes made to an entity
     *
     * @param Task $task The entity to save
     * @throws OrmException Thrown if the entity couldn't be saved
     */
    public function update($task)
    {
        try {
            $statement = $this->writeConnection->prepare('UPDATE tasks SET text = :text WHERE id = :id');
            $statement->bindValues([
                'id' => [$task->getId(), PDO::PARAM_INT],
                'text' => $task->getText()
            ]);
            $statement->execute();
        } catch (PDOException $ex) {
            throw new OrmException('Failed to update task', 0, $ex);
        }
    }

    /**
     * Loads an entity from a hash of data
     *
     * @param array $hash The hash of data to load the entity from
     * @return Task The entity
     */
    protected function loadEntity(array $hash)
    {
        return new Task((int)$hash['id'], $hash['text']);
    }
}
