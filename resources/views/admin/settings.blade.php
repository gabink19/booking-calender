@extends('admin.layouts.app')

@section('content')
<div style="display: flex; gap: 32px; flex-wrap: wrap;">
    <div class="card summary" style="flex:1 1 340px; min-width:320px;">
        <h2>Logo App</h2>
        @php
            $logoPath = $settings['app_logo'] ?? 'default-logo.png';
        @endphp
        <div style="display: flex; justify-content: center; align-items: center; min-height: 100px;">
            <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo App" style="max-height:150px;max-width:300px;border-radius:8px;box-shadow:0 1px 4px rgba(60,80,180,0.07);background:#fff;padding:6px;">
        </div>
        <div>
            <form action="{{ route('admin.settings.logo') }}" method="POST" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 12px; background: #ebeef7; border-radius: 8px; padding: 16px 18px; box-shadow: 0 1px 4px rgba(60,80,180,0.07);">
                @csrf
                <input type="file" name="logo" accept="image/*" required
                    style="width:70%;padding: 6px 12px; border: 1px solid #9ea8b7ff; border-radius: 6px; background: #fff; font-size: 13px; color: #3041b7; cursor: pointer;"/>
                <button type="submit" class="action-btn primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-icons" style="vertical-align:middle;">upload</span>
                    <span class="d-none d-md-inline">Simpan</span>
                </button>
            </form>
        </div>
    </div>
    <div class="card summary" style="flex:1 1 340px; min-width:320px;">
        <h2>Background App</h2>
        @php
            $backgroundPath = $settings['app_background'] ?? 'default-background.png';
        @endphp
        <div style="display: flex; justify-content: center; align-items: center; min-height: 100px;">
            <img src="{{ asset('storage/' . $backgroundPath) }}" alt="Background App" style="max-height:150px;max-width:300px;border-radius:8px;box-shadow:0 1px 4px rgba(60,80,180,0.07);background:#fff;padding:6px;">
        </div>
        <div>
            <form action="{{ route('admin.settings.background') }}" method="POST" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 12px; background: #ebeef7; border-radius: 8px; padding: 16px 18px; box-shadow: 0 1px 4px rgba(60,80,180,0.07);">
                @csrf
                <input type="file" name="background" accept="image/*" required
                    style="width:70%;padding: 6px 12px; border: 1px solid #9ea8b7ff; border-radius: 6px; background: #fff; font-size: 13px; color: #3041b7; cursor: pointer;"/>
                <button type="submit" class="action-btn primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-icons" style="vertical-align:middle;">upload</span>
                    <span class="d-none d-md-inline">Simpan</span>
                </button>
            </form>
        </div>
    </div>
    <div class="card summary">
        <h2>Informasi Aplikasi</h2>
        <div>
            <h3>Nomor Kontak :</h3>
            <form action="{{ route('admin.settings.info') }}" method="POST" class="info-form-responsive" style="display: flex; align-items: center; gap: 12px; background: #ebeef7; border-radius: 8px; padding: 16px 18px; box-shadow: 0 1px 4px rgba(60,80,180,0.07);">
                @csrf
                <input type="hidden" name="key" value="contact">
                <input type="text" name="value" required
                    style="width:80%!important;padding: 6px 12px; border: 1px solid #9ea8b7ff; border-radius: 6px; background: #fff; font-size: 13px; color: #3041b7; width: 100%;"
                    value="{{ $settings['contact'] ?? '' }}"/>
                <button type="submit" class="action-btn primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-icons" style="vertical-align:middle;">save</span>
                    <span class="d-none d-md-inline">Simpan</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"/>
<style>
    .info-form-responsive {
        width: 50%;
    }
    @media (max-width: 768px) {
        .info-form-responsive {
            width: 90% !important;
        }
        .d-none.d-md-inline {
            display: none;
        }
    }
</style>
@endpush