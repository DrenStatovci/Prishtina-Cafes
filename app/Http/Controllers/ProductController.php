<?php

namespace App\Http\Controllers;

use App\Http\Requests\{ProductStoreRequest, ProductUpdateRequest};
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Support\AppliesQueryFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use AppliesQueryFilters;

    public function index(Request $r) {
        $this->authorize('viewAny', Product::class);

        $q = Product::query()->with('category');
        if ($r->filled('cafe_id'))     $q->where('cafe_id', (int)$r->query('cafe_id'));
        if ($r->filled('category_id')) $q->where('category_id', (int)$r->query('category_id'));
        $this->applyCommon($q, $r, ['name','price','created_at']);

        return ProductResource::collection($q->paginate(50));
    }

    public function store(ProductStoreRequest $req) {
        $product = new Product($req->validated());
        $this->authorize('create', $product);

        if (empty($product->slug)) {
            $product->slug = Str::slug($product->name) . '-' . Str::lower(Str::random(6));
        }
        $product->save();

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function show(Product $product) {
        $this->authorize('view', $product);
        return new ProductResource($product->load('category'));
    }

    public function update(ProductUpdateRequest $req, Product $product) {
        $this->authorize('update', $product);
        $product->fill($req->validated())->save();
        return new ProductResource($product->load('category'));
    }

    public function destroy(Product $product) {
        $this->authorize('delete', $product);
        $product->delete();
        return response()->noContent();
    }
}
