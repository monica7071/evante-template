<?php

namespace App\Http\Controllers;

use App\Models\ProfileField;
use Illuminate\Http\Request;

class ProfileFieldController extends Controller
{
    public function index()
    {
        $fields = ProfileField::orderBy('field_group')->orderBy('sort_order')->get();
        $groups = $fields->groupBy('field_group');

        return view('employee.profile-fields', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'field_name' => 'required|string|max:255|unique:profile_fields,field_name',
            'field_label' => 'required|string|max:255',
            'field_label_th' => 'nullable|string|max:255',
            'field_type' => 'required|in:text,number,date,select,file,textarea',
            'field_group' => 'required|in:personal,contact,document,bank,other',
            'is_required' => 'boolean',
            'options' => 'nullable|string',
        ]);

        $validated['is_active'] = true;
        $validated['sort_order'] = ProfileField::where('field_group', $validated['field_group'])->max('sort_order') + 1;

        if (!empty($validated['options'])) {
            $validated['options'] = array_map('trim', explode(',', $validated['options']));
        }

        ProfileField::create($validated);

        return back()->with('success', 'Field added successfully.');
    }

    public function update(Request $request, ProfileField $profileField)
    {
        $validated = $request->validate([
            'field_label' => 'sometimes|string|max:255',
            'field_label_th' => 'nullable|string|max:255',
            'is_required' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $profileField->update($validated);

        return back()->with('success', 'Field updated successfully.');
    }

    public function toggle(ProfileField $profileField, string $field)
    {
        if (!in_array($field, ['is_active', 'is_required'])) {
            return back()->with('error', 'Invalid field.');
        }

        $profileField->update([$field => !$profileField->$field]);

        return back()->with('success', 'Field updated successfully.');
    }
}
