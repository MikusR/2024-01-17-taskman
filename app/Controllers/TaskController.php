<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Task;
use App\Models\TaskCollection;
use App\RedirectResponse;
use App\Response;
use App\ViewResponse;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

class TaskController
{
    protected Connection $database;

    public function __construct()
    {
        $connectionParams = [
            'dbname'   => $_ENV['DBNAME'],
            'user'     => $_ENV['USER'],
            'password' => $_ENV['PASSWORD'],
            'host'     => $_ENV['HOST'],
            'driver'   => $_ENV['DRIVER'],
        ];

        try {
            $this->database = DriverManager::getConnection($connectionParams);
            $this->database->connect();
        } catch (Exception $e) {
            $_SESSION['error'] = [
                'status'      => true,
                'message'     => "Can't connect to database",
                'description' => $e->getMessage()
            ];
        }
    }


    public function index(): ViewResponse
    {
        $tasks = $this->getAll();

        return new ViewResponse('index', ['tasks' => $tasks]);
    }

    public function show(string $id): Response
    {
        $task = $this->getById((int) $id);

        return new ViewResponse('show', ['task' => $task]);
    }

    public function edit(string $id): Response
    {
        $task = $this->getById((int) $id);

        return new ViewResponse('edit', ['task' => $task]);
    }

    public function update(string $id): Response
    {
        $task = $this->getById((int) $id);

        $name        = $_POST['name'];
        $description = $_POST['description'];

        $task->set_name($name);

        $task->set_description($description);

        $this->save($task);

        return new RedirectResponse('/');
    }

    public function showSearch(): Response
    {
        return new RedirectResponse('/');
    }

    public function search(): Response
    {
        $term = $_POST['term'];


        $builder = $this->database->createQueryBuilder();
        $tasks   = $builder->select('*')
                           ->from('tasks')
                           ->where(
                               $builder->expr()->or(
                                   $builder->expr()->like('task_description', ':term'),
                                   $builder->expr()->like('task_name', ':term')))
                           ->setParameter('term', '%'.$term.'%')
                           ->fetchAllAssociative();

        if (count($tasks) === 0) {
            return new ViewResponse('index', ['tasks' => []]);
        }

        $results = new TaskCollection();
        foreach ($tasks as $task) {
            $results->add(
                $this->buildModel($task)
            );
        }


        return new ViewResponse('index', ['tasks' => $results]);
    }

    public function add(): Response
    {
        if (strlen($_POST['name']) <= 0) {
            $_SESSION['nameError'] = ['missing' => true, 'description' => $_POST['description']];

            return new RedirectResponse('/');
        }
        $name        = $_POST['name'];
        $description = $_POST['description'];

        $task = new Task($name, $description);
        $this->save($task);

        $_SESSION['nameError'] = [];

        return new RedirectResponse('/');
    }


    public function delete(string $id): Response
    {
        try {
            $this->database->createQueryBuilder()
                           ->delete('tasks')
                           ->where('id = :id')
                           ->setParameter('id', (int) $id)
                           ->executeQuery();
        } catch (Exception $e) {
            $_SESSION['error'] = [
                'status'      => true,
                'message'     => "Can't delete task",
                'description' => $e->getMessage()
            ];
        }

        return new RedirectResponse('/');
    }


    private function save(Task $task): void
    {
        try {
            $builder = $this->database->createQueryBuilder();
            if ($task->getId()) {
                $builder->update('tasks')
                        ->where('id = :id')
                        ->set('task_name', ':name')
                        ->set('task_description', ':description')
                        ->setParameters([
                            'name'        => $task->getName(),
                            'description' => $task->getDescription(),
                            'id'          => $task->getId()
                        ])
                        ->executeQuery();
            } else {
                $builder->insert('tasks')
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
            }
        } catch (Exception $e) {
            $_SESSION['error'] = [
                'status'      => true,
                'message'     => "Can't save task",
                'description' => $e->getMessage()
            ];
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
            $_SESSION['error'] = [
                'status'      => true,
                'message'     => "Can't get task",
                'description' => $e->getMessage()
            ];

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
            $_SESSION['error'] = [
                'status'      => true,
                'message'     => "Can't get list of tasks",
                'description' => $e->getMessage()
            ];
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
            (int) $task['id']
        );
    }
}