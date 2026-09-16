<?php

namespace App\Http\Controllers;

use App\Services\ProductStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductStore $products
    ) {}

    public function index(): JsonResponse
    {
        return $this->listing($this->products->all());
    }

    public function store(Request $request): JsonResponse
    {
        $products = $this->products->save(
            $this->validatedProduct($request)
        );

        return $this->listing($products, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $products = $this->products->save(
            $this->validatedProduct($request),
            $id
        );

        return $this->listing($products);
    }

    private function validatedProduct(Request $request): array
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],
            'quantity' => [
                'required',
                'regex:/^(0|[1-9][0-9]{0,5})$/',
                'numeric',
                'max:100000',
            ],
            'price' => [
                'required',
                'regex:/^(0|[1-9][0-9]{0,4})(\.[0-9]{1,2})?$/',
            ],
        ], [
            'name.required' => 'Enter a product name.',
            'quantity.required' => 'Enter the quantity in stock.',
            'quantity.regex' => 'Enter a whole number from 0 to 100,000.',
            'quantity.max' => 'Quantity cannot exceed 100,000.',
            'price.required' => 'Enter the price per item.',
            'price.regex' => 'Enter a price from 0 to 99,999.99, with up to two decimal places.',
        ]);

        [$whole, $fraction] = array_pad(
            explode('.', (string) $data['price']),
            2,
            ''
        );

        $fraction = str_pad($fraction, 2, '0');

        return [
            'name' => trim($data['name']),
            'quantity' => (int) $data['quantity'],
            'price' => $whole . '.' . $fraction,
            'price_cents' => ((int) $whole * 100) + (int) $fraction,
        ];
    }

    private function listing(
        array $products,
        int $status = 200
    ): JsonResponse {
        usort($products, function (array $first, array $second) {
            return strcmp(
                $first['submitted_at'],
                $second['submitted_at']
            ) ?: strcmp($first['id'], $second['id']);
        });

        $total = 0;

        foreach ($products as &$product) {
            $product['total_cents'] =
                $product['quantity'] * $product['price_cents'];

            $total += $product['total_cents'];
        }

        unset($product);

        return response()->json([
            'products' => $products,
            'total_cents' => $total,
        ], $status)->header('Cache-Control', 'no-store');
    }
}