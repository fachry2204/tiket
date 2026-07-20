<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, AuditLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index() {
        return response()->json(['data' => User::latest()->get()->makeHidden('password')]);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:super_admin,finance_admin,ticketing_admin,viewer',
            'is_active' => 'boolean',
        ]);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        return response()->json(['data' => $user->makeHidden('password'), 'message' => 'Admin berhasil ditambahkan.'], 201);
    }

    public function update(Request $request, User $user) {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|string|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|in:super_admin,finance_admin,ticketing_admin,viewer',
            'is_active' => 'boolean',
        ]);
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return response()->json(['data' => $user->makeHidden('password'), 'message' => 'Admin diperbarui.']);
    }

    public function destroy(Request $request, User $user) {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Tidak dapat menghapus akun sendiri.'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'Admin dihapus.']);
    }
}
