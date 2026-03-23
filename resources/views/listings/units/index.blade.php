@extends('layouts.app')

@section('title', 'Listings (Units)')

@section('styles')
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .btn-import {
            background: var(--cream);
            border: 1px solid var(--border);
            color: var(--text-mid);
            border-radius: var(--radius-sm);
            padding: 0.55rem 1.2rem;
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.15s;
        }
        .btn-import:hover {
            background: var(--cream-dark);
            color: var(--text-dark);
        }
        .btn-add {
            background: var(--primary);
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.55rem 1.2rem;
            color: #fff;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-add:hover {
            background: var(--primary-dark);
            color: #fff;
            box-shadow: 0 4px 12px rgba(42,139,146,0.3);
            transform: translateY(-1px);
        }

        /* Filters */
        .listing-filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.85rem;
        }
        .filter-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-light);
            margin-bottom: 0.2rem;
        }
        .filter-actions {
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
        }
        .btn-clear {
            border: 1px solid var(--border);
            color: var(--text-mid);
        }

        /* Card grid */
        .listing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }
        @media (max-width: 1024px) { .listing-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .listing-grid { grid-template-columns: 1fr; } }

        .listing-card {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .listing-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        /* Carousel */
        .listing-carousel {
            position: relative;
            width: 100%;
            aspect-ratio: 16/10;
            background: var(--cream);
            overflow: hidden;
        }
        .listing-carousel .carousel-inner,
        .listing-carousel .carousel-item {
            height: 100%;
        }
        .listing-carousel .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .listing-carousel .carousel-control-prev,
        .listing-carousel .carousel-control-next {
            width: 32px;
            height: 32px;
            top: 50%;
            transform: translateY(-50%);
            bottom: auto;
            background: rgba(0,0,0,0.45);
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .listing-carousel .carousel-control-prev { left: 8px; }
        .listing-carousel .carousel-control-next { right: 8px; }
        .listing-card:hover .carousel-control-prev,
        .listing-card:hover .carousel-control-next { opacity: 1; }
        .listing-carousel .carousel-control-prev-icon,
        .listing-carousel .carousel-control-next-icon {
            width: 14px;
            height: 14px;
        }
        .carousel-indicators {
            margin-bottom: 6px;
        }
        .carousel-indicators [data-bs-target] {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            border: none;
            opacity: 0.5;
        }
        .carousel-indicators .active {
            opacity: 1;
        }
        .placeholder-img {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-size: 2.5rem;
        }

        .listing-card-body {
            padding: 1rem;
        }
        .listing-card-body .unit-type {
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-dark);
        }
        .listing-card-body .meta {
            font-size: 0.82rem;
            color: var(--text-mid);
            margin-top: 4px;
        }
        .listing-card-footer {
            padding: 0 1rem 1rem;
            display: flex;
            gap: 0.5rem;
        }
        .listing-card-footer .btn { font-size: 0.8rem; flex: 1; }

        .btn-copy-link {
            background: var(--cream);
            border: 1px solid var(--border);
            color: var(--text-mid);
            border-radius: var(--radius-sm);
            font-weight: 500;
            transition: all 0.15s;
        }
        .btn-copy-link:hover {
            background: var(--cream-dark);
            color: var(--text-dark);
        }
        .btn-copy-link.copied {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        /* Pagination */
        .pagination-shell {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
            padding: 0.9rem 1.25rem;
            margin-top: 1.5rem;
        }
        .pagination-summary {
            font-size: 0.85rem;
            color: var(--text-mid);
            font-weight: 500;
        }
        .pagination-mini {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.85rem;
        }
        .pagination-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50px;
            border: 1px solid var(--border);
            color: var(--text-mid);
            font-weight: 600;
            transition: all 0.15s ease;
            text-decoration: none;
            background: #fff;
        }
        .pagination-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .pagination-btn.active {
            border-color: var(--primary);
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(42,139,146,0.25);
        }
        .pagination-btn.disabled,
        .pagination-btn.disabled:hover {
            border-color: var(--border);
            color: #c0c5cb;
            cursor: not-allowed;
            background: #f3f4f6;
            box-shadow: none;
        }
        .pagination-ellipsis {
            color: var(--text-light);
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    @if(session('import_errors') && count(session('import_errors')))
    <div class="alert alert-warning alert-dismissible fade show">
        <strong><i class="bi bi-exclamation-triangle me-1"></i> Rows skipped during import:</strong>
        <ul class="mb-0 mt-1">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="page-header">
        <h3 class="mb-0 fw-bold">Listings (Units)</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('units.import.form') }}" class="btn-import">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Excel
            </a>
            <a href="{{ route('units.create') }}" class="btn-add">
                <i class="bi bi-plus me-1"></i> Add Listing
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" id="filterForm" class="listing-filters mb-2">
                <div>
                    <div class="filter-label">Search</div>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Room number, unit code..." value="{{ request('search') }}">
                </div>
                <div>
                    <div class="filter-label">Building</div>
                    <select name="project" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Buildings</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="filter-label">Status</div>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        @foreach(['available','reserved','contract','installment','transferred'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="filter-label">Bedrooms</div>
                    <select name="bedrooms" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach(($bedroomOptions ?? collect()) as $bedOption)
                            <option value="{{ $bedOption }}" {{ request('bedrooms') == $bedOption ? 'selected' : '' }}>{{ $bedOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    @if(request()->hasAny(['search','project','status','bedrooms']))
                        <a href="{{ route('units.index') }}" class="btn btn-sm btn-outline-secondary btn-clear">Clear Filters</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Card Grid --}}
    @if($listings->count())
        <div class="listing-grid">
            @foreach($listings as $unit)
                @php
                    $colors = ['available'=>'success','reserved'=>'warning','contract'=>'info','installment'=>'primary','transferred'=>'secondary'];
                    $carouselId = 'carousel-' . $unit->id;
                @endphp
                <div class="listing-card">
                    {{-- Image Carousel --}}
                    <div class="listing-carousel">
                        @if($unit->listingImages->count())
                            <div id="{{ $carouselId }}" class="carousel slide h-100" data-bs-ride="false">
                                @if($unit->listingImages->count() > 1)
                                    <div class="carousel-indicators">
                                        @foreach($unit->listingImages as $idx => $img)
                                            <button type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide-to="{{ $idx }}" @if($idx === 0) class="active" @endif></button>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="carousel-inner h-100">
                                    @foreach($unit->listingImages as $idx => $img)
                                        <div class="carousel-item h-100 @if($idx === 0) active @endif">
                                            <img src="{{ asset('storage/' . $img->image_path) }}" alt="Room photo">
                                        </div>
                                    @endforeach
                                </div>
                                @if($unit->listingImages->count() > 1)
                                    <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon"></span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                                        <span class="carousel-control-next-icon"></span>
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="placeholder-img">
                                <i class="bi bi-image"></i>
                            </div>
                        @endif
                    </div>

                    {{-- Card Body --}}
                    <div class="listing-card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="unit-type">{{ $unit->unit_code ?? $unit->room_number }}</div>
                                <div class="meta">
                                    {{ $unit->project->name ?? '-' }}
                                    @if($unit->floor) &middot; Floor {{ $unit->floor }} @endif
                                </div>
                            </div>
                            <span class="badge bg-{{ $colors[$unit->status] ?? 'dark' }}">{{ ucfirst($unit->status) }}</span>
                        </div>
                        <div class="d-flex gap-3 mt-2" style="font-size:0.82rem;">
                            <span><i class="bi bi-arrows-angle-expand me-1"></i>{{ $unit->area ? number_format($unit->area, 2) . ' sqm' : '-' }}</span>
                            <span><i class="bi bi-tag me-1"></i>{{ $unit->price_per_room ? number_format($unit->price_per_room, 0) . ' THB' : '-' }}</span>
                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="listing-card-footer">
                        <a href="{{ route('units.edit', $unit) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>See Detail
                        </a>
                        <button type="button" class="btn btn-sm btn-copy-link" onclick="copyLink(this, '{{ route('public.listing.show', $unit) }}')">
                            <i class="bi bi-link-45deg me-1"></i>Copy Link
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        @if($listings->total() > 0)
            @php
                $firstItem = $listings->firstItem();
                $lastItem = $listings->lastItem();
                $totalItems = $listings->total();
                $currentPage = $listings->currentPage();
                $lastPage = $listings->lastPage();
            @endphp

            <div class="pagination-shell">
                <div class="pagination-summary">
                    Showing {{ $firstItem }} to {{ $lastItem }} of {{ number_format($totalItems) }} results
                </div>
                @if($listings->hasPages())
                    <div class="pagination-mini">
                        @php
                            $prevUrl = $listings->previousPageUrl();
                            $nextUrl = $listings->nextPageUrl();
                        @endphp
                        <a class="pagination-btn {{ $listings->onFirstPage() ? 'disabled' : '' }}" href="{{ $listings->onFirstPage() ? '#' : $prevUrl }}" aria-label="Previous page">
                            &lt;
                        </a>

                        <a class="pagination-btn {{ $currentPage === 1 ? 'active' : '' }}" href="{{ $listings->url(1) }}">1</a>

                        @if($currentPage > 2)
                            <span class="pagination-ellipsis">&hellip;</span>
                        @endif

                        @if($currentPage > 1 && $currentPage < $lastPage)
                            <a class="pagination-btn active" href="{{ $listings->url($currentPage) }}">{{ $currentPage }}</a>
                        @endif

                        @if($currentPage < $lastPage - 1)
                            <span class="pagination-ellipsis">&hellip;</span>
                        @endif

                        @if($lastPage > 1)
                            <a class="pagination-btn {{ $currentPage === $lastPage ? 'active' : '' }}" href="{{ $listings->url($lastPage) }}">{{ $lastPage }}</a>
                        @endif

                        <a class="pagination-btn {{ $currentPage === $lastPage ? 'disabled' : '' }}" href="{{ $currentPage === $lastPage ? '#' : $nextUrl }}" aria-label="Next page">
                            &gt;
                        </a>
                    </div>
                @endif
            </div>
        @endif
    @else
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-inbox" style="font-size:2rem;"></i>
                <p class="mt-2 mb-0">No listings found.</p>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
<script>
    function copyLink(btn, url) {
        navigator.clipboard.writeText(url).then(function() {
            btn.classList.add('copied');
            btn.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
            setTimeout(function() {
                btn.classList.remove('copied');
                btn.innerHTML = '<i class="bi bi-link-45deg me-1"></i>Copy Link';
            }, 2000);
        });
    }
</script>
@endsection
