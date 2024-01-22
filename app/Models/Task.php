<?php

declare(strict_types=1);

namespace App\Models;


use Carbon\Carbon;

class Task
{
    private string $name;
    private ?string $description;
    private ?carbon $created;
    private ?int $id;

    public function __construct(
        string $name,
        ?string $description,
        ?string $created = null,
        ?int $id = null
    ) {
        $this->name        = $name;
        $this->description = $description;
        $this->created     = $created == null ? Carbon::now() : new Carbon($created);
        $this->id          = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreated(): ?Carbon
    {
        return $this->created;
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }
}