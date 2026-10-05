<?php

namespace App\Http\Controllers;

use App\Models\Draft;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DraftController extends Controller
{
    private const FORMS = ['material-requests', 'rlps', 'purchase-requests', 'purchase-orders'];

    public function show(Request $request)
    {
        $data = $request->validate([
            'form' => ['required', Rule::in(self::FORMS)],
            'context' => 'nullable|string|max:100',
        ]);

        $draft = Draft::where('user_id', auth()->id())
            ->where('form', $data['form'])
            ->where('context', $data['context'] ?? '')
            ->first();

        if (! $draft) {
            return response()->json(['exists' => false]);
        }

        return response()->json([
            'exists' => true,
            'payload' => $draft->payload,
            'updated_at' => $draft->updated_at->toIso8601String(),
        ]);
    }

        public function store(Request $request)
    {
        $data = $request->validate([
            'form' => ['required', Rule::in(self::FORMS)],
            'context' => 'nullable|string|max:100',
            'payload' => 'required|string|max:1000000',
        ]);

        Draft::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'form' => $data['form'],
                'context' => $data['context'] ?? '',
            ],
            [
                'payload' => $data['payload'],
                'title' => Draft::titleFrom($data['form'], $data['payload']),
            ]
        );

        return response()->json(['ok' => true]);
    }

    // Hapus dari tombol di list (redirect balik, beda dengan destroy() yang dipanggil JS)
    public function remove(Draft $draft)
    {
        abort_unless($draft->user_id === auth()->id(), 403);

        $draft->delete();

        return back()->with('success', 'Draft dihapus.');
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'form' => ['required', Rule::in(self::FORMS)],
            'context' => 'nullable|string|max:100',
        ]);

        Draft::forget($data['form'], $data['context'] ?? '');

        return response()->noContent();
    }
}
