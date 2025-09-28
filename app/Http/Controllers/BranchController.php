<?php

namespace App\Http\Controllers;

use App\Http\Requests\{BranchStoreRequest, BranchUpdateRequest};
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Support\AppliesQueryFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    use AppliesQueryFilters;

    public function index(Request $r) {
        $this->authorize('viewAny', Branch::class);
        $q = Branch::query();

        // filtro sipas café nëse dërgohet
        if ($r->filled('cafe_id')) $q->where('cafe_id', (int)$r->query('cafe_id'));

        // shfaq vetëm brenda cafëve ku je staf
        $staffCafeIds = DB::table('staff_profiles')
            ->where('user_id', $r->user()->id)->where('is_active', true)
            ->pluck('cafe_id');
        $q->whereIn('cafe_id', $staffCafeIds);

        $this->applyCommon($q, $r, ['name','created_at']);
        return BranchResource::collection($q->paginate(50));
    }

    public function store(BranchStoreRequest $req) {
        $branch = new Branch($req->validated());
        $this->authorize('create', $branch);
        if (empty($branch->slug)) $branch->slug = Str::slug($branch->name).'-'.Str::lower(Str::random(6));
        $branch->save();
        return (new BranchResource($branch))->response()->setStatusCode(201);
    }

    public function show(Branch $branch) {
        $this->authorize('view', $branch);
        return new BranchResource($branch);
    }

    public function update(BranchUpdateRequest $req, Branch $branch) {
        $this->authorize('update', $branch);
        $branch->fill($req->validated())->save();
        return new BranchResource($branch);
    }

    public function destroy(Branch $branch) {
        $this->authorize('delete', $branch);
        $branch->delete();
        return response()->noContent();
    }
}