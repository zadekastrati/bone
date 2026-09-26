<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountCode;
use Illuminate\View\View;

class DiscountCodeController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', DiscountCode::class);

        $codes = DiscountCode::query()
            ->with('usedByOrder')
            ->latest()
            ->paginate(50);

        return view('admin.discount-codes.index', compact('codes'));
    }
}
