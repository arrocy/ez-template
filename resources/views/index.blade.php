@extends('layouts.main')

@section('title', __('Templates'))
@section('page-heading', __('Template overview'))

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">@lang('Templates')</h1>
            <p class="text-body-secondary mb-0">@lang('Search and manage Templates across your workspace.')</p>
        </div>
        <x-button variant="dark" type="button" data-bs-toggle="modal" data-bs-target="#createRecordModal">
            <i class="fa-solid fa-plus me-2"></i>@lang('Add New Template')
        </x-button>
    </div>

    <x-card>
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
            <form action="{{ route('template.index') }}" method="GET" class="d-flex gap-2">
                <div class="flex-grow-1">
                    <x-input name="search" id="search" type="search" label="" :value="request('search')" icon="fa-solid fa-magnifying-glass text-body-secondary" placeholder="Search Templates..." aria-label="Search Templates" />
                </div>
                <x-button variant="secondary" outline type="submit">@lang('Search')</x-button>
            </form>
            <div class="d-flex gap-2">
                <x-button variant="secondary" outline type="button" data-bs-toggle="modal" data-bs-target="#importRecordsModal">
                    <i class="fa-solid fa-file-import me-2"></i>@lang('Import')
                </x-button>
                <a href="{{ route('template.export') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-file-export me-2"></i>@lang('Export')</a>
            </div>
        </div>

        <form action="{{ route('template.bulk-destroy') }}" method="POST" data-bulk-delete-form>
            @csrf
            @method('DELETE')
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small text-body-secondary">{{ $templates->total() }} @lang('Templates found')</span>
                <x-button variant="danger" outline size="sm" type="submit" data-bulk-delete-button hidden><i class="fa-solid fa-trash-can me-1"></i>@lang('Bulk Delete')</x-button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col" style="width:2.5rem"><input class="form-check-input" type="checkbox" aria-label="Select all rows" data-select-all="input[name='ids[]']"></th>
                            <th scope="col">@lang('Name')</th>
                            <th scope="col">@lang('Text')</th>
                            <th scope="col" class="text-end">@lang('Actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($templates as $template)
                            <tr>
                                <td><input class="form-check-input" type="checkbox" name="ids[]" value="{{ $template->getKey() }}" aria-label="Select row"></td>
                                <td class="text-body-secondary"><div class="d-flex align-items-center gap-2"><x-avatar :src="$template->avatar" :name="$template->name" />{{ $template->name }}</div></td>
                                <td class="text-body-secondary">{{ $template->text === null ? '' : \Illuminate\Support\Str::limit($template->text, 37, '...') }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('template.toggle', $template) }}" class="btn btn-sm btn-outline-{{ $template->enabled ? 'success' : 'secondary' }}" title="Template {{ $template->enabled ? 'enabled' : 'disabled' }}. Click to change!" aria-label="Toggle enabled"><i class="fa-solid fa-toggle-{{ $template->enabled ? 'on' : 'off' }}"></i></a>
                                    <a href="{{ route('template.edit', $template) }}" class="btn btn-sm btn-outline-primary" title="Edit template" aria-label="Edit template"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <x-button variant="danger" outline size="sm" type="button" data-bs-toggle="modal" data-bs-target="#deleteRecordModal" :data-delete-url="route('template.destroy', $template)" :data-delete-name="$template->name" title="Delete template" aria-label="Delete template"><i class="fa-solid fa-trash-can"></i></x-button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-empty-state title="No Templates found" description="Try another search or add a new Template." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-top pt-3 mt-2">
                <span class="small text-body-secondary">@lang('Showing') {{ $templates->firstItem() ?? 0 }}–{{ $templates->lastItem() ?? 0 }} @lang('of') {{ $templates->total() }}</span>
                {{ $templates->links('pagination::bootstrap-5') }}
            </div>
        </form>
    </x-card>

    <x-modal id="deleteRecordModal" title="Delete Template?" data-delete-modal>
        <form id="delete-template-form" method="POST" action="{{ route('template.index') }}" data-delete-form>
            @csrf
            @method('DELETE')
            @lang('Are you sure you want to delete') <strong data-delete-name></strong>? @lang('This action cannot be undone.')
        </form>
        <x-slot:footer>
            <x-button variant="light" type="button" data-bs-dismiss="modal">@lang('Cancel')</x-button>
            <x-button variant="danger" type="submit" form="delete-template-form">@lang('Delete Template')</x-button>
        </x-slot:footer>
    </x-modal>

    <x-modal id="importRecordsModal" title="Import Templates">
        <form id="import-template-form" method="POST" action="{{ route('template.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="import-file">@lang('CSV file')</label>
                <input class="form-control @error('file') is-invalid @enderror" type="file" id="import-file" name="file" accept=".csv,text/csv" required>
                @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <p class="small text-body-secondary mb-2">@lang('CSV columns and example row:')</p>
            <pre class="bg-body-tertiary p-3 rounded small"><code>enabled,name,avatar,text
1,Example,https://picsum.photos/200/300,Example template text.</code></pre>
        </form>
        <x-slot:footer>
            <x-button variant="light" type="button" data-bs-dismiss="modal">@lang('Cancel')</x-button>
            <x-button variant="dark" type="submit" form="import-template-form">@lang('Import')</x-button>
        </x-slot:footer>
    </x-modal>

    <x-modal id="createRecordModal" title="Add New Template">
        <form id="create-template-form" method="POST" action="{{ route('template.store') }}">
            @csrf
            <div class="mb-3">
                <x-input name="name" id="new-name" label="Name" icon="fa-solid fa-tag" required />
            </div>
            <div class="mb-3">
                <x-textarea name="text" id="new-text" label="Text" icon="fa-solid fa-file-lines" rows="5" required />
            </div>
        </form>
        <x-slot:footer>
            <x-button variant="light" type="button" data-bs-dismiss="modal">@lang('Cancel')</x-button>
            <x-button variant="dark" type="submit" form="create-template-form">@lang('Create Template')</x-button>
        </x-slot:footer>
    </x-modal>
@endsection