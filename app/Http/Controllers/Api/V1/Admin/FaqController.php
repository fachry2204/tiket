<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index() {
        return response()->json(['data' => Faq::orderBy('sort_order')->get()]);
    }
    public function store(Request $request) {
        $data = $request->validate(['question'=>'required|string','answer'=>'required|string','sort_order'=>'integer','is_active'=>'boolean']);
        return response()->json(['data' => Faq::create($data), 'message' => 'FAQ ditambahkan.'], 201);
    }
    public function update(Request $request, Faq $faq) {
        $faq->update($request->validate(['question'=>'sometimes|string','answer'=>'sometimes|string','sort_order'=>'integer','is_active'=>'boolean']));
        return response()->json(['data' => $faq, 'message' => 'FAQ diperbarui.']);
    }
    public function destroy(Faq $faq) {
        $faq->delete();
        return response()->json(['message' => 'FAQ dihapus.']);
    }
}
