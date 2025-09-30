<?php

namespace App\Http\Controllers;

use App\Http\Requests\{StaffProfileStoreRequest, StaffProfileUpdateRequest};
use App\Http\Resources\StaffProfileResource;
use App\Models\StaffProfile;
use App\Support\AppliesQueryFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffProfileController extends Controller
{
    use AppliesQueryFilters;
    public function index(Request $r)
    {
        $this->authorize('viewAny', StaffProfile::class);
        $q = StaffProfile::query()->with(['user', 'cafe', 'branch']);

        // Apply filters
        $this->applyCommon($q, $r, ['position', 'created_at']);

        // Handle cafe filtering
        if ($r->filled('cafe_id')) {
            $q->where('cafe_id', $r->get('cafe_id'));
        }

        // Handle role filtering
        if ($r->filled('role')) {
            $q->where('position', $r->get('role'));
        }


        // Scope to user's cafes unless admin (admin bypass via Gate::before)
        if (!$r->user()->hasRole('admin')) {
            $staffCafeIds = DB::table('staff_profiles')
                ->where('user_id', $r->user()->id)->where('is_active', true)
                ->pluck('cafe_id');
            if ($staffCafeIds->isNotEmpty()) {
                $q->whereIn('cafe_id', $staffCafeIds);
            } else {
                // No staff profiles = no access
                return StaffProfileResource::collection(collect([]));
            }
        }

        return StaffProfileResource::collection($q->paginate(50));
    }

    public function store(StaffProfileStoreRequest $request)
    {
        $data = $request->validated();
        $this->authorize('create', [StaffProfile::class, (int) ($data['cafe_id'] ?? 0)]);

        // Assign role to user based on position
        if (isset($data['user_id']) && isset($data['position'])) {
            $user = \App\Models\User::find($data['user_id']);
            if ($user) {
                $user->assignRole($data['position']);
            }
        }

        $profile = StaffProfile::create($data);
        return (new StaffProfileResource($profile->load(['user', 'cafe', 'branch'])))->response()->setStatusCode(201);
    }

    public function show(StaffProfile $staffProfile)
    {
        $this->authorize('view', $staffProfile);
        return new StaffProfileResource($staffProfile->load(['user', 'cafe', 'branch']));
    }

    public function update(StaffProfileUpdateRequest $request, StaffProfile $staffProfile)
    {
        $this->authorize('update', $staffProfile);
        $data = $request->validated();

        // Update user role if position changed
        if (isset($data['position']) && $data['position'] !== $staffProfile->position) {
            $user = $staffProfile->user;
            if ($user) {
                // Remove old role and assign new one
                $user->removeRole($staffProfile->position);
                $user->assignRole($data['position']);
            }
        }

        $staffProfile->fill($data)->save();
        return new StaffProfileResource($staffProfile->load(['user', 'cafe', 'branch']));
    }

    public function destroy(StaffProfile $staffProfile)
    {
        $this->authorize('delete', $staffProfile);

        // Remove the user's role if they no longer have any staff profiles for this position
        $user = $staffProfile->user;
        if ($user) {
            // Check if user has other staff profiles with the same position
            $otherProfiles = StaffProfile::where('user_id', $user->id)
                ->where('position', $staffProfile->position)
                ->where('id', '!=', $staffProfile->id)
                ->where('is_active', true)
                ->exists();

            // If no other active profiles with this position, remove the role
            if (!$otherProfiles) {
                $user->removeRole($staffProfile->position);
            }
        }

        $staffProfile->delete();
        return response()->noContent();
    }

    public function toggle(StaffProfile $staffProfile)
    {
        $this->authorize('update', $staffProfile);
        $staffProfile->is_active = !$staffProfile->is_active;
        $staffProfile->save();
        return new StaffProfileResource($staffProfile->load('user'));
    }
}
