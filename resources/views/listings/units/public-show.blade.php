<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $unit->unit_type ?? 'Unit' }} {{ $unit->room_number }} | {{ $unit->project->name ?? '' }}</title>
    @php $faviconLogo = optional(\App\Models\Company::query()->select('logo')->first())->logo; @endphp
    @if($faviconLogo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $faviconLogo) }}">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2A8B92;
            --primary-dark: #1e6b71;
            --cream: #F7EFE2;
            --text-dark: #1a2e35;
            --text-mid: #3d5a61;
            --text-light: #6b8c93;
            --surface: #ffffff;
            --bg: #f5f2ee;
            --border: #e8e2d9;
            --radius: 12px;
            --radius-sm: 8px;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-dark);
            min-height: 100vh;
        }
        .public-header {
            background: linear-gradient(135deg, #1a2e35 0%, #1e3d44 100%);
            padding: 16px 0;
            text-align: center;
        }
        .public-header .brand {
            color: #fff;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.15rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .public-header .brand-icon {
            width: 30px;
            height: 30px;
            background: var(--primary);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .public-header .brand-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: inherit;
        }

        .public-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 32px 20px 60px;
        }

        /* Carousel */
        .hero-carousel {
            border-radius: var(--radius);
            overflow: hidden;
            background: var(--cream);
            aspect-ratio: 16/9;
        }
        .hero-carousel .carousel-inner,
        .hero-carousel .carousel-item { height: 100%; }
        .hero-carousel .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .hero-carousel .carousel-control-prev,
        .hero-carousel .carousel-control-next {
            width: 40px;
            height: 40px;
            top: 50%;
            transform: translateY(-50%);
            bottom: auto;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
        }
        .hero-carousel .carousel-control-prev { left: 12px; }
        .hero-carousel .carousel-control-next { right: 12px; }
        .hero-carousel .carousel-control-prev-icon,
        .hero-carousel .carousel-control-next-icon { width: 16px; height: 16px; }
        .carousel-indicators { margin-bottom: 10px; }
        .carousel-indicators [data-bs-target] {
            width: 8px; height: 8px; border-radius: 50%; border: none; opacity: 0.5;
        }
        .carousel-indicators .active { opacity: 1; }
        .placeholder-hero {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            color: var(--text-light); font-size: 4rem;
        }

        .info-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        .info-card h2 {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }
        .info-card .project-name {
            color: var(--text-mid);
            font-size: 0.95rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1.25rem;
        }
        @media (max-width: 480px) { .info-grid { grid-template-columns: 1fr; } }
        .info-item label {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-light);
            margin-bottom: 2px;
            display: block;
        }
        .info-item .value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .asset-section {
            margin-top: 1.5rem;
        }
        .asset-section h5 {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        .asset-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        @media (max-width: 480px) { .asset-grid { grid-template-columns: 1fr; } }
        .asset-grid img {
            width: 100%;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
        }
        .asset-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-light);
            margin-top: 6px;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="public-header">
        @php $companyLogoPath = optional(\App\Models\Company::query()->select('logo')->first())->logo; @endphp
        <span class="brand">
            <span class="brand-icon">
                @if(!empty($companyLogoPath))
                    <img src="{{ asset('storage/' . $companyLogoPath) }}" alt="Logo">
                @else
                    <i class="bi bi-building text-white"></i>
                @endif
            </span>
            Evante
        </span>
    </div>

    <div class="public-container">
        {{-- Room Photos Carousel --}}
        <div class="hero-carousel">
            @if($unit->listingImages->count())
                <div id="heroCarousel" class="carousel slide h-100" data-bs-ride="carousel">
                    @if($unit->listingImages->count() > 1)
                        <div class="carousel-indicators">
                            @foreach($unit->listingImages as $idx => $img)
                                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $idx }}" @if($idx === 0) class="active" @endif></button>
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
                        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    @endif
                </div>
            @else
                <div class="placeholder-hero"><i class="bi bi-image"></i></div>
            @endif
        </div>

        {{-- Unit Info --}}
        <div class="info-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2>{{ $unit->unit_code ?? $unit->room_number }}</h2>
                    <div class="project-name">
                        @if($unit->location)
                            <i class="bi bi-geo-alt me-1"></i>
                            {{ $unit->location->project_name }}
                            @if($unit->location->province)
                                ({{ $unit->location->province }})
                            @endif
                        @endif
                    </div>
                </div>
                @php
                    $colors = ['available'=>'success','reserved'=>'warning','contract'=>'info','installment'=>'primary','transferred'=>'secondary'];
                @endphp
                <span class="badge bg-{{ $colors[$unit->status] ?? 'dark' }} fs-6">{{ ucfirst($unit->status) }}</span>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <label>Bedrooms</label>
                    <div class="value">{{ $unit->bedrooms ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <label>Floor</label>
                    <div class="value">{{ $unit->floor ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <label>Area</label>
                    <div class="value">{{ $unit->area ? number_format($unit->area, 2) . ' sqm' : '-' }}</div>
                </div>
                <div class="info-item">
                    <label>Price per Room</label>
                    <div class="value">{{ $unit->price_per_room ? number_format($unit->price_per_room, 0) . ' THB' : '-' }}</div>
                </div>
            </div>
        </div>

        {{-- Floor Plan & Room Layout --}}
        @if($unit->floor_plan_image || $unit->room_layout_image)
            <div class="info-card asset-section">
                <h5>Floor Plan & Room Layout</h5>
                <div class="asset-grid">
                    @if($unit->floor_plan_image)
                        <div>
                            <img src="{{ asset('storage/' . $unit->floor_plan_image) }}" alt="Floor Plan">
                            <div class="asset-label">Floor Plan</div>
                        </div>
                    @endif
                    @if($unit->room_layout_image)
                        <div>
                            <img src="{{ asset('storage/' . $unit->room_layout_image) }}" alt="Room Layout">
                            <div class="asset-label">Room Layout</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
