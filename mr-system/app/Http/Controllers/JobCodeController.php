<?php

namespace App\Http\Controllers;

use App\Models\JobCode;
use Illuminate\Http\Request;

class JobCodeController extends Controller
{
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
        return view('job_codes.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateJobCode($request);

        JobCode::create($validated);

        return redirect()->route('job-codes.index')->with('success', 'Job code added successfully.');
    }

    public function edit(JobCode $jobCode)
    {
        return view('job_codes.edit', compact('jobCode'));
    }

    public function update(Request $request, JobCode $jobCode)
    {
        $validated = $this->validateJobCode($request, $jobCode->id);

        $jobCode->update($validated);

        return redirect()->route('job-codes.index')->with('success', 'Job code updated successfully.');
    }

    public function destroy(JobCode $jobCode)
    {
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
