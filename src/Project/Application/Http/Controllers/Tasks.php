<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */
namespace Project\Application\Http\Controllers;

use Exception;
use InvalidArgumentException;
use Opulence\Http\Responses\RedirectResponse;
use Opulence\Http\Responses\Response;
use Opulence\Orm\IUnitOfWork;
use Opulence\Orm\OrmException;
use Opulence\Orm\Repositories\IRepository;
use Opulence\Routing\Controller;
use Project\Infrastructure\Tasks\Task;
use Throwable;

/**
 * Defines the task controller
 */
class Tasks extends Controller
{
    /** @var IUnitOfWork The unit of work */
    private $unitOfWork;
    /** @var  IRepository The task repository */
    private $taskRepository;

    /**
     * @param IUnitOfWork $unitOfWork The unit of work
     * @param IRepository $taskRepository The task repository
     */
    public function __construct(IUnitOfWork $unitOfWork, IRepository $taskRepository)
    {
        $this->unitOfWork = $unitOfWork;
        $this->taskRepository = $taskRepository;
    }

    /**
     * Adds a task
     *
     * @return RedirectResponse The response
     * @throws OrmException Thrown if the task could not be added
     */
    public function addTask() : RedirectResponse
    {
        $task = new Task(-1, $this->request->getInput('text'));
        $this->taskRepository->add($task);
        $this->unitOfWork->commit();

        return new RedirectResponse('/');
    }

    /**
     * Deletes a task
     *
     * @param int $taskId The Id of the task to delete
     * @return RedirectResponse The response
     * @throws OrmException Thrown if the task could not be added
     */
    public function deleteTask(int $taskId) : RedirectResponse
    {
        $task = $this->taskRepository->getById($taskId);
        $this->taskRepository->delete($task);
        $this->unitOfWork->commit();

        return new RedirectResponse('/');
    }

    /**
     * Shows the home page
     *
     * @return Response The response
     * @throws InvalidArgumentException Thrown if the view name does not exist
     * @throws Exception|Throwable Thrown if there was an error compiling the view
     */
    public function showHome() : Response
    {
        $this->view = $this->viewFactory->createView('Home');
        $this->view->setVar('tasks', []);

        return new Response($this->viewCompiler->compile($this->view));
    }
}
