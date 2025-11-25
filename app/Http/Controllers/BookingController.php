<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


class BookingController extends Controller
{
    public function show(Listing $listing): View
    {
        return view('pages.booking', compact('listing'));
    }
} 
