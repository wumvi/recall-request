<?php
declare(strict_types=1);

namespace Wumvi\ReCallRequest;

use Wumvi\MysqlDao\DbDao;

class ReCallRequestDao extends DbDao
{
    public function addRecord(
        string $name,
        string $url,
        string $method = 'GET',
        string $data = '',
        ?string $headers = null
    )
    {
        $this->db->call('call recall_add_record(:name, :url, :method, :data, :headers)', [
            'name' => $name,
            'url' => $url,
            'method' => $method,
            'data' => $data,
            'headers' => $headers,
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
