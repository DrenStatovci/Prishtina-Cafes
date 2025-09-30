<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\StaffProfile;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user?->only('id', 'name', 'email'),
                'roles' => $user?->getRoleNames() ?? [],
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
            ],
            'staffScope' => $user ? (function () use ($user) {
                $profiles = \App\Models\StaffProfile::query()
                    ->where('user_id', $user->id)->where('is_active', true)
                    ->get(['cafe_id', 'branch_id']);

                $isAdmin = $user->hasRole('admin');
                $isMgrOrOw = $user->hasAnyRole(['owner', 'manager']);

                $branchIds = $profiles->whereNotNull('branch_id')->pluck('branch_id')->values();
                $cafeIds = $profiles->pluck('cafe_id')->unique()->values();

                // Café selection
                $allowedCafeIds = $isAdmin ? null : $cafeIds; // null = all
                $canChooseCafe = $isAdmin || ($isMgrOrOw && $cafeIds->count() > 1);
                $lockedCafeId = (!$isAdmin && $cafeIds->count() === 1) ? $cafeIds->first() : null;

                // Branch selection
                $hasCafeLevel = $profiles->whereNull('branch_id')->count() > 0; // can see all branches in their cafe
                $canChooseBranch = $isAdmin || $isMgrOrOw || $hasCafeLevel;
                $lockedBranchId = (!$canChooseBranch && $branchIds->count() === 1) ? $branchIds->first() : null;

                return [
                    'canChooseCafe' => $canChooseCafe,
                    'lockedCafeId' => $lockedCafeId,
                    'allowedCafeIds' => $allowedCafeIds,   // array or null for admin
                    'canChooseBranch' => $canChooseBranch,
                    'lockedBranchId' => $lockedBranchId,
                ];
            })() : null,
        ]);
    }
}
