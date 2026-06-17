<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        abort(404);
    }

    /**
     * Handle an incoming registration request.
     *
     */
    public function store(Request $request): Response
    {
        abort(404);
    }
}
