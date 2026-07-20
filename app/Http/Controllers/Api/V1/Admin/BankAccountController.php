<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index()
    {
        return response()->json(['data' => BankAccount::latest()->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:150',
            'branch' => 'nullable|string|max:150',
            'instructions' => 'nullable|string',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($data['is_primary'] ?? false) {
            BankAccount::where('is_primary', true)->update(['is_primary' => false]);
        }

        $bank = BankAccount::create($data);
        return response()->json(['data' => $bank, 'message' => 'Rekening bank berhasil ditambahkan.'], 201);
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $data = $request->validate([
            'bank_name' => 'sometimes|string|max:100',
            'account_number' => 'sometimes|string|max:50',
            'account_holder_name' => 'sometimes|string|max:150',
            'branch' => 'nullable|string|max:150',
            'instructions' => 'nullable|string',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($data['is_primary'] ?? false) {
            BankAccount::where('is_primary', true)->update(['is_primary' => false]);
        }

        $bankAccount->update($data);
        return response()->json(['data' => $bankAccount, 'message' => 'Rekening bank diperbarui.']);
    }

    public function destroy(BankAccount $bankAccount)
    {
        $bankAccount->delete();
        return response()->json(['message' => 'Rekening bank dihapus.']);
    }
}
