<?php

class CafeController extends Controller
{
    use AppliesQueryFilters;

    public function index(Request $r) {
        $this->authorize('viewAny', Cafe::class);
        $q = Cafe::query();

        // shfaq vetëm cafet ku je staf (admin bypass nga Gate::before)
        $staffCafeIds = DB::table('staff_profiles')
            ->where('user_id', $r->user()->id)->where('is_active', true)
            ->pluck('cafe_id');
        $q->whereIn('id', $staffCafeIds);

        $this->applyCommon($q, $r, ['name','created_at']);
        return CafeResource::collection($q->paginate(20));
    }

    public function store(CafeStoreRequest $req) {
        $cafe = new Cafe($req->validated());
        $this->authorize('create', $cafe);
        if (empty($cafe->slug)) $cafe->slug = Str::slug($cafe->name).'-'.Str::lower(Str::random(6));
        $cafe->save();
        return (new CafeResource($cafe))->response()->setStatusCode(201);
    }

    public function show(Cafe $cafe) {
        $this->authorize('view', $cafe);
        return new CafeResource($cafe);
    }

    public function update(CafeUpdateRequest $req, Cafe $cafe) {
        $this->authorize('update', $cafe);
        $cafe->fill($req->validated())->save();
        return new CafeResource($cafe);
    }

    public function destroy(Cafe $cafe) {
        $this->authorize('delete', $cafe);
        $cafe->delete(); // restrictOnDelete në orders/branches ruan historikun
        return response()->noContent();
    }
}