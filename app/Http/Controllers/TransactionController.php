<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 12;

        $userId = Auth::id();

        $baseQuery = Transaction::where(function ($q) use ($userId) {
            $q->whereNull('user_id')
              ->whereHas('booking', function ($bq) use ($userId) {
                  $bq->where('guest_id', $userId);
              })
            ->orWhere('user_id', $userId);
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

    public function payForm(Transaction $transaction)
    {
        // Only allow viewing/ paying if transaction is pending and user is allowed
        $userId = Auth::id();

        if ($transaction->status !== 'Pending') {
            return redirect()->route('transactions.index')->with('error', 'This transaction is not pending payment.');
        }

        // If transaction is scoped to a user, only that user can access
        if ($transaction->user_id !== null) {
            if ($transaction->user_id != $userId) {
                abort(403);
            }
        } else {
            // Otherwise, only the booking guest may access
            if (!$transaction->booking || $transaction->booking->guest_id != $userId) {
                abort(403);
            }
        }

        return view('pages.transaction-pay', ['transaction' => $transaction]);
    }
}
