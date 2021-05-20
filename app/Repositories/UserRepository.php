<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    /**
     * UserRepository constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function create($data)
    {
        return $this->model->create([
          'name' => $data->name,
          'email' => $data->email,
          'password' => Hash::make($data->password),
        ]);
    }

    public function update($data, $id)
    {
        $user = $this->model->find($id);
        $user->update([
          'name' => $data->name,
          'email' => $data->email,
          'password' => Hash::make($data->password),
        ]);
        return $user;
    }

    public function destroy($id)
    {
        return $this->model->destroy($id);
    }

    public function find($id)
    {
        if (null == $user = $this->model->find($id)) {
            throw new ModelNotFoundException("User not found");
        }

        return $user;
    }
}