<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // GET /api/products
    public function index()
    {
        return response()->json(
            Product::with(['currency', 'prices.currency'])->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */

    // POST /api/products
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|exists:currencies,name',
            'tax_cost' => 'required|numeric|min:0',
            'manufacturing_cost' => 'required|numeric|min:0',
        ]);

        $currency = Currency::where('name', $data['currency'])->firstOrFail();

        $data['currency_id'] = $currency->id;
        unset($data['currency']);

        $product = Product::create($data);
        $product->prices()->create([
            'currency_id' => $currency->id,
            'price' => $product->price,
        ]);

        return response()->json(
            $product->load('currency'),
            201);
    }

    /**
     * Display the specified resource.
     */

    // GET /api/products/{id}
    public function show(Product $product)
    {
        return response()->json(
            $product->load(['currency', 'prices.currency'])
        );
    }

    /**
     * Update the specified resource in storage.
     */

    // PUT /api/products/{id}
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'currency' => 'required|string|exists:currencies,name',
            'tax_cost' => 'sometimes|required|numeric|min:0',
            'manufacturing_cost' => 'sometimes|required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, $product) {
           
            $currency = isset($data['currency'])
                ? Currency::where('name', $data['currency'])->firstOrFail()
                : $product->currency;

            unset($data['currency']);

            $data['currency_id'] = $currency->id;

            $product->update($data);

            $product->prices()->updateOrCreate(
                ['currency_id' => $currency->id],
                ['price' => $product->price]
            );
        });

        return response()->json(
            $product->fresh()->load('currency')
        );
    }

    /**
     * Remove the specified resource from storage.
     */

    // DELETE /api/products/{id}
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(null, 204);
    }

    // GET /api/products/{id}/prices
    public function indexPrices(Product $product)
    {
        return response()->json(
            $product->prices()->with('currency')->get()
        );
    }

    // POST /api/products/{id}/prices
    public function storePrice(Request $request, Product $product)
    {
        $data = $request->validate([
            'currency' => 'required|string|exists:currencies,name',
            'price' => 'required|numeric|min:0',
        ]);

        $currency = Currency::where('name', $data['currency'])
            ->firstOrFail();

        $currencyId = $currency->id;

        $exists = $product->prices()
            ->where('currency_id', $currencyId)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Este producto ya tiene un precio en esa divisa.',
            ], 422);
        }

        $price = $product->prices()->create([
            'currency_id' => $currencyId,
            'price' => $data['price'],
        ]);

        return response()->json(
            $price->load('currency'),
            201
        );
    }
}
