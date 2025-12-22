<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 12;

        $baseQuery = Transaction::whereHas('booking', function ($q) {
            $q->where('guest_id', Auth::id());
        });

        $totalTransactions = (clone $baseQuery)->count();
        $pendingTransactions = (clone $baseQuery)->where('status', 'Pending')->count();

        $paymentsQuery = (clone $baseQuery)->where('type', '!=', 'Refund');
        $refundsQuery = (clone $baseQuery)->where('type', 'Refund');

        $totalPayments = (clone $paymentsQuery)->count();
        $totalRefunds = (clone $refundsQuery)->count();

        $payments = $paymentsQuery->with(['booking.availability.listing', 'refund'])
            ->orderByDesc('paid_at')
            ->paginate($perPage, ['*'], 'payments_page');

        $refunds = $refundsQuery->with(['booking.availability.listing', 'refund'])
            ->orderByDesc('paid_at')
            ->paginate($perPage, ['*'], 'refunds_page');

        return view('pages.transactions', compact(
            'payments', 'refunds', 'totalTransactions', 'pendingTransactions', 'totalPayments', 'totalRefunds'
        ));
    }
}
