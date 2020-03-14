<?php

namespace App\Infrastructure\EventSource;

interface SnapshotRepositoryInterface
{
    public function save(Snapshot $snapshot);

    /**
     * @param string $id
     * @param string $type
     *
     * @return mixed
     */
    public function load(string $id, string $type): ?Snapshot;
}
