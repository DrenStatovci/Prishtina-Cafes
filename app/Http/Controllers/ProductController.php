<?php

namespace App\Http\Controllers;

use App\Http\Requests\{ProductStoreRequest, ProductUpdateRequest};
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Support\AppliesQueryFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use AppliesQueryFilters;

    public function index(Request $r)
    {
        $this->authorize('viewAny', Product::class);

        $q = Product::query()->with('category');
        if ($r->filled('cafe_id'))
            $q->where('cafe_id', (int) $r->query('cafe_id'));
        if ($r->filled('category_id'))
            $q->where('category_id', (int) $r->query('category_id'));

        // Scope to user's cafes unless admin (admin bypass via Gate::before)
        if (!$r->user()->hasRole('admin')) {
            $staffCafeIds = DB::table('staff_profiles')
                ->where('user_id', $r->user()->id)->where('is_active', true)
                ->pluck('cafe_id');
            if ($staffCafeIds->isNotEmpty()) {
                $q->whereIn('cafe_id', $staffCafeIds);
            } else {
                // No staff profiles = no access
                return ProductResource::collection(collect([]));
            }
        }

        $this->applyCommon($q, $r, ['name', 'price', 'created_at']);

        return ProductResource::collection($q->paginate(50));
    }

    public function store(ProductStoreRequest $req)
    {
        $data = $req->validated();
        $this->authorize('create', [Product::class, (int) $data['cafe_id']]);

        $product = new Product($data);
        if (empty($product->slug)) {
            $product->slug = Str::slug($product->name) . '-' . Str::lower(Str::random(6));
        }
        $product->save();

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);
        return new ProductResource($product->load('category'));
    }

    public function update(ProductUpdateRequest $req, Product $product)
    {
        $this->authorize('update', $product);
        $product->fill($req->validated())->save();
        return new ProductResource($product->load('category'));
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();
        return response()->noContent();
    }

    public function toggle(Product $product)
    {
        $this->authorize('update', $product);
        $product->is_active = !$product->is_active;
        $product->save();
        return new ProductResource($product);
    }
}
