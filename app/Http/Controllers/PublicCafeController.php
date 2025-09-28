<?php 


namespace App\Http\Controllers;

use App\Http\Resources\{CafeResource, BranchResource, CategoryResource, ProductResource};
use App\Models\{Cafe, Category};
use App\Support\AppliesQueryFilters;
use Illuminate\Http\Request;

class PublicCafeController extends Controller
{
    use AppliesQueryFilters;

    public function cafes(Request $r) {
        $q = Cafe::query()->select('id','name','slug','email');
        // $this->applyCommon($q, $r, ['name','created_at']);
        return CafeResource::collection($q->paginate(20));
    }

    public function branches(string $slug, Request $r) {
        $cafe = Cafe::where('slug',$slug)->firstOrFail();
        $q = $cafe->branches()->where('is_active', true)->getQuery();
        // $this->applyCommon($q, $r, ['name','created_at']);
        return BranchResource::collection($q->paginate(50));
    }

    public function categories(string $slug) {
        $cafe = Cafe::where('slug',$slug)->firstOrFail();
        return CategoryResource::collection(
            $cafe->categories()->where('is_active', true)->get()
        );
    }

    public function products(string $categorySlug, Request $r) {
        $category = Category::where('slug',$categorySlug)->firstOrFail();
        $q = $category->products()->where('is_active', true)->getQuery(); ;
        $this->applyCommon($q, $r, ['name','price','created_at']);
        return ProductResource::collection($q->paginate(50));
    }
}