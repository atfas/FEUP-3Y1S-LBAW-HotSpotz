<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(): View
    {
        $listings = DB::table('listings')
            ->where('is_active', true)
            ->orderByDesc('average_rating')
            ->limit(6)
            ->get();

        $listingIds = $listings->pluck('id')->all();
        $photosGrouped = DB::table('listing_photos')
            ->whereIn('listing_id', $listingIds)
            ->orderBy('id')
            ->get()
            ->groupBy('listing_id');

        $prices = DB::table('availability')
            ->select('listing_id', DB::raw('MIN(price_per_night) as min_price'))
            ->whereIn('listing_id', $listingIds)
            ->groupBy('listing_id')
            ->get()
            ->keyBy('listing_id');

        $listings = $listings->map(function ($listing) use ($photosGrouped, $prices) {
            $listing->photos = isset($photosGrouped[$listing->id]) ? $photosGrouped[$listing->id]->pluck('url')->all() : [];
            $listing->min_price = isset($prices[$listing->id]) ? $prices[$listing->id]->min_price : null;
            return $listing;
        });

        $allTags = DB::table('tags')->orderBy('name')->get();

        return view('pages.home', ['listings' => $listings, 'allTags' => $allTags]);
    }
}