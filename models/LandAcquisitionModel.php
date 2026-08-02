<?php

declare(strict_types=1);

namespace App\Models;

final class LandAcquisitionModel extends BaseModel
{
    public function __construct(string $table)
    {
        $this->table = $table;
        parent::__construct();
    }

    public function listRecords(string $orderBy = 'id DESC'): array
    {
        return $this->db->query('SELECT * FROM ' . $this->table . ' ORDER BY ' . $orderBy)->fetchAll();
    }

    public function createRecord(array $data): int
    {
        return $this->create($data);
    }

    public function updateRecord(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    public function deleteRecord(int $id): bool
    {
        return $this->delete($id);
    }

    public function countRecords(string $where = '1=1'): int
    {
        return $this->count($where);
    }
}
