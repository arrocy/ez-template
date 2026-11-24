@extends('layouts.main')

@section('title', __('Edit Template'))
@section('page-heading', __('Edit Template'))

@section('content')
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">@lang('Edit Template')</h1>
        <p class="text-body-secondary mb-0">{{ $template->name }}</p>
    </div>

    <x-card>
        <form method="POST" action="{{ route('template.update', $template) }}">
            @csrf
            @method('PUT')
            <div class="row row-cols-md-2 g-3 pb-4">
                <x-select name="enabled" id="edit-enabled" label="Enabled" :value="$template->enabled">
                    <option value="1" @selected((string) old('enabled', (int) $template->enabled) === '1')>Yes</option>
                    <option value="0" @selected((string) old('enabled', (int) $template->enabled) === '0')>No</option>
                </x-select>
                <x-input name="name" id="edit-name" :value="$template->name" label="Name" icon="fa-solid fa-tag" required />
                <x-input name="avatar" id="edit-avatar" :value="$template->avatar" label="Avatar" icon="fa-solid fa-image" />
            </div>
            <div class="pb-4">
                <x-textarea name="text" id="edit-text" :value="$template->text" icon="fa-solid fa-file-lines" rows="5" required />
            </div>
            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="{{ route('template.index') }}" class="btn btn-light">@lang('Cancel')</a>
                <x-button variant="dark" type="submit">@lang('Save changes')</x-button>
            </div>
        </form>
    </x-card>
@endsection