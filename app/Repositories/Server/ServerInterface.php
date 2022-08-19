<?php

namespace App\Repository\Server;

interface ServerInterface
{
    public function create(array $data);
    public function delete(int $id);
}
