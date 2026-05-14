@extends('layouts.app')

@section('title', 'PDF Templates')

@section('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .btn-add {
        background: var(--primary);
        border: none;
        color: #fff;
        border-radius: var(--radius-sm);
        padding: 0.55rem 1.2rem;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.15s;
    }
    .btn-add:hover { background: var(--primary-dark); color: #fff; box-shadow: var(--shadow-sm); }

    .tpl-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
    @media (max-width: 1199px) { .tpl-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 767px) { .tpl-grid { grid-template-columns: 1fr; } }

    .tpl-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.35rem;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
    }
    .tpl-card:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .tpl-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 0.85rem;
    }
    .tpl-icon {
        width: 42px;
        height: 42px;
        border-radius: var(--radius-sm);
        background: rgba(220,53,69,0.08);
        color: #dc3545;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .tpl-lang {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        background: var(--primary-muted);
        color: var(--primary);
        text-transform: uppercase;
    }

    .tpl-name {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-dark);
        margin-top: 0.75rem;
        line-height: 1.35;
    }

    .tpl-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.75rem;
        flex: 1;
    }
    .tpl-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        background: var(--cream);
        color: var(--text-mid);
        border: 1px solid var(--border);
    }
    .tpl-tag i { font-size: 0.7rem; }

    .tpl-card-footer {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid var(--border);
    }
    .tpl-card-footer .btn { flex: 1; font-size: 0.8rem; font-weight: 600; border-radius: var(--radius-sm); }

    .empty-state {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        text-align: center;
        padding: 3rem;
    }
</style>
@endsection

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-0">PDF Templates</h3>
        @permission('templates.upload')
        <a href="{{ route('upload-template.create') }}" class="btn-add">
            <i class="bi bi-upload me-1"></i> Upload Template
        </a>
        @endpermission
    </div>

    @if($templates->isEmpty())
        <div class="empty-state">
            <i class="bi bi-file-earmark-pdf" style="font-size:2.5rem; color:var(--text-light);"></i>
            <h5 class="mt-3 text-muted fw-semibold">No templates uploaded yet</h5>
            <p class="text-muted mb-3">Upload a PDF template to get started with contract generation.</p>
            @permission('templates.upload')
            <a href="{{ route('upload-template.create') }}" class="btn-add">
                <i class="bi bi-upload me-1"></i> Upload Template
            </a>
            @endpermission
        </div>
    @else
        <div class="tpl-grid">
            @foreach($templates as $template)
                <div class="tpl-card">
                    <div class="tpl-card-top">
                        <span class="tpl-icon"><i class="bi bi-file-earmark-pdf"></i></span>
                        <span class="tpl-lang">{{ strtoupper($template->language) }}</span>
                    </div>
                    <div class="tpl-name">{{ ucwords(str_replace('_', ' ', $template->contract_type)) }}</div>
                    <div class="tpl-meta">
                        <span class="tpl-tag"><i class="bi bi-link-45deg"></i> {{ $template->mappings_count }} mappings</span>
                        <span class="tpl-tag"><i class="bi bi-calendar3"></i> {{ $template->created_at->timezone(config('app.timezone'))->format('d M Y') }}</span>
                    </div>

                    <div class="tpl-card-footer">
                        @permission('templates.manage_mappings')
                        <a href="{{ route('templates.mappings.show', $template) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-sliders me-1"></i>Mappings
                        </a>
                        @endpermission
                        @permission('templates.delete')
                        <form action="{{ route('templates.destroy', $template) }}" method="POST" class="flex-fill" onsubmit="return confirm('Delete this template? This will remove its PDF and mappings.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </form>
                        @endpermission
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
