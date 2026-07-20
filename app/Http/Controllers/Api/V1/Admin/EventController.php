<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index() {
        return response()->json(['data' => Event::latest()->get()]);
    }
    public function show(Event $event) {
        return response()->json(['data' => $event]);
    }
    public function update(Request $request, Event $event) {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'sometimes|date',
            'location' => 'sometimes|string',
            'city' => 'nullable|string',
            'maps_url' => 'nullable|url',
            'contact_whatsapp' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_instagram' => 'nullable|string',
            'contact_address' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'privacy_policy' => 'nullable|string',
            'participant_requirements' => 'nullable|string',
            'status' => 'in:draft,published,ended',
        ]);
        $event->update($data);
        return response()->json(['data' => $event, 'message' => 'Data acara diperbarui.']);
    }
}
