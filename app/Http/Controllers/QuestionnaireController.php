<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Models\Organization;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    public function create(Request $request)
    {
        $agentId = $request->query('ref');

        return view('questionnaires.create', compact('agentId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'gender' => 'required|string|in:male,female,other',
            'gender_other' => 'nullable|required_if:gender,other|string|max:255',
            'address_house_no' => 'nullable|string|max:255',
            'address_street' => 'nullable|string|max:255',
            'address_subdistrict' => 'nullable|string|max:255',
            'address_district' => 'nullable|string|max:255',
            'address_province' => 'nullable|string|max:255',
            'address_postal_code' => 'nullable|string|max:10',
            'address_country' => 'nullable|string|max:255',
            'age_range' => 'required|string',
            'marital_status' => 'required|string|in:single,married',
            'children_count' => 'nullable|integer|min:0|max:20',
            'household_income' => 'required|string',
            'source' => 'required|string|in:pass_by,edm,sms,friend,website,billboard,online_media,other',
            'source_other' => 'nullable|required_if:source,other|string|max:255',
            'source_billboard_detail' => 'nullable|required_if:source,billboard|string|max:255',
            'source_online_media_detail' => 'nullable|required_if:source,online_media|string|max:255',
            'visit_reasons' => 'required|array|min:1',
            'visit_reasons.*' => 'string|in:price,promotion,design_room,design_entrance,location,other',
            'visit_reasons_other' => 'nullable|string|max:255',
            'promotions' => 'nullable|array',
            'promotions.*' => 'string|in:special_financial_conditions,free_appliance,free_aircon,free_curtain,discount,free_furniture,free_fitted,other',
            'promotions_other' => 'nullable|string|max:255',
            'budget' => 'required|string|in:under_1m,1m_2m,2m_3m,3m_4m,4m_5m,5m_6m,over_6m',
            'purchase_purpose' => 'required|string|in:self_use,investment,other',
            'purchase_purpose_other' => 'nullable|required_if:purchase_purpose,other|string|max:255',
            'finance_plan' => 'required|string|in:cash,bank_loan,installment',
            'agent_id' => 'nullable|integer|exists:users,id',
        ]);

        // Assign to the first organization (public form, no auth)
        $organization = Organization::first();
        $validated['organization_id'] = $organization->id;

        Questionnaire::withoutGlobalScopes()->create($validated);

        return redirect()->route('questionnaire.thank-you');
    }

    public function thankYou()
    {
        return view('questionnaires.thank-you');
    }

    public function index()
    {
        $questionnaires = Questionnaire::latest()->paginate(20);
        return view('questionnaires.index', compact('questionnaires'));
    }
}
