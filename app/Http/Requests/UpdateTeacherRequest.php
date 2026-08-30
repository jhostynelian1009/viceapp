<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        $teacher = $this->route('teacher');

        return $this->user()->can('update', $teacher);
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$teacher->id,
            'password' => 'nullable|string|min:8|confirmed',
        ];
    }
}
