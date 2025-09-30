<?php

namespace App\Http\Controllers;

use App\Http\Requests\{CafeStoreRequest, CafeUpdateRequest};
use App\Http\Resources\CafeResource;
use App\Models\Cafe;
use App\Support\AppliesQueryFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CafeController extends Controller
{
    use AppliesQueryFilters;

    public function index(Request $r)
    {
        $this->authorize('viewAny', Cafe::class);
        $q = Cafe::query()->with('owner');

        // Scope to user's cafes unless admin (admin bypass via Gate::before)
        if (!$r->user()->hasRole('admin')) {
            $staffCafeIds = DB::table('staff_profiles')
                ->where('user_id', $r->user()->id)->where('is_active', true)
                ->pluck('cafe_id');
            if ($staffCafeIds->isNotEmpty()) {
                $q->whereIn('id', $staffCafeIds);
            } else {
                // No staff profiles = no access
                return CafeResource::collection(collect([]));
            }
        }

        $this->applyCommon($q, $r, ['name', 'created_at']);
        return CafeResource::collection($q->paginate(20));
    }

    public function store(CafeStoreRequest $req)
    {
        $this->authorize('create', Cafe::class);
        $cafe = new Cafe($req->validated());
        if (empty($cafe->slug))
            $cafe->slug = Str::slug($cafe->name) . '-' . Str::lower(Str::random(6));
        $cafe->save();

        // Create staff profile for the owner
        if ($cafe->owner_id) {
            $owner = \App\Models\User::find($cafe->owner_id);
            if ($owner) {
                // Assign owner role
                $owner->assignRole('owner');

                // Create staff profile
                \App\Models\StaffProfile::create([
                    'user_id' => $cafe->owner_id,
                    'cafe_id' => $cafe->id,
                    'position' => 'owner',
                    'is_active' => true,
                ]);
            }
        }

        return (new CafeResource($cafe->load('owner')))->response()->setStatusCode(201);
    }

    public function show(Cafe $cafe)
    {
        $this->authorize('view', $cafe);
        return new CafeResource($cafe->load('owner'));
    }

    public function update(CafeUpdateRequest $req, Cafe $cafe)
    {
        $this->authorize('update', $cafe);
        $oldOwnerId = $cafe->owner_id;
        $cafe->fill($req->validated())->save();

        // Handle owner change
        if ($cafe->owner_id !== $oldOwnerId) {
            // Remove old owner's staff profile for this cafe
            if ($oldOwnerId) {
                \App\Models\StaffProfile::where('user_id', $oldOwnerId)
                    ->where('cafe_id', $cafe->id)
                    ->where('position', 'owner')
                    ->delete();
            }

            // Create new owner's staff profile
            if ($cafe->owner_id) {
                $owner = \App\Models\User::find($cafe->owner_id);
                if ($owner) {
                    // Assign owner role
                    $owner->assignRole('owner');

                    // Create staff profile
                    \App\Models\StaffProfile::create([
                        'user_id' => $cafe->owner_id,
                        'cafe_id' => $cafe->id,
                        'position' => 'owner',
                        'is_active' => true,
                    ]);
                }
            }
        }

        return new CafeResource($cafe->load('owner'));
    }

    public function destroy(Cafe $cafe)
    {
        $this->authorize('delete', $cafe);
        $cafe->delete(); // restrictOnDelete në orders/branches ruan historikun
        return response()->noContent();
    }

    public function toggle(Cafe $cafe)
    {
        $this->authorize('update', $cafe);
        $cafe->is_active = !$cafe->is_active;
        $cafe->save();
        return new CafeResource($cafe->load('owner'));
    }
}