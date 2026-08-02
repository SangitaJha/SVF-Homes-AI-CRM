<?php

declare(strict_types=1);

namespace App\Models;

final class Resource extends BaseModel
{
    public function __construct(string $table)
    {
        $this->table = $table;
        parent::__construct();
    }
}
