<?php

namespace Arrocy\Template\Http\Controllers;

use App\Http\Controllers\Controller;
use Arrocy\Template\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TemplateController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $templates = Template::query()
            ->where('user_id', auth()->id())
            ->search($request->string('search')->toString())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('template::index', compact('templates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'text' => ['required', 'string'],
        ]);

        $template = new Template($validated);
        $template->user()->associate(auth()->user());
        $template->save();

        return redirect()->route('template.index')->with('success', __('Template created successfully.'));
    }

    public function edit(Template $template): View
    {
        $this->authorizeOwner($template);

        return view('template::edit', compact('template'));
    }

    public function update(Request $request, Template $template): RedirectResponse
    {
        $this->authorizeOwner($template);

        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'url:http,https'],
            'text' => ['required', 'string'],
        ]);

        $template->update($validated);

        return redirect()->route('template.index')->with('success', __('Template updated successfully.'));
    }

    public function toggle(Template $template): RedirectResponse
    {
        $this->authorizeOwner($template);
        $template->update(['enabled' => ! $template->enabled]);

        return back()->with('success', __('Template updated successfully.'));
    }

    public function destroy(Template $template): RedirectResponse
    {
        $this->authorizeOwner($template);
        $template->delete();

        return redirect()->route('template.index')->with('success', __('Template deleted successfully.'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        DB::transaction(function () use ($validated): void {
            Template::query()
                ->where('user_id', auth()->id())
                ->whereIn('id', $validated['ids'])
                ->delete();
        });

        return redirect()->route('template.index')->with('success', __('Selected Templates were deleted.'));
    }

    public function export(): StreamedResponse
    {
        return \Helper::csvDownload(
            'templates.csv',
            ['id', 'user_id', 'enabled', 'name', 'avatar', 'text', 'created_at', 'updated_at'],
            Template::query()
                ->where('user_id', auth()->id())
                ->orderBy('id')
                ->cursor()
                ->map(static fn (Template $template): array => [
                    $template->getKey(),
                    $template->user_id,
                    $template->enabled ? 1 : 0,
                    $template->name,
                    $template->avatar,
                    $template->text,
                    $template->created_at?->toDateTimeString(),
                    $template->updated_at?->toDateTimeString(),
                ]),
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'extensions:csv', 'max:10240'],
        ]);

        $filePath = $validated['file']->getRealPath();
        $stream = $filePath === false ? false : fopen($filePath, 'r');

        if ($stream === false) {
            throw new \RuntimeException('Unable to open the uploaded CSV file.');
        }

        $expectedHeaders = ['enabled', 'name', 'avatar', 'text'];
        $records = [];
        $rowNumber = 1;

        try {
            $headers = fgetcsv($stream, null, ',', '"', '');

            if ($headers === false) {
                throw ValidationException::withMessages([
                    'file' => __('The CSV file is empty.'),
                ]);
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]) ?? (string) $headers[0];

            if ($headers !== $expectedHeaders) {
                throw ValidationException::withMessages([
                    'file' => __('The CSV headers must be: :headers.', [
                        'headers' => implode(',', $expectedHeaders),
                    ]),
                ]);
            }

            while (true) {
                $row = fgetcsv($stream, null, ',', '"', '');

                if ($row === false) {
                    if (! feof($stream)) {
                        throw new \RuntimeException('Unable to read the uploaded CSV file.');
                    }

                    break;
                }

                $rowNumber++;

                if ($row === [null]) {
                    continue;
                }

                if (count($row) !== count($expectedHeaders)) {
                    throw ValidationException::withMessages([
                        'file' => __('CSV row :row has an unexpected number of columns.', [
                            'row' => $rowNumber,
                        ]),
                    ]);
                }

                $attributes = array_combine($expectedHeaders, $row);
                $attributes['avatar'] = $attributes['avatar'] === '' ? null : $attributes['avatar'];
                $attributes['text'] = $attributes['text'] === '' ? null : $attributes['text'];

                $validator = Validator::make($attributes, [
                    'enabled' => ['sometimes', 'boolean'],
                    'name' => ['required', 'string', 'max:255'],
                    'avatar' => ['nullable', 'url:http,https'],
                    'text' => ['required', 'string'],
                ]);

                if ($validator->fails()) {
                    throw ValidationException::withMessages([
                        'file' => __('CSV row :row is invalid: :error', [
                            'row' => $rowNumber,
                            'error' => $validator->errors()->first(),
                        ]),
                    ]);
                }

                $attributes = $validator->validated();

                $records[] = $attributes;
            }
        } finally {
            fclose($stream);
        }

        if ($records === []) {
            throw ValidationException::withMessages([
                'file' => __('The CSV file does not contain any records.'),
            ]);
        }

        DB::transaction(function () use ($records): void {
            foreach ($records as $attributes) {
                $template = new Template($attributes);
                $template->user()->associate(auth()->user());
                $template->save();
            }
        });

        return redirect()->route('template.index')->with('success', __('Imported :count Templates.', [
            'count' => count($records),
        ]));
    }

    private function authorizeOwner(Template $template): void
    {
        abort_unless($template->user_id === auth()->id(), 403, __('You are not the owner of this template.'));
    }
}