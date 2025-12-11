<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Celengan;
use Illuminate\Support\Facades\Storage;

class CelenganController extends Controller
{
    private function queryWithSums()
    {
        return Celengan::where('user_id', Auth::id())
            ->withSum(['transactions as total_deposit' => function ($query) {
                $query->where('type', 'deposit');
            }], 'amount')
            ->withSum(['transactions as total_withdraw' => function ($query) {
                $query->where('type', 'withdraw');
            }], 'amount');
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        
        $query = $this->queryWithSums();

        if ($status) {
            $query->where('status', $status);
        }

        return response()->json([
            'data' => $query->orderBy('created_at', 'DESC')->get()
        ], 200);
    }

public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'currency' => 'required|string',
            'target' => 'required|numeric|min:1',
            'target_date' => 'required|date',
            'plan' => 'nullable|string|in:Harian,Mingguan,Bulanan',
            'nominal_pengisian' => 'nullable|numeric|min:0',
            'notif_on' => 'nullable', 
            'notif_day' => 'nullable|string|max:20',
            'notif_time' => 'nullable|string|max:10',
            'image' => 'nullable|image|max:5120'
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('celengan', 'public');
        }

        $celengan = Celengan::create([
            'user_id' => Auth::id(),
            'nama' => $request->nama,
            'currency' => $request->currency,
            'target' => $request->target,
            'nominal_terkumpul' => 0,
            'nominal_pengisian' => $request->nominal_pengisian ?? 0,
            'plan' => $request->plan ?? 'Harian',
            
            'notif_on' => $request->boolean('notif_on'), 
            
            'notif_day' => $request->notif_day,
            'notif_time' => $request->notif_time,
            'image_path' => $path,
            'target_date' => $request->target_date,
            'status' => 'active',
            'completed_at' => null,
        ]);

        $data = $this->queryWithSums()->find($celengan->id);

        if (!$data) {
            $data = $celengan;
        }

        return response()->json([
            'message' => 'Celengan berhasil dibuat.',
            'data' => $data 
        ], 201);
    }

    public function show($id)
    {
        $celengan = $this->queryWithSums()->findOrFail($id);

        return response()->json([
            'data' => $celengan
        ]);
    }

    public function update(Request $request, $id)
    {
        $celengan = $this->queryWithSums()->findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'currency' => 'required|string',
            'target' => 'required|numeric|min:1',
            'nominal_terkumpul' => 'nullable|numeric|min:0',
            'nominal_pengisian' => 'nullable|numeric|min:0',
            'plan' => 'nullable|string|in:Harian,Mingguan,Bulanan',
            'target_date' => 'nullable|date',
            'notif_on' => 'nullable|boolean',
            'notif_day' => 'nullable|string|max:20',
            'notif_time' => 'nullable|string|max:10',
            'status' => 'nullable|in:active,completed',
            'image' => 'nullable|image|max:5120'
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('celengan', 'public');
            $celengan->image_path = $path;
        }

        $celengan->nama = $request->nama;
        $celengan->currency = $request->currency;
        $celengan->target = $request->target;
        $celengan->nominal_terkumpul = $request->nominal_terkumpul ?? $celengan->nominal_terkumpul;
        $celengan->nominal_pengisian = $request->nominal_pengisian ?? $celengan->nominal_pengisian;
        $celengan->plan = $request->plan ?? $celengan->plan;
        $celengan->target_date = $request->target_date ?? $celengan->target_date;
        if ($request->has('notif_on')) {
            $celengan->notif_on = $request->boolean('notif_on');
        }
        $celengan->notif_day = $request->notif_day ?? $celengan->notif_day;
        $celengan->notif_time = $request->notif_time ?? $celengan->notif_time;
        if ($request->filled('status')) {
            $celengan->status = $request->status;
        }

        if ($celengan->nominal_terkumpul >= $celengan->target) {
            $celengan->status = 'completed';
            $celengan->completed_at = now();
        } elseif ($celengan->status === 'completed' && $celengan->nominal_terkumpul < $celengan->target) {
            $celengan->status = 'active';
            $celengan->completed_at = null;
        }

        $celengan->save();

        return response()->json([
            'message' => 'Celengan berhasil diperbarui.',
            'data' => $this->queryWithSums()->find($celengan->id)
        ]);
    }

    public function destroy($id)
    {
        $celengan = Celengan::where('user_id', Auth::id())->findOrFail($id);
        $celengan->delete();

        return response()->json([
            'message' => "Celengan berhasil dihapus."
        ]);
    }
}
