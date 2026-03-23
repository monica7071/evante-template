<?php

namespace Database\Seeders;

use App\Models\ProfileField;
use Illuminate\Database\Seeder;

class ProfileFieldSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            // Personal
            ['field_name' => 'employee_code', 'field_label' => 'Employee Code', 'field_label_th' => 'รหัสพนักงาน', 'field_type' => 'text', 'is_required' => true, 'field_group' => 'personal', 'sort_order' => 1],
            ['field_name' => 'prefix', 'field_label' => 'Prefix', 'field_label_th' => 'คำนำหน้า', 'field_type' => 'select', 'is_required' => false, 'field_group' => 'personal', 'sort_order' => 2, 'options' => ['Mr.', 'Ms.', 'Mrs.', 'นาย', 'นาง', 'นางสาว']],
            ['field_name' => 'first_name', 'field_label' => 'First Name', 'field_label_th' => 'ชื่อจริง', 'field_type' => 'text', 'is_required' => true, 'field_group' => 'personal', 'sort_order' => 3],
            ['field_name' => 'last_name', 'field_label' => 'Last Name', 'field_label_th' => 'นามสกุล', 'field_type' => 'text', 'is_required' => true, 'field_group' => 'personal', 'sort_order' => 4],
            ['field_name' => 'first_name_th', 'field_label' => 'First Name (TH)', 'field_label_th' => 'ชื่อจริง (ภาษาไทย)', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'personal', 'sort_order' => 5],
            ['field_name' => 'last_name_th', 'field_label' => 'Last Name (TH)', 'field_label_th' => 'นามสกุล (ภาษาไทย)', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'personal', 'sort_order' => 6],
            ['field_name' => 'nickname', 'field_label' => 'Nickname', 'field_label_th' => 'ชื่อเล่น', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'personal', 'sort_order' => 7],
            ['field_name' => 'gender', 'field_label' => 'Gender', 'field_label_th' => 'เพศ', 'field_type' => 'select', 'is_required' => false, 'field_group' => 'personal', 'sort_order' => 8, 'options' => ['male', 'female', 'other']],
            ['field_name' => 'date_of_birth', 'field_label' => 'Date of Birth', 'field_label_th' => 'วันเกิด', 'field_type' => 'date', 'is_required' => false, 'field_group' => 'personal', 'sort_order' => 9],
            ['field_name' => 'national_id', 'field_label' => 'National ID', 'field_label_th' => 'เลขบัตรประชาชน', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'personal', 'sort_order' => 10],
            ['field_name' => 'nationality', 'field_label' => 'Nationality', 'field_label_th' => 'สัญชาติ', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'personal', 'sort_order' => 11],
            ['field_name' => 'religion', 'field_label' => 'Religion', 'field_label_th' => 'ศาสนา', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'personal', 'sort_order' => 12],
            ['field_name' => 'blood_type', 'field_label' => 'Blood Type', 'field_label_th' => 'กรุ๊ปเลือด', 'field_type' => 'select', 'is_required' => false, 'field_group' => 'personal', 'sort_order' => 13, 'options' => ['A', 'B', 'AB', 'O']],
            ['field_name' => 'marital_status', 'field_label' => 'Marital Status', 'field_label_th' => 'สถานะสมรส', 'field_type' => 'select', 'is_required' => false, 'field_group' => 'personal', 'sort_order' => 14, 'options' => ['Single', 'Married', 'Divorced', 'Widowed']],

            // Contact
            ['field_name' => 'address', 'field_label' => 'Address', 'field_label_th' => 'ที่อยู่', 'field_type' => 'textarea', 'is_required' => false, 'field_group' => 'contact', 'sort_order' => 1],
            ['field_name' => 'phone', 'field_label' => 'Phone', 'field_label_th' => 'เบอร์โทร', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'contact', 'sort_order' => 2],
            ['field_name' => 'email', 'field_label' => 'Email', 'field_label_th' => 'อีเมล', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'contact', 'sort_order' => 3],
            ['field_name' => 'emergency_contact_name', 'field_label' => 'Emergency Contact Name', 'field_label_th' => 'ชื่อผู้ติดต่อฉุกเฉิน', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'contact', 'sort_order' => 4],
            ['field_name' => 'emergency_contact_phone', 'field_label' => 'Emergency Contact Phone', 'field_label_th' => 'เบอร์ผู้ติดต่อฉุกเฉิน', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'contact', 'sort_order' => 5],
            ['field_name' => 'emergency_contact_relation', 'field_label' => 'Emergency Contact Relation', 'field_label_th' => 'ความสัมพันธ์', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'contact', 'sort_order' => 6],

            // Document
            ['field_name' => 'id_card_copy', 'field_label' => 'ID Card Copy', 'field_label_th' => 'สำเนาบัตรประชาชน', 'field_type' => 'file', 'is_required' => false, 'field_group' => 'document', 'sort_order' => 1],
            ['field_name' => 'house_registration', 'field_label' => 'House Registration', 'field_label_th' => 'สำเนาทะเบียนบ้าน', 'field_type' => 'file', 'is_required' => false, 'field_group' => 'document', 'sort_order' => 2],
            ['field_name' => 'passport_copy', 'field_label' => 'Passport Copy', 'field_label_th' => 'สำเนาพาสปอร์ต', 'field_type' => 'file', 'is_required' => false, 'field_group' => 'document', 'sort_order' => 3],
            ['field_name' => 'work_permit', 'field_label' => 'Work Permit', 'field_label_th' => 'ใบอนุญาตทำงาน', 'field_type' => 'file', 'is_required' => false, 'field_group' => 'document', 'sort_order' => 4],

            // Bank
            ['field_name' => 'bank_name', 'field_label' => 'Bank Name', 'field_label_th' => 'ธนาคาร', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'bank', 'sort_order' => 1],
            ['field_name' => 'bank_branch', 'field_label' => 'Bank Branch', 'field_label_th' => 'สาขา', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'bank', 'sort_order' => 2],
            ['field_name' => 'account_number', 'field_label' => 'Account Number', 'field_label_th' => 'เลขบัญชี', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'bank', 'sort_order' => 3],
            ['field_name' => 'account_name', 'field_label' => 'Account Name', 'field_label_th' => 'ชื่อบัญชี', 'field_type' => 'text', 'is_required' => false, 'field_group' => 'bank', 'sort_order' => 4],
        ];

        foreach ($fields as $field) {
            ProfileField::updateOrCreate(
                ['field_name' => $field['field_name']],
                array_merge($field, ['is_active' => true])
            );
        }
    }
}
