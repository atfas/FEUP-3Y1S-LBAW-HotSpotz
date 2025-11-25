<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


class BookingController extends Controller
{
    /**
     * Display the booking page.
     */
    public function show(): View
    {
        return view('pages.booking');
    }
} 
