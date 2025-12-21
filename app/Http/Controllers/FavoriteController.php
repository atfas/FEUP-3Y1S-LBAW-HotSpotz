<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Show user's favorites
     */
    public function index()
    {
        $favorites = Favorite::with([
            'listing.photos',
            'listing.availabilities',
            'listing.host'
        ])
        ->where('user_id', Auth::id())
        ->latest()
        ->paginate(9);


        return view('pages.favorites', compact('favorites'));
    }

    public function toggle(Request $request, Listing $listing)
    {
        $user = $request->user();

        if ($user->id === $listing->host_id) {
            return back()->with('error', 'You cannot favorite your own listing.');
        }

        $favorite = Favorite::where('user_id', $user->id)
            ->where('listing_id', $listing->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return back()->with('success', 'Removed from favorites.');
        }

        Favorite::create([
            'user_id' => $user->id,
            'listing_id' => $listing->id,
        ]);

        return back()->with('success', 'Added to favorites.');
    }

}
