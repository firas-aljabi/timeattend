<?php

namespace App\Http\Requests\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateResignationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'reason' => 'required',
            'attachments' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
