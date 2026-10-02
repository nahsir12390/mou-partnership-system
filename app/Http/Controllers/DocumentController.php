<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $documents = Document::with(['agreement.partner', 'uploader'])
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('original_name', 'like', "%{$search}%")
                        ->orWhereHas('agreement', fn ($agreement) => $agreement->where('title', 'like', "%{$search}%")->orWhere('reference_number', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('documents.index', compact('documents'));
    }

    public function create(Agreement $agreement): View
    {
        return view('documents.create', compact('agreement'));
    }

    public function store(Request $request, Agreement $agreement): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:draft_mou,reviewed_mou,signed_mou,supporting_document,correspondence,other'],
            'version' => ['required', 'integer', 'min:1', 'max:999'],
            'description' => ['nullable', 'string', 'max:3000'],
            'is_final' => ['nullable', 'boolean'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->store('agreements/'.$agreement->id.'/documents', 'local');

        $agreement->documents()->create([
            'title' => $data['title'],
            'type' => $data['type'],
            'version' => $data['version'],
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => basename($path),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'description' => $data['description'] ?? null,
            'is_final' => $request->boolean('is_final'),
            'uploaded_by' => $request->user()->id,
        ]);

        return redirect()->route('agreements.show', $agreement)->with('success', 'Document uploaded successfully.');
    }

    public function download(Document $document)
    {
        if (! Storage::disk('local')->exists($document->path)) {
            abort(404);
        }

        return Storage::disk('local')->download($document->path, $document->original_name);
    }

    public function destroy(Document $document): RedirectResponse
    {
        $agreement = $document->agreement;
        Storage::disk('local')->delete($document->path);
        $document->delete();

        return redirect()->route('agreements.show', $agreement)->with('success', 'Document removed successfully.');
    }
}
