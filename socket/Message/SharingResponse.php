<?php

namespace socket\Message;

use uzdevid\property\loader\Entity;

class SharingResponse extends Entity {
    public int $id;
    public string $remoteAddress;
    public string $localAddress;
    public array $proxies = [];

    /**
     * @return array[]
     */
    protected function properties(): array {
        return [
            'remoteAddress' => [fn(string $remote_address) => sprintf("https://%s", $remote_address), 'remote'],
            'localAddress' => [fn(string $remote_address) => sprintf("https://%s", $remote_address), 'local'],
            'proxies' => [fn(string $remote_address, string $local_address) => [
                sprintf("https://%s", $remote_address) => $local_address,
                sprintf("http://%s", $remote_address) => $local_address
            ], 'remote', 'local']
        ];
    }
}