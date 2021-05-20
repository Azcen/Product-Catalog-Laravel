<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
              return $this->user()->hasPermissionTo('products.store');
            case 'PUT':
              return $this->user()->hasPermissionTo('products.update');
            case 'DELETE':
              return $this->user()->hasPermissionTo('products.destroy');
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
                'product_name' => 'required|string',
                'img_url'      => 'string',
                'quantity'     => 'required|numeric',
                'unit_price'   => 'required',
                'currency'     => 'required|numeric',
              ];
          }
          return [
            //
          ];
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException(__('messages.unauthorized'));
    }
}
