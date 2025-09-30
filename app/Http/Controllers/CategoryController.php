<?php

namespace App\Http\Controllers;

use App\Http\Requests\{CategoryStoreRequest, CategoryUpdateRequest};
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\AppliesQueryFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use AppliesQueryFilters;

    public function index(Request $r)
    {
        $this->authorize('viewAny', Category::class);
        $q = Category::query();

        if ($r->filled('cafe_id'))
            $q->where('cafe_id', (int) $r->query('cafe_id'));

        // Scope to user's cafes unless admin (admin bypass via Gate::before)
        if (!$r->user()->hasRole('admin')) {
            $staffCafeIds = DB::table('staff_profiles')
                ->where('user_id', $r->user()->id)->where('is_active', true)
                ->pluck('cafe_id');
            if ($staffCafeIds->isNotEmpty()) {
                $q->whereIn('cafe_id', $staffCafeIds);
            } else {
                // No staff profiles = no access
                return CategoryResource::collection(collect([]));
            }
        }

        $this->applyCommon($q, $r, ['name', 'created_at']);
        return CategoryResource::collection($q->paginate(50));
    }

    public function store(CategoryStoreRequest $req)
    {
        $data = $req->validated();
        $this->authorize('create', [Category::class, (int) $data['cafe_id']]);

        $cat = new Category($data);
        if (empty($cat->slug))
            $cat->slug = Str::slug($cat->name) . '-' . Str::lower(Str::random(6));
        $cat->save();
        return (new CategoryResource($cat))->response()->setStatusCode(201);
    }

    public function show(Category $category)
    {
        $this->authorize('view', $category);
        return new CategoryResource($category);
    }

    public function update(CategoryUpdateRequest $req, Category $category)
    {
        $this->authorize('update', $category);
        $category->fill($req->validated())->save();
        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);
        $category->delete();
        return response()->noContent();
    }

    public function toggle(Category $category)
    {
        $this->authorize('update', $category);
        $category->is_active = !$category->is_active;
        $category->save();
        return new CategoryResource($category);
    }
}