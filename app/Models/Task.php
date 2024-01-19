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

    public function get_name(): string
    {
        return $this->name;
    }

    public function get_description(): ?string
    {
        return $this->description;
    }

    public function get_created(): ?Carbon
    {
        return $this->created;
    }

    public function get_id(): ?int
    {
        return $this->id ?? null;
    }
}