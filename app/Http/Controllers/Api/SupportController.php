<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\RagDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupportController extends Controller
{
    // GET /api/v1/knowledge/search?q=...&category=faq
    public function knowledgeSearch(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2']);

        $query = RagDocument::active();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $keyword = $request->q;
        $results = $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
              ->orWhere('content', 'like', "%{$keyword}%");
        })
        ->limit(10)
        ->get()
        ->map(fn ($doc) => [
            'id'       => $doc->id,
            'title'    => $doc->title,
            'category' => $doc->category,
            'content'  => $doc->content,
        ]);

        return response()->json([
            'success' => true,
            'total'   => $results->count(),
            'data'    => $results,
        ]);
    }

    // GET /api/v1/customers/{id}
    public function customer(int $id): JsonResponse
    {
        $session = ChatSession::find($id);

        if (! $session) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $session->id,
                'customer_name' => $session->customer_name,
                'channel'       => $session->channel,
                'status'        => $session->status,
                'handled_by'    => $session->handled_by,
                'last_message'  => $session->last_message_at?->toIso8601String(),
                'created_at'    => $session->created_at?->toIso8601String(),
            ],
        ]);
    }

    // POST /api/v1/chat/handoff
    public function chatHandoff(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|integer',
            'admin_id'   => 'nullable|integer',
        ]);

        $session = ChatSession::find($validated['session_id']);

        if (! $session) {
            return response()->json(['success' => false, 'message' => 'Session not found.'], 404);
        }

        $session->update([
            'handled_by' => 'admin',
            'admin_id'   => $validated['admin_id'] ?? null,
            'status'     => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Chat handed off to admin.',
            'data'    => [
                'session_id' => $session->id,
                'handled_by' => 'admin',
                'admin_id'   => $session->admin_id,
            ],
        ]);
    }

    // POST /api/v1/documents/upload
    public function uploadDocument(Request $request): JsonResponse
    {
        $request->validate([
            'file'        => 'required|file|max:10240',
            'category'    => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        return response()->json([
            'success' => true,
            'data'    => [
                'filename'    => $file->getClientOriginalName(),
                'path'        => $path,
                'url'         => asset('storage/' . $path),
                'size'        => $file->getSize(),
                'category'    => $request->input('category'),
                'description' => $request->input('description'),
            ],
        ], 201);
    }

    // GET /api/v1/loan-calculator?price=5000000&down_percent=10&rate=6.5&years=30
    public function loanCalculator(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'price'        => 'required|numeric|min:1',
            'down_percent' => 'nullable|numeric|min:0|max:100',
            'rate'         => 'nullable|numeric|min:0',
            'years'        => 'nullable|integer|min:1|max:40',
            'monthly_income' => 'nullable|numeric|min:0',
        ]);

        $price = (float) $validated['price'];
        $downPercent = (float) ($validated['down_percent'] ?? 10);
        $rate = (float) ($validated['rate'] ?? 6.5);
        $years = (int) ($validated['years'] ?? 30);

        $downPayment = $price * ($downPercent / 100);
        $loanAmount = $price - $downPayment;
        $monthlyRate = $rate / 100 / 12;
        $totalMonths = $years * 12;

        if ($monthlyRate > 0) {
            $monthly = $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $totalMonths))
                / (pow(1 + $monthlyRate, $totalMonths) - 1);
        } else {
            $monthly = $loanAmount / $totalMonths;
        }

        $totalPayment = $monthly * $totalMonths;
        $totalInterest = $totalPayment - $loanAmount;

        $result = [
            'property_price'    => $price,
            'down_payment'      => round($downPayment, 2),
            'down_percent'      => $downPercent,
            'loan_amount'       => round($loanAmount, 2),
            'interest_rate'     => $rate,
            'loan_term_years'   => $years,
            'monthly_payment'   => round($monthly, 2),
            'total_payment'     => round($totalPayment, 2),
            'total_interest'    => round($totalInterest, 2),
        ];

        // Optional: max loan from income
        if (! empty($validated['monthly_income'])) {
            $income = (float) $validated['monthly_income'];
            $debtRatio = 0.4;
            $maxMonthly = $income * $debtRatio;

            if ($monthlyRate > 0) {
                $maxLoan = $maxMonthly * (pow(1 + $monthlyRate, $totalMonths) - 1)
                    / ($monthlyRate * pow(1 + $monthlyRate, $totalMonths));
            } else {
                $maxLoan = $maxMonthly * $totalMonths;
            }

            $result['max_loan_from_income'] = round($maxLoan, 2);
            $result['max_monthly_payment'] = round($maxMonthly, 2);
        }

        return response()->json(['success' => true, 'data' => $result]);
    }

    // POST /api/v1/verify-slip
    public function verifySlip(Request $request): JsonResponse
    {
        $request->validate([
            'sale_number' => 'required|string',
            'slip_image'  => 'required|image|max:5120',
            'amount'      => 'nullable|numeric|min:0',
        ]);

        $path = $request->file('slip_image')->store('payment-slips', 'public');

        // Placeholder — integrate slip verification API (e.g., SlipOK, OpenSlipVerify)
        return response()->json([
            'success' => true,
            'data'    => [
                'sale_number' => $request->sale_number,
                'slip_url'    => asset('storage/' . $path),
                'amount'      => $request->amount,
                'status'      => 'pending_verification',
                'note'        => 'Slip uploaded. Integrate verification API for auto-check.',
            ],
        ], 201);
    }
}
