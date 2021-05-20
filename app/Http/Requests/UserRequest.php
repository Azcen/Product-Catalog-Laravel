<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;

use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        switch ($this->method()) {
            case 'POST':
              return $this->user()->hasPermissionTo('users.store');
            case 'PUT':
              return $this->user()->hasPermissionTo('users.update');
            case 'DELETE':
              return $this->user()->hasPermissionTo('users.destroy');
          }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'POST':
              return [
                'name' => 'required|max:191',
                'email' => 'required|max:191|email|unique:users',
              ];
            case 'PUT':
              return [
                'name' => 'required|max:191',
                'email' => 'required',
              ];
            case 'DELETE':
              return [
              ];
          }
        return [
            //
        ];
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException('Not Authorized');
    }
}
