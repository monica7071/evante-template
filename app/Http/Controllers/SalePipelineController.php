<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Location;
use App\Models\Project;
use App\Models\Reservation;
use App\Models\Sale;
use App\Models\SaleAppointment;
use App\Models\SalePurchaseAgreement;
use App\Models\SalePurchaseAgreementInstallment;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SalePipelineController extends Controller
{
    private array $statusFlow = [
        'appointment' => [
            'label' => 'Appointment',
            'icon' => 'bi-calendar-check',
            'color' => '#7c3aed',
            'bg' => 'rgba(124,58,237,0.12)',
        ],
        'available' => [
            'label' => 'Available',
            'icon' => 'bi-check-circle',
            'color' => '#12b76a',
            'bg' => 'rgba(18,183,106,0.12)',
        ],
        'reserved' => [
            'label' => 'Reserved',
            'icon' => 'bi-bookmark-check',
            'color' => '#0ba5ec',
            'bg' => 'rgba(11,165,236,0.12)',
        ],
        'contract' => [
            'label' => 'Contract',
            'icon' => 'bi-file-earmark-text',
            'color' => '#f79009',
            'bg' => 'rgba(247,144,9,0.12)',
        ],
        'installment' => [
            'label' => 'Installment',
            'icon' => 'bi-credit-card',
            'color' => '#6f42c1',
            'bg' => 'rgba(111,66,193,0.12)',
        ],
        'transferred' => [
            'label' => 'Transferred',
            'icon' => 'bi-patch-check',
            'color' => '#101828',
            'bg' => 'rgba(16,24,40,0.08)',
        ],
    ];

    private array $statusSequence = ['appointment', 'available', 'reserved', 'contract', 'installment', 'transferred'];

    private array $remarkColumns = [
        'available' => 'remark_available',
        'reserved' => 'remark_reserved',
        'contract' => 'remark_contract',
        'installment' => 'remark_installment',
        'transferred' => 'remark_transferred',
    ];

    public function index(Request $request)
    {
        $status = $request->query('status');
        $projectId = $request->query('project');
        $unitCode = $request->query('unit_code');
        $unitType = $request->query('unit_type');
        $bedrooms = $request->query('bedrooms');

        $query = Sale::with(['listing.project.location', 'user', 'purchaseAgreement', 'appointment'])
            ->withSum('purchaseAgreementInstallments', 'amount_number');

        if ($status && array_key_exists($status, $this->statusFlow)) {
            $query->where('status', $status);
        }

        if ($projectId) {
            $query->whereHas('listing', fn ($q) => $q->where('project_id', $projectId));
        }

        if ($unitCode) {
            $query->whereHas('listing', function ($q) use ($unitCode) {
                $q->where('unit_code', 'like', '%' . trim($unitCode) . '%');
            });
        }

        if ($unitType) {
            $query->whereHas('listing', fn ($q) => $q->where('unit_type', $unitType));
        }

        if ($bedrooms !== null && $bedrooms !== '') {
            $query->whereHas('listing', fn ($q) => $q->where('bedrooms', $bedrooms));
        }

        $sales = $query->latest()->paginate(12)->withQueryString();

        $counts = ['all' => Sale::count()];
        foreach ($this->statusFlow as $key => $definition) {
            $counts[$key] = Sale::where('status', $key)->count();
        }

        $projects = Project::orderBy('name')->get(['id', 'name']);
        $unitTypes = Listing::whereNotNull('unit_type')->distinct()->orderBy('unit_type')->pluck('unit_type');
        $bedroomOptions = Listing::whereNotNull('bedrooms')->distinct()->orderBy('bedrooms')->pluck('bedrooms');

        $statusFlow = $this->statusFlow;
        $remarkColumns = $this->remarkColumns;

        return view('buy-sale.index', compact('sales', 'counts', 'status', 'projects', 'projectId', 'statusFlow', 'unitCode', 'remarkColumns', 'unitType', 'bedrooms', 'unitTypes', 'bedroomOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'remark_appointment' => 'nullable|string|max:2000',
        ]);

        $sale = Sale::create([
            'listing_id' => null,
            'user_id' => $request->user()?->id,
            'status' => 'appointment',
        ]);

        $sale->appointment()->create([
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'remark' => $request->remark_appointment,
        ]);

        $this->recordStatusHistory($sale, 'appointment', null, 'Appointment created', $request->user()?->id);

        return redirect()->route('buy-sale.index')->with('success', 'Appointment created successfully.');
    }

    public function getProjects(Location $location)
    {
        return response()->json(
            $location->projects()->orderBy('name')->get(['id', 'name'])
        );
    }

    public function getFloors(Project $project)
    {
        $floors = Listing::where('project_id', $project->id)
            ->whereNotNull('floor')
            ->distinct()
            ->orderBy('floor')
            ->pluck('floor');

        return response()->json($floors);
    }

    public function getUnits(Project $project, $floor)
    {
        $units = Listing::where('project_id', $project->id)
            ->where('floor', $floor)
            ->whereDoesntHave('sales', function ($q) {
                $q->where('status', '!=', 'transferred');
            })
            ->orderBy('room_number')
            ->get(['id', 'room_number', 'unit_code', 'unit_type', 'area', 'price_per_room']);

        return response()->json($units);
    }

    public function form(Sale $sale, string $type)
    {
        $sale->load('listing.project.location');

        $allowedTypes = ['reserved', 'contract'];
        if (!in_array($type, $allowedTypes)) {
            return redirect()->route('buy-sale.index')->with('error', 'Invalid form type.');
        }

        $formTitles = ['reserved' => 'Reservation', 'contract' => 'Contract'];
        $formTitle = $formTitles[$type];
        $statusFlow = $this->statusFlow;
        $reservationRecord = Reservation::where('listing_id', $sale->listing_id)->latest()->first();

        $provinceOptions = $this->loadThaiData('provinces', 'provinces');
        $districtOptions = $this->loadThaiData('districts', 'districts');
        $subDistrictOptions = $this->loadThaiData('sub_districts', 'sub_districts');

        return view('buy-sale.form', compact(
            'sale',
            'type',
            'formTitle',
            'statusFlow',
            'reservationRecord',
            'provinceOptions',
            'districtOptions',
            'subDistrictOptions'
        ));
    }

    public function advance(Request $request, Sale $sale): RedirectResponse
    {
        $action = $request->input('save_action', 'submit');
        $isDraft = $action === 'draft';
        $currentIndex = array_search($sale->status, $this->statusSequence, true);

        if ($currentIndex === false || $currentIndex === count($this->statusSequence) - 1) {
            return back()->with('error', 'This sale is already at the final step.');
        }

        $nextStatus = $this->statusSequence[$currentIndex + 1];

        if ($nextStatus === 'available') {
            $data = $request->validate([
                'listing_id' => 'required|exists:listings,id',
            ]);

            $listing = Listing::findOrFail($data['listing_id']);

            // Check if listing already has an active sale
            $existingSale = Sale::where('listing_id', $listing->id)
                ->where('status', '!=', 'transferred')
                ->where('id', '!=', $sale->id)
                ->first();

            if ($existingSale) {
                return back()->with('error', 'This unit already has an active sale.');
            }

            $sale->listing_id = $listing->id;
        } elseif ($nextStatus === 'reserved') {
            if ($isDraft) {
                $sale->reservation_data = $this->buildReservationFormState($request->all(), $sale);
                $sale->save();

                return redirect()->route('buy-sale.form', ['sale' => $sale->id, 'type' => 'reserved'])
                    ->with('success', 'Reservation draft saved.');
            }

            if ($request->has('reservation_amount_paid_number')) {
                $request->merge([
                    'reservation_amount_paid_number' => str_replace(',', '', (string) $request->input('reservation_amount_paid_number', '')),
                ]);
            }

            $data = $request->validate([
                'reservation_first_name' => 'required|string|max:255',
                'reservation_last_name' => 'required|string|max:255',
                'reservation_id_type' => 'required|in:id_card,passport',
                'reservation_id_number' => 'required|string|max:13',
                'reservation_nationality' => 'nullable|string|max:255',
                'reservation_address' => 'required|string',
                'reservation_phone' => 'required|string|max:50',
                'reservation_email' => 'nullable|email|max:255',
                'reservation_date' => 'required|date',
                'reservation_amount_paid_number' => 'required|numeric|min:0',
                'reservation_amount_paid_text' => 'required|string|max:255',
                'reservation_contract_start_date' => 'required|date',
                'reservation_buyer_signature_name' => 'required|string|max:255',
                'reservation_seller_name' => 'required|string|max:255',
                'reservation_witness_one_name' => 'nullable|string|max:255',
                'reservation_witness_two_name' => 'nullable|string|max:255',
            ]);

            $reservationPayload = [
                'listing_id' => $sale->listing_id ?? $data['listing_id'] ?? null,
                'buyer_first_name' => $data['reservation_first_name'],
                'buyer_last_name' => $data['reservation_last_name'],
                'buyer_full_name' => trim($data['reservation_first_name'] . ' ' . $data['reservation_last_name']),
                'buyer_id_type' => $data['reservation_id_type'],
                'buyer_id_number' => $data['reservation_id_number'],
                'buyer_nationality' => $data['reservation_nationality'] ?? null,
                'buyer_address' => $data['reservation_address'],
                'buyer_phone' => $data['reservation_phone'],
                'buyer_email' => $data['reservation_email'] ?? null,
                'reservation_date' => $data['reservation_date'],
                'reservation_amount' => $sale->listing->reservation_deposit,
                'amount_paid_number' => $data['reservation_amount_paid_number'],
                'amount_paid_text' => $data['reservation_amount_paid_text'],
                'contract_start_date' => $data['reservation_contract_start_date'],
                'buyer_signature_name' => $data['reservation_buyer_signature_name'],
                'seller_name' => $data['reservation_seller_name'],
                'witness_one_name' => $data['reservation_witness_one_name'],
                'witness_two_name' => $data['reservation_witness_two_name'],
            ];

            Reservation::updateOrCreate(
                ['listing_id' => $sale->listing_id],
                $reservationPayload
            );

            $sale->reservation_data = $this->buildReservationFormState($data, $sale);
        } elseif ($nextStatus === 'contract') {
            // Strip thousand-separator commas from formatted number fields
            $numericFields = [
                'contract_price_per_sqm_number',
                'contract_area_sqm',
                'contract_total_price_number',
                'contract_adjustment_number',
                'contract_deposit_number',
                'contract_payment_number',
                'contract_installment_total_number',
                'contract_remaining_number',
            ];
            foreach ($numericFields as $field) {
                if ($request->has($field)) {
                    $request->merge([$field => str_replace(',', '', (string) $request->input($field, ''))]);
                }
            }
            if ($request->has('contract_installment_amount_number')) {
                $request->merge([
                    'contract_installment_amount_number' => array_map(
                        fn ($v) => $v !== null && $v !== '' ? str_replace(',', '', (string) $v) : $v,
                        (array) $request->input('contract_installment_amount_number', [])
                    ),
                ]);
            }

            // ── Draft: save raw input without validation ──
            if ($isDraft) {
                $sale->contract_data = $this->buildContractFormState($request->all(), $sale);
                $sale->save();

                return redirect()->route('buy-sale.form', ['sale' => $sale->id, 'type' => 'contract'])
                    ->with('success', 'Contract draft saved.');
            }

            // ── Submit: full validation required ──
            $data = $request->validate([
                'contract_number' => 'nullable|string|max:255',
                'contract_date' => 'required|date',
                'contract_full_name' => 'required|string|max:255',
                'contract_phone' => 'required|string|max:50',
                'contract_house_no' => 'required|string|max:255',
                'contract_village_no' => 'nullable|string|max:255',
                'contract_street' => 'nullable|string|max:255',
                'contract_province' => 'required|string|max:255',
                'contract_district' => 'required|string|max:255',
                'contract_subdistrict' => 'required|string|max:255',
                'contract_postal_code' => 'nullable|string|max:10',
                'contract_project_name' => 'required|string|max:255',
                'contract_floor' => 'required|string|max:255',
                'contract_room_number' => 'required|string|max:255',
                'contract_unit_type' => 'required|string|max:255',
                'contract_quantity' => 'required|integer|min:1',
                'contract_price_per_sqm_number' => 'required|numeric|min:0',
                'contract_area_sqm' => 'required|numeric|min:0',
                'contract_total_price_number' => 'required|numeric|min:0',
                'contract_total_price_text' => 'required|string',
                'contract_adjustment_number' => 'required|numeric',
                'contract_adjustment_text' => 'required|string',
                'contract_deposit_number' => 'required|numeric|min:0',
                'contract_deposit_text' => 'required|string',
                'contract_deposit_date' => 'required|date',
                'contract_payment_number' => 'required|numeric|min:0',
                'contract_payment_text' => 'required|string',
                'contract_payment_date' => 'required|date',
                'contract_installment_total_number' => 'required|numeric|min:0',
                'contract_installment_total_text' => 'required|string',
                'contract_remaining_number' => 'required|numeric|min:0',
                'contract_remaining_text' => 'required|string',
                'contract_payment_type' => 'required|in:bank_loan,cash_transfer',
                'contract_installment_count' => 'nullable|integer|min:1|max:36',
                'contract_installment_amount_number' => 'nullable|array',
                'contract_installment_amount_number.*' => 'nullable|numeric|min:0',
                'contract_installment_amount_text' => 'nullable|array',
                'contract_installment_amount_text.*' => 'nullable|string',
                'contract_installment_date' => 'nullable|array',
                'contract_installment_date.*' => 'nullable|date',
                'contract_seller_name' => 'required|string|max:255',
                'contract_buyer_signature_name' => 'required|string|max:255',
                'contract_witness_one_name' => 'nullable|string|max:255',
                'contract_witness_two_name' => 'nullable|string|max:255',
            ]);

            $data['contract_witness_one_name'] = $data['contract_witness_one_name'] ?? '';
            $data['contract_witness_two_name'] = $data['contract_witness_two_name'] ?? '';

            $contractPayload = $this->buildContractFormState($data, $sale);
            $installments = $contractPayload['pricing']['installments'];

            $sale->contract_data = $contractPayload;

            $agreement = SalePurchaseAgreement::updateOrCreate(
                ['sale_id' => $sale->id],
                [
                    'sale_id' => $sale->id,
                    'listing_id' => $sale->listing_id,
                    'contract_number' => $contractPayload['contract_number'],
                    'contract_date' => $contractPayload['contract_date'],
                    'buyer_full_name' => $contractPayload['buyer_full_name'],
                    'buyer_phone' => $contractPayload['phone'],
                    'house_no' => $contractPayload['address']['house_no'],
                    'village_no' => $contractPayload['address']['village_no'],
                    'street' => $contractPayload['address']['street'],
                    'province' => $contractPayload['address']['province'],
                    'district' => $contractPayload['address']['district'],
                    'subdistrict' => $contractPayload['address']['subdistrict'],
                    'postal_code' => $contractPayload['address']['postal_code'],
                    'project_name' => $contractPayload['project']['name'],
                    'floor' => $contractPayload['project']['floor'],
                    'room_number' => $contractPayload['project']['room_number'],
                    'unit_type' => $contractPayload['project']['unit_type'],
                    'quantity' => $contractPayload['project']['quantity'],
                    'price_per_sqm_number' => $contractPayload['project']['price_per_sqm_number'],
                    'area_sqm' => $contractPayload['project']['area_sqm'],
                    'total_price_number' => $contractPayload['pricing']['total_price_number'],
                    'total_price_text' => $contractPayload['pricing']['total_price_text'],
                    'adjustment_number' => $contractPayload['pricing']['adjustment_number'],
                    'adjustment_text' => $contractPayload['pricing']['adjustment_text'],
                    'deposit_number' => $contractPayload['pricing']['deposit_number'],
                    'deposit_text' => $contractPayload['pricing']['deposit_text'],
                    'deposit_date' => $contractPayload['pricing']['deposit_date'],
                    'contract_payment_number' => $contractPayload['pricing']['contract_payment_number'],
                    'contract_payment_text' => $contractPayload['pricing']['contract_payment_text'],
                    'contract_payment_date' => $contractPayload['pricing']['contract_payment_date'],
                    'installment_total_number' => $contractPayload['pricing']['installment_total_number'],
                    'installment_total_text' => $contractPayload['pricing']['installment_total_text'],
                    'remaining_number' => $contractPayload['pricing']['remaining_number'],
                    'remaining_text' => $contractPayload['pricing']['remaining_text'],
                    'total_term' => $contractPayload['pricing']['installment_count'],
                    'is_bank_loan' => $contractPayload['payment_type'] === 'bank_loan' ? 1 : 0,
                    'is_cash_transfer' => $contractPayload['payment_type'] === 'cash_transfer' ? 1 : 0,
                    'installments' => $contractPayload['pricing']['installments'],
                    'seller_name' => $contractPayload['signatures']['seller_name'],
                    'buyer_signature_name' => $contractPayload['signatures']['buyer_name'],
                    'witness_one_name' => $contractPayload['signatures']['witness_one_name'],
                    'witness_two_name' => $contractPayload['signatures']['witness_two_name'],
                ]
            );

            if ($agreement) {
                $agreement->installments()->delete();

                foreach ($installments as $index => $installment) {
                    if (
                        ($installment['amount_number'] ?? null) === null &&
                        ($installment['amount_text'] ?? null) === null &&
                        ($installment['date'] ?? null) === null
                    ) {
                        continue;
                    }

                    $agreement->installments()->create([
                        'sequence' => $index + 1,
                        'amount_number' => $installment['amount_number'],
                        'amount_text' => $installment['amount_text'],
                        'due_date' => $installment['date'],
                    ]);
                }
            }
        }

        if (!$isDraft) {
            $previousStatus = $sale->status;
            $sale->previous_status = $previousStatus;
            $sale->status = $nextStatus;
            $sale->save();

            if ($sale->listing_id) {
                if ($sale->relationLoaded('listing')) {
                    $sale->listing?->update(['status' => $nextStatus]);
                } else {
                    Listing::where('id', $sale->listing_id)->update(['status' => $nextStatus]);
                }
            }

            $this->recordStatusHistory($sale, $nextStatus, $previousStatus, null, $request->user()?->id);

            return redirect()->route('buy-sale.index', [
                'status' => $nextStatus,
                'highlight' => $sale->id,
            ])->with('success', "Sale advanced to {$this->statusFlow[$nextStatus]['label']} stage.");
        }
        return redirect()->route('buy-sale.index', [
            'status' => $nextStatus,
            'highlight' => $sale->id,
        ])->with('success', "Sale advanced to {$this->statusFlow[$nextStatus]['label']} stage.");
    }

    public function installments(Sale $sale)
    {
        if (!in_array($sale->status, ['installment', 'transferred'], true)) {
            return redirect()->route('buy-sale.index')->with('error', 'Installment tracking is only available for installment stage.');
        }

        $sale->load(['listing.project', 'purchaseAgreement.installments']);
        $agreement = $sale->purchaseAgreement;
        $installments = $agreement?->installments()->orderBy('sequence')->get() ?? collect();
        $today = now()->startOfDay();
        $statusFlow = $this->statusFlow;

        return view('buy-sale.installments', compact('sale', 'agreement', 'installments', 'today', 'statusFlow'));
    }

    public function uploadProof(Request $request, Sale $sale, SalePurchaseAgreementInstallment $installment): RedirectResponse
    {
        $request->validate([
            'proof_image' => 'required|image|max:5120',
        ]);

        if ($installment->agreement->sale_id !== $sale->id) {
            abort(403);
        }

        if ($installment->proof_image) {
            Storage::disk('public')->delete($installment->proof_image);
        }

        $path = $request->file('proof_image')->store('installment-proofs', 'public');
        $installment->update(['proof_image' => $path]);

        return back()->with('success', 'Payment proof uploaded successfully.');
    }

    public function cancel(Request $request, Sale $sale): RedirectResponse
    {
        if ($sale->status === 'appointment') {
            return back()->with('error', 'Sale is already at appointment status.');
        }

        // Delete related form data
        if ($sale->listing_id) {
            Reservation::where('listing_id', $sale->listing_id)->delete();
        }

        if ($sale->purchaseAgreement) {
            $sale->purchaseAgreement->installments()->delete();
            $sale->purchaseAgreement->delete();
        }

        // Delete status histories except appointment
        $sale->statusHistories()->where('status', '!=', 'appointment')->delete();

        // Reset listing status if linked
        if ($sale->listing_id) {
            Listing::where('id', $sale->listing_id)->update(['status' => 'available']);
        }

        // Reset sale
        $sale->status = 'appointment';
        $sale->previous_status = null;
        $sale->listing_id = null;
        $sale->reservation_data = null;
        $sale->contract_data = null;
        $sale->remark_available = null;
        $sale->remark_reserved = null;
        $sale->remark_contract = null;
        $sale->remark_installment = null;
        $sale->remark_transferred = null;
        $sale->save();

        return redirect()->route('buy-sale.index', [
            'highlight' => $sale->id,
        ])->with('success', 'Sale has been cancelled and reset to Appointment.');
    }

    public function updateRemark(Request $request, Sale $sale): RedirectResponse
    {
        $status = $request->input('status');
        if ($status !== 'appointment' && !isset($this->remarkColumns[$status])) {
            abort(404);
        }

        $data = $request->validate([
            'remark' => 'nullable|string|max:2000',
        ]);

        if ($status === 'appointment') {
            $appointment = $sale->appointment;
            if ($appointment) {
                $appointment->update(['remark' => $data['remark']]);
            }
        } else {
            $column = $this->remarkColumns[$status];
            $sale->$column = $data['remark'];
            $sale->save();
        }

        $filters = array_filter([
            'status' => $request->input('current_filter_status'),
            'project' => $request->input('current_filter_project'),
            'unit_code' => $request->input('current_filter_unit'),
        ], fn ($value) => filled($value));

        $filters['highlight'] = $sale->id;

        return redirect()->route('buy-sale.index', $filters)
            ->with('success', 'Remark saved successfully.');
    }

    public function saveQuotationVisitor(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'visitor_name' => 'required|string|max:255',
            'visitor_phone' => 'required|string|max:50',
            'language' => 'required|in:th,en',
        ]);

        $sale->update([
            'avail_name' => $data['visitor_name'],
            'avail_tel' => $data['visitor_phone'],
        ]);

        return response()->json([
            'success' => true,
            'redirect_url' => route('contracts.quotation.preview-listing', [
                'listing' => $sale->listing_id,
                'language' => $data['language'],
            ]),
        ]);
    }

    private function loadThaiData(string $filename, string $key): array
    {
        $path = public_path("data/thai/{$filename}.json");
        if (!File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) && array_key_exists($key, $decoded)
            ? $decoded[$key]
            : [];
    }

    private function buildReservationFormState(array $source, Sale $sale): array
    {
        return [
            'first_name' => data_get($source, 'reservation_first_name'),
            'last_name' => data_get($source, 'reservation_last_name'),
            'id_type' => data_get($source, 'reservation_id_type', 'id_card'),
            'id_number' => data_get($source, 'reservation_id_number'),
            'nationality' => data_get($source, 'reservation_nationality'),
            'address' => data_get($source, 'reservation_address'),
            'phone' => data_get($source, 'reservation_phone'),
            'email' => data_get($source, 'reservation_email'),
            'reservation_date' => data_get($source, 'reservation_date'),
            'reservation_amount' => $sale->listing->reservation_deposit,
            'amount_paid_number' => data_get($source, 'reservation_amount_paid_number'),
            'amount_paid_text' => data_get($source, 'reservation_amount_paid_text'),
            'contract_start_date' => data_get($source, 'reservation_contract_start_date'),
            'buyer_signature_name' => data_get($source, 'reservation_buyer_signature_name'),
            'seller_name' => data_get($source, 'reservation_seller_name'),
            'witness_one_name' => data_get($source, 'reservation_witness_one_name'),
            'witness_two_name' => data_get($source, 'reservation_witness_two_name'),
        ];
    }

    private function recordStatusHistory(
        Sale $sale,
        string $status,
        ?string $previousStatus = null,
        ?string $notes = null,
        ?int $userId = null,
    ): void {
        $sale->statusHistories()->create([
            'status' => $status,
            'previous_status' => $previousStatus,
            'notes' => $notes,
            'user_id' => $userId,
        ]);
    }
    private function buildContractFormState(array $source, Sale $sale): array
    {
        $numbers = data_get($source, 'contract_installment_amount_number', []);
        $texts = data_get($source, 'contract_installment_amount_text', []);
        $dates = data_get($source, 'contract_installment_date', []);

        $numbers = is_array($numbers) ? $numbers : [];
        $texts = is_array($texts) ? $texts : [];
        $dates = is_array($dates) ? $dates : [];

        $maxRows = max(count($numbers), count($texts), count($dates));
        $installments = [];

        for ($i = 0; $i < $maxRows; $i++) {
            $amountNumber = $numbers[$i] ?? null;
            $amountText = $texts[$i] ?? null;
            $date = $dates[$i] ?? null;

            if ($amountNumber === null && $amountText === null && $date === null) {
                continue;
            }

            $installments[] = [
                'amount_number' => $amountNumber,
                'amount_text' => $amountText,
                'date' => $date,
            ];
        }

        return [
            'contract_number' => data_get($source, 'contract_number', $sale->listing->unit_code),
            'contract_date' => data_get($source, 'contract_date'),
            'buyer_full_name' => data_get($source, 'contract_full_name'),
            'phone' => data_get($source, 'contract_phone'),
            'payment_type' => data_get($source, 'contract_payment_type'),
            'address' => [
                'house_no' => data_get($source, 'contract_house_no'),
                'village_no' => data_get($source, 'contract_village_no'),
                'street' => data_get($source, 'contract_street'),
                'province' => data_get($source, 'contract_province'),
                'district' => data_get($source, 'contract_district'),
                'subdistrict' => data_get($source, 'contract_subdistrict'),
                'postal_code' => data_get($source, 'contract_postal_code'),
            ],
            'project' => [
                'name' => data_get($source, 'contract_project_name'),
                'floor' => data_get($source, 'contract_floor'),
                'room_number' => data_get($source, 'contract_room_number'),
                'unit_type' => data_get($source, 'contract_unit_type'),
                'quantity' => data_get($source, 'contract_quantity'),
                'price_per_sqm_number' => data_get($source, 'contract_price_per_sqm_number'),
                'area_sqm' => data_get($source, 'contract_area_sqm'),
            ],
            'pricing' => [
                'total_price_number' => data_get($source, 'contract_total_price_number'),
                'total_price_text' => data_get($source, 'contract_total_price_text'),
                'adjustment_number' => data_get($source, 'contract_adjustment_number'),
                'adjustment_text' => data_get($source, 'contract_adjustment_text'),
                'deposit_number' => data_get($source, 'contract_deposit_number'),
                'deposit_text' => data_get($source, 'contract_deposit_text'),
                'deposit_date' => data_get($source, 'contract_deposit_date'),
                'contract_payment_number' => data_get($source, 'contract_payment_number'),
                'contract_payment_text' => data_get($source, 'contract_payment_text'),
                'contract_payment_date' => data_get($source, 'contract_payment_date'),
                'installment_total_number' => data_get($source, 'contract_installment_total_number'),
                'installment_total_text' => data_get($source, 'contract_installment_total_text'),
                'remaining_number' => data_get($source, 'contract_remaining_number'),
                'remaining_text' => data_get($source, 'contract_remaining_text'),
                'installment_count' => data_get($source, 'contract_installment_count'),
                'installments' => $installments,
            ],
            'signatures' => [
                'seller_name' => data_get($source, 'contract_seller_name'),
                'buyer_name' => data_get($source, 'contract_buyer_signature_name'),
                'witness_one_name' => data_get($source, 'contract_witness_one_name'),
                'witness_two_name' => data_get($source, 'contract_witness_two_name'),
            ],
        ];
    }
}
