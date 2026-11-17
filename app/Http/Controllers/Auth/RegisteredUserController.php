<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        abort(404);
    }

    /**
     * Public registration is disabled. Accounts are created by administrators.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        abort(404);
    }
}
