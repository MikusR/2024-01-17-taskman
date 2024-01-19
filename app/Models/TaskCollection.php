<?php

declare(strict_types=1);

namespace App\Models;

class TaskCollection
{
    private array $tasks;

    public function __construct(array $tasks = [])
    {
        foreach ($tasks as $task) {
            if ( ! $task instanceof Task) {
                continue;
            }
            $this->add($task);
        }
    }

    public function add(Task $task): void
    {
        $this->tasks[] = $task;
    }

    public function get_all(): array
    {
        return $this->tasks;
    }
}