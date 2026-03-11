<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

abstract class GracimorFormRequest extends FormRequest
{
    /**
     * All Gracimor API requests require an authenticated user.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }
}
