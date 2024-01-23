<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Task;
use App\Models\TaskCollection;
use App\RedirectResponse;
use App\Response;
use App\ViewResponse;
use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

class TaskController
{
    protected Connection $database;

    public function __construct()
    {
        $connectionParams = [
            'dbname'   => 'task_manager',
            'user'     => 'taskman',
            'password' => 'taskman',
            'host'     => 'localhost',
            'driver'   => 'pdo_mysql',
        ];

        $this->database = DriverManager::getConnection($connectionParams);
    }

    public function index(): ViewResponse
    {
        $tasks = $this->getAll();

        return new ViewResponse('index', ['tasks' => $tasks]);
    }

    public function show(int $id): Response
    {
        $task = $this->getById($id);

        return new ViewResponse('show', ['task' => $task]);
    }

    public function add(): Response
    {
        $name        = (strlen($_POST['name']) > 0) ? $_POST['name'] : (string)Carbon::now();
        $description = $_POST['description'];
        $task        = new Task($name, $description);
        $this->save($task);

        return new RedirectResponse('/');
    }

    public function delete(int $id): Response
    {
        $this->database->createQueryBuilder()
                       ->delete('tasks')
                       ->where('id = :id')
                       ->setParameter('id', $id)
                       ->executeQuery();

        return new RedirectResponse('/');
    }

    private function save(Task $task): void
    {
        try {
            $this->database->createQueryBuilder()
                           ->insert('tasks')
                           ->values([
                               'task_name'        => ':name',
                               'task_description' => ':description',
                               'created_at'       => ':created'
                           ])
                           ->setParameters([
                               'name'        => $task->getName(),
                               'description' => $task->getDescription(),
                               'created'     => $task->getCreated()
                           ])
                           ->executeQuery();
        } catch (Exception $e) {
        }
    }

    private function getById(int $id): ?Task
    {
        try {
            $task = $this->database->createQueryBuilder()
                                   ->select('*')
                                   ->from('tasks')
                                   ->where('id = :id')
                                   ->setParameter('id', $id)
                                   ->fetchAssociative();
        } catch (Exception $e) {
            return null;
        }
        if ( ! $task) {
            return null;
        }

        return $this->buildModel($task);
    }

    private function getAll(): ?TaskCollection
    {
        try {
            $taskList = $this->database->createQueryBuilder()
                                       ->select('*')
                                       ->from('tasks')
                                       ->fetchAllAssociative();
        } catch (Exception $e) {
        }
        if (empty($taskList)) {
            return null;
        }
        $tasks = new TaskCollection();
        foreach ($taskList as $task) {
            $tasks->add(
                $this->buildModel($task)
            );
        }

        return $tasks;
    }

    private function buildModel(array $task): Task
    {
        return new Task(
            $task['task_name'],
            $task['task_description'],
            $task['created_at'],
            (int)$task['id']
        );
    }
}