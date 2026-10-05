<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    private function scopeVisible(Builder $query, $user): Builder
    {
        if ($user->hasRole('system-administrator', 'management', 'legal-review-officer')) {
            return $query;
        }

        return $query->whereHas('agreement', function (Builder $agreement) use ($user) {
            $agreement->where(function (Builder $scope) use ($user) {
                if ($user->department_id) {
                    $scope->where('department_id', $user->department_id);
                }
                $scope->orWhere('responsible_officer_id', $user->id)->orWhere('created_by', $user->id);
            });
        });
    }

    private function ensureAgreementVisible(Agreement $agreement, $user): void
    {
        if ($user->hasRole('system-administrator', 'management', 'legal-review-officer')) {
            return;
        }
        if (($user->department_id && $agreement->department_id === $user->department_id) || $agreement->responsible_officer_id === $user->id || $agreement->created_by === $user->id) {
            return;
        }
        abort(403);
    }

    private function ensureDocumentVisible(Document $document, $user): void
    {
        $this->ensureAgreementVisible($document->agreement, $user);
    }

    public function index(Request $request): View
    {
        $documents = $this->scopeVisible(Document::with(['agreement.partner', 'uploader']), $request->user())
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('original_name', 'like', "%{$search}%")
                        ->orWhereHas('agreement', fn ($agreement) => $agreement->where('title', 'like', "%{$search}%")->orWhere('reference_number', 'like', "%{$search}%"));
                });
            })->latest()->paginate(15)->withQueryString();

        return view('documents.index', compact('documents'));
    }

    public function create(Request $request, Agreement $agreement): View
    {
        $this->ensureAgreementVisible($agreement, $request->user());

        return view('documents.create', compact('agreement'));
    }

    public function store(Request $request, Agreement $agreement): RedirectResponse
    {
        $this->ensureAgreementVisible($agreement, $request->user());
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
        $agreement->documents()->create(['title' => $data['title'], 'type' => $data['type'], 'version' => $data['version'], 'original_name' => $file->getClientOriginalName(), 'stored_name' => basename($path), 'path' => $path, 'mime_type' => $file->getMimeType(), 'size' => $file->getSize(), 'description' => $data['description'] ?? null, 'is_final' => $request->boolean('is_final'), 'uploaded_by' => $request->user()->id]);

        return redirect()->route('agreements.show', $agreement)->with('success', 'Document uploaded successfully.');
    }

    public function download(Request $request, Document $document)
    {
        $this->ensureDocumentVisible($document, $request->user());
        if (! Storage::disk('local')->exists($document->path)) {
            abort(404);
        }

        return Storage::disk('local')->download($document->path, $document->original_name);
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        $this->ensureDocumentVisible($document, $request->user());
        $agreement = $document->agreement;
        Storage::disk('local')->delete($document->path);
        $document->delete();

        return redirect()->route('agreements.show', $agreement)->with('success', 'Document removed successfully.');
    }
}
