<?php
// app/Http/Controllers/JobCodeController.php

namespace App\Http\Controllers;

use App\Models\JobCode;
use App\Models\RlpItem;
use Illuminate\Http\Request;

class JobCodeController extends Controller
{
    // Job code ikut dari project / RRP, jadi yang boleh mengubahnya cuma user input
    // (pembuat dokumen). Approver dan finance hanya boleh melihat daftar.
    private function authorizeManage(): void
    {
        abort_unless(auth()->user()->isInput(), 403, "You don't have permission to manage job codes.");
    }

    public function index(Request $request)
    {
        $search = $request->query('search');

        $jobCodes = JobCode::query()
            ->when($search, function ($query) use ($search) {
                $query->where('job_code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            return view('job_codes._table', compact('jobCodes', 'search'));
        }

        return view('job_codes.index', compact('jobCodes', 'search'));
    }

    public function create()
    {
        $this->authorizeManage();

        return view('job_codes.create');
    }

    public function store(Request $request)
    {
        $this->authorizeManage();

        $validated = $this->validateJobCode($request);

        JobCode::create($validated);

        return redirect()->route('job-codes.index')->with('success', 'Job code added successfully.');
    }

    public function edit(JobCode $jobCode)
    {
        $this->authorizeManage();

        return view('job_codes.edit', compact('jobCode'));
    }

    public function update(Request $request, JobCode $jobCode)
    {
        $this->authorizeManage();

        $validated = $this->validateJobCode($request, $jobCode->id);

        $jobCode->update($validated);

        return redirect()->route('job-codes.index')->with('success', 'Job code updated successfully.');
    }

    public function destroy(JobCode $jobCode)
    {
        $this->authorizeManage();

        // Job code yang sudah dipakai di item RRP tidak boleh dihapus,
        // kalau dihapus item RRP lama kehilangan job code-nya.
        abort_if(
            RlpItem::where('job_code_id', $jobCode->id)->exists(),
            403,
            "This job code is already used in an RRP and can't be deleted."
        );

        $jobCode->delete();

        return redirect()->route('job-codes.index')->with('success', 'Job code deleted successfully.');
    }

    private function validateJobCode(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'job_code' => 'required|string|max:100|unique:job_codes,job_code' . ($ignoreId ? ",$ignoreId" : ''),
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'part_number' => 'nullable|string|max:100',
        ]);
    }
}
