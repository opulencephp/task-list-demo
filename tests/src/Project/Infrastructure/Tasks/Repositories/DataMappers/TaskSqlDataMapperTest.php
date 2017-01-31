<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */
namespace Project\Tests\Infrastructure\Tasks\Repositories\DataMappers;

use Opulence\Databases\IConnection;
use Opulence\Databases\IStatement;
use Opulence\Orm\OrmException;
use PDO;
use PDOException;
use Project\Infrastructure\Tasks\Repositories\DataMappers\TaskSqlDataMapper;
use Project\Infrastructure\Tasks\Task;

/**
 * Tests the task SQL data mapper
 */
class TaskSqlDataMapperTest extends \PHPUnit\Framework\TestCase
{
    /** @var IConnection|\PHPUnit_Framework_MockObject_MockObject The mocked write connection */
    private $readConnection;
    /** @var IConnection|\PHPUnit_Framework_MockObject_MockObject The mocked write connection */
    private $writeConnection;
    /** @var IStatement|\PHPUnit_Framework_MockObject_MockObject The mocked database statement */
    private $statement;
    /** @var TaskSqlDataMapper The data mapper to test */
    private $dataMapper;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->readConnection = $this->createMock(IConnection::class);
        $this->writeConnection = $this->createMock(IConnection::class);
        $this->statement = $this->createMock(IStatement::class);
        $this->dataMapper = new TaskSqlDataMapper($this->readConnection, $this->writeConnection);
    }

    /**
     * Tests that adding a task creates the correct query
     */
    public function testAddingTaskCreatesCorrectQuery() : void
    {
        $task = new Task(-1, 'foo');
        $this->setUpMockedConnectionAndStatement(
            $this->writeConnection,
            'INSERT INTO tasks (text) VALUES (:text)',
            [
                'text' => 'foo'
            ]
        );
        $this->dataMapper->add($task);
    }

    /**
     * Tests that deleting a task creates the correct query
     */
    public function testDeletingTaskCreatesCorrectQuery() : void
    {
        $task = new Task(1, 'foo');
        $this->setUpMockedConnectionAndStatement(
            $this->writeConnection,
            'DELETE FROM tasks WHERE id = :id',
            [
                'id' => [1, PDO::PARAM_INT]
            ]
        );
        $this->dataMapper->delete($task);
    }

    /**
     * Tests that getting all tasks creates the correct query
     */
    public function testGettingAllTaskCreatesCorrectQuery() : void
    {
        $this->setUpMockedConnectionAndStatement(
            $this->readConnection,
            'SELECT id, text FROM tasks',
            []
        );
        $this->statement->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([
                ['id' => 1, 'text' => 'foo'],
                ['id' => 2, 'text' => 'bar']
            ]);
        /** @var Task[] $actualTasks */
        $actualTasks = $this->dataMapper->getAll();
        $this->assertCount(2, $actualTasks);
        $this->assertEquals(1, $actualTasks[0]->getId());
        $this->assertEquals('foo', $actualTasks[0]->getText());
        $this->assertEquals(2, $actualTasks[1]->getId());
        $this->assertEquals('bar', $actualTasks[1]->getText());
    }

    /**
     * Tests that getting a task by Id creates the correct query
     */
    public function testGettingByIdTaskCreatesCorrectQuery() : void
    {
        $this->setUpMockedConnectionAndStatement(
            $this->readConnection,
            'SELECT id, text FROM tasks WHERE id = :id',
            ['id' => [1, PDO::PARAM_INT]]
        );
        $this->statement->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([
                ['id' => 1, 'text' => 'foo']
            ]);
        /** @var Task $actualTask */
        $actualTask = $this->dataMapper->getById(1);
        $this->assertEquals(1, $actualTask->getId());
        $this->assertEquals('foo', $actualTask->getText());
    }

    /**
     * Tests that PDO exception is rethrown as an ORM exception during an add
     */
    public function testPdoExceptionWhileAddingTaskIsRethrownAsOrmException() : void
    {
        $this->expectException(OrmException::class);
        $this->writeConnection->method('prepare')
            ->willThrowException(new PDOException());
        $this->dataMapper->add(new Task(-1, 'foo'));
    }

    /**
     * Tests that PDO exception is rethrown as an ORM exception during a delete
     */
    public function testPdoExceptionWhileDeletingTaskIsRethrownAsOrmException() : void
    {
        $this->expectException(OrmException::class);
        $this->writeConnection->method('prepare')
            ->willThrowException(new PDOException());
        $this->dataMapper->delete(new Task(-1, 'foo'));
    }

    /**
     * Tests that PDO exception is rethrown as an ORM exception during an update
     */
    public function testPdoExceptionWhileUpdatingTaskIsRethrownAsOrmException() : void
    {
        $this->expectException(OrmException::class);
        $this->writeConnection->method('prepare')
            ->willThrowException(new PDOException());
        $this->dataMapper->update(new Task(-1, 'foo'));
    }

    /**
     * Tests that updating a task creates the correct query
     */
    public function testUpdatingTaskCreatesCorrectQuery() : void
    {
        $task = new Task(1, 'foo');
        $this->setUpMockedConnectionAndStatement(
            $this->writeConnection,
            'UPDATE tasks SET text = :text WHERE id = :id',
            [
                'id' => [1, PDO::PARAM_INT],
                'text' => $task->getText()
            ]
        );
        $this->dataMapper->update($task);
    }

    /**
     * Sets up the mocked connection and statement
     *
     * @param IConnection|\PHPUnit_Framework_MockObject_MockObject $connection The mocked connection to set up
     * @param string $expectedQuery The expected query
     * @param array $expectedBoundValues The expected bound statement values
     */
    private function setUpMockedConnectionAndStatement(
        IConnection $connection,
        string $expectedQuery,
        array $expectedBoundValues
    ) : void {
        $connection->expects($this->once())
            ->method('prepare')
            ->with($expectedQuery)
            ->willReturn($this->statement);
        $this->statement->expects($this->once())
            ->method('bindValues')
            ->with($expectedBoundValues);
        $this->statement->expects($this->once())
            ->method('execute');
    }
}
