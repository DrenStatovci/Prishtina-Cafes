<?php

namespace App\Http\Controllers;

use App\Http\Requests\{OrderStoreRequest, OrderStatusRequest};
use App\Http\Resources\OrderResource;
use App\Models\{Order, Product};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Staff/Kitchen board
    public function index(Request $r)
    {
        $user = $r->user();

        $q = Order::query()->with(['items.product', 'payments'])
            ->when($r->filled('status'), fn($q) => $q->where('status', $r->query('status')))
            ->when($r->filled('cafe_id'), fn($q) => $q->where('cafe_id', (int) $r->query('cafe_id')))
            ->when($r->filled('branch_id'), fn($q) => $q->where('branch_id', (int) $r->query('branch_id')))
            ->orderByDesc('created_at');

        // Admin can see all; everyone else is scoped by staff_profiles
        if (!$user->hasRole('admin')) {
            $profiles = DB::table('staff_profiles')
                ->select('cafe_id', 'branch_id')
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->get();

            if ($profiles->isEmpty()) {
                // No staff profiles = no access to any orders
                return OrderResource::collection(collect([]));
            }

            // Get all cafe IDs the user has access to
            $cafeIds = $profiles->pluck('cafe_id')->unique()->values();

            // Get branch IDs for branch-specific access
            $branchIds = $profiles->whereNotNull('branch_id')->pluck('branch_id')->unique()->values();

            // Get cafe IDs where user has cafe-level access (branch_id is NULL)
            $cafeLevelIds = $profiles->whereNull('branch_id')->pluck('cafe_id')->unique()->values();

            $q->where(function ($w) use ($branchIds, $cafeLevelIds, $cafeIds) {
                // If user has cafe-level access (branch_id is NULL), show all orders for those cafes
                if ($cafeLevelIds->isNotEmpty()) {
                    $w->whereIn('cafe_id', $cafeLevelIds);
                }

                // If user has branch-specific access, show orders for those branches
                if ($branchIds->isNotEmpty()) {
                    $w->orWhereIn('branch_id', $branchIds);
                }
            });
        }

        return OrderResource::collection($q->paginate(20));
    }

    // Customer orders - only their own orders
    public function myOrders(Request $r)
    {
        $user = $r->user();

        $q = Order::query()
            ->with(['items.product', 'payments', 'cafe', 'branch'])
            ->where('user_id', $user->id)
            ->when($r->filled('status'), fn($q) => $q->where('status', $r->query('status')))
            ->orderByDesc('created_at');

        return OrderResource::collection($q->paginate(20));
    }
    // Customer creates an order
    public function store(OrderStoreRequest $req)
    {
        $this->authorize('create', Order::class);
        $data = $req->validated();



        $order = DB::transaction(function () use ($req, $data) {
            $total = '0.00';
            $order = Order::create([
                'user_id' => optional($req->user())->id,
                'cafe_id' => $data['cafe_id'],
                'branch_id' => $data['branch_id'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_preference' => $data['payment_preference'] ?? null,
                'table_number' => $data['table_number'] ?? null,
                'total_price' => 0,
            ]);

            foreach ($data['items'] as $line) {
                $p = Product::where('id', $line['product_id'])
                    ->where('cafe_id', $data['cafe_id'])
                    ->firstOrFail();

                $qty = (int) $line['quantity'];
                $unit = number_format((float) $p->price, 2, '.', '');
                $lineTotal = bcmul($unit, (string) $qty, 2);
                $total = bcadd($total, $lineTotal, 2);

                $order->items()->create([
                    'product_id' => $p->id,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'line_total' => $lineTotal,
                ]);
            }

            $order->update(['total_price' => $total]);
            return $order->load(['items.product', 'payments']);
        });

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    // Staff updates status
    public function updateStatus(OrderStatusRequest $req, Order $order)
    {
        $this->authorize('updateStatus', $order);
        $order->update(['status' => $req->validated()['status']]);
        return new OrderResource($order->fresh(['items.product', 'payments']));
    }
}