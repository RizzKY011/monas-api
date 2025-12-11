<?php

namespace App\Http\Controllers;

use App\Models\Celengan;
use App\Models\CelenganTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CelenganTransactionController extends Controller
{
    public function index($id)
    {
        $celengan = Celengan::where('user_id', Auth::id())->findOrFail($id);

        $transactions = $celengan->transactions()
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_deposit' => $transactions->where('type', 'deposit')->sum('amount'),
            'total_withdraw' => $transactions->where('type', 'withdraw')->sum('amount'),
            'current_balance' => $celengan->nominal_terkumpul,
            'target' => $celengan->target,
        ];

        return response()->json([
            'data' => $transactions,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request, $id)
    {
        $celengan = Celengan::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:deposit,withdraw',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $amount = (int) $validated['amount'];
        if ($validated['type'] === 'withdraw' && $amount > $celengan->nominal_terkumpul) {
            return response()->json([
                'message' => 'Nominal penarikan melebihi saldo celengan.',
            ], 422);
        }

        $transaction = CelenganTransaction::create([
            'celengan_id' => $celengan->id,
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'amount' => $amount,
            'description' => $validated['description'] ?? null,
            'created_at' => $request->created_at,
        ]);

        if ($validated['type'] === 'deposit') {
            $celengan->nominal_terkumpul += $amount;
        } else {
            $celengan->nominal_terkumpul -= $amount;
        }

        if ($celengan->nominal_terkumpul >= $celengan->target) {
            $celengan->status = 'completed';
            $celengan->completed_at = $celengan->completed_at ?? now();
        } elseif ($celengan->status === 'completed' && $celengan->nominal_terkumpul < $celengan->target) {
            $celengan->status = 'active';
            $celengan->completed_at = null;
        }

        $celengan->save();

        return response()->json([
            'message' => 'Transaksi celengan berhasil disimpan.',
            'data' => $transaction,
            'celengan' => $celengan->fresh(),
        ], 201);
    }
}


