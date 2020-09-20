<?php
declare(strict_types=1);

namespace Wumvi\ReCallRequest;

use Wumvi\Sqlite3Dao\DbDao;

class ReCallRequestDao extends DbDao
{
    public function add(string $url, string $method = 'GET', string $data = '')
    {
        $this->db->call('insert into tasks (url, method, data) values (:url, :method, :data)', [
            'url' => $url,
            'method' => $method,
            'data' => $data,
        ]);
    }

    public function getAll(): array
    {
        return $this->db->tableFetchAll('select * from tasks');
    }

    public function removeById(int $id): void
    {
        $this->db->call('delete from tasks where id = :id', [
            'id' => $id,
        ]);
    } 

    public function incrementTry(int $id): void
    {
        $this->db->call('update tasks set try = try + 1 where id = :id', [
            'id' => $id,
        ]);
    }
}
