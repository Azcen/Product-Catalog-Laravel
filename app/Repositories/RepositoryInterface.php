<?php

namespace App\Repositories;

interface RepositoryInterface
{
    public function all();

    public function create($data);

    public function update($data, $id);

    public function destroy($id);

    public function find($id);
}