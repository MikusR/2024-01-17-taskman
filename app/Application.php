<?php

namespace App;

use App\Models\Task;
use App\Models\TaskCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;


class Application
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
        try {
            $this->database = DriverManager::getConnection($connectionParams);
        } catch (Exception $e) {
            var_dump($e);
        }
    }

    public function run(): void
    {
        try {
            $taskList = $this->database->createQueryBuilder()
                                       ->select('*')
                                       ->from('tasks')
                                       ->fetchAllAssociative();
        } catch (Exception $e) {
        }

        $tasks = new TaskCollection();
        foreach ($taskList as $task) {
            $tasks->add(
                $this->buildModel($task)
            );
        }
        echo "<pre>\n";
//        header('Content-Type: application/json');

        var_dump($tasks);
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