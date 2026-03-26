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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'age' => 'nullable|integer|min:1|max:150',
            'source' => 'required|string|in:facebook,google,website,line,agent,friend,billboard,event,other',
            'source_other' => 'nullable|required_if:source,other|string|max:255',
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
