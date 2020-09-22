<?php
declare(strict_types=1);

namespace Wumvi\ReCallRequest;

use Wumvi\MysqlDao\DbDao;

class ReCallRequestDao extends DbDao
{
    public function addRecord(string $url, string $method = 'GET', string $data = '')
    {
        $this->db->call('call recall_add_record(:url, :method, :data)', [
            'url' => $url,
            'method' => $method,
            'data' => $data,
        ]);
    }

    public function removeRecord(int $id): void
    {
        $this->db->call('call recall_delete_record(:id)', [
            'id' => $id,
        ]);
    }

    public function setErrorToRecord(int $id, string $error): void
    {
        $this->db->call('call recall_set_error_to_record(:id, :error)', [
            'id' => $id,
            'error' => $error,
        ]);
    }

    public function getRecords(): array
    {
        return $this->db->call('call recall_get_records()');
    }
}
