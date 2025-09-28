<?php 

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait AppliesQueryFilters {
    protected function applyCommon(Builder $q, Request $r, array $sortable=['created_at']): Builder {
        if ($s = trim((string)$r->query('search',''))) {
            $q->where(fn($w) => $w->where('name','like',"%$s%")->orWhere('slug','like',"%$s%"));
        }
        if ($r->filled('is_active')) $q->where('is_active', (bool)$r->boolean('is_active'));
        $sort = in_array($r->query('sort','created_at'), $sortable, true) ? $r->query('sort') : 'created_at';
        $dir  = $r->query('dir','desc') === 'asc' ? 'asc' : 'desc';
        return $q->orderBy($sort, $dir);
    }
}