<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductRepository implements ProductRepositoryInterface
{
    protected $model;

    /**
     * ProductRepository constructor.
     *
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->model = $product;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function create($data)
    {
        return $this->model->create([
          'product_name' => $data->product_name,
          'quantity'     => $data->quantity,
          'unit_price'   => $data->unit_price,
          'currency'     => $data->currency,
        ]);
    }

    public function update($data, $id)
    {
        $product = $this->model->find($id);
        $product->update([
          'product_name' => $data->product_name,
          'quantity'     => $data->quantity,
          'unit_price'   => $data->unit_price,
          'currency'     => $data->currency,
        ]);
        return $product;
    }

    public function destroy($id)
    {
        return $this->model->destroy($id);
    }

    public function find($id)
    {
        if (null == $product = $this->model->find($id)) {
            throw new ModelNotFoundException("Product not found");
        }

        return $product;
    }
}