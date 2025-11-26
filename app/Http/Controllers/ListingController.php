<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ListingController extends Controller
{    public function show($id, Request $request): View
    {
        $listing = DB::table('listings')
            ->where('id', $id)
            ->where('is_active', true)
            ->first();

        if (!$listing) {
            abort(404);
        }

        $photos = DB::table('listing_photos')
            ->where('listing_id', $id)
            ->orderBy('id')
            ->pluck('url')
            ->all();

        $minPriceRow = DB::table('availability')
            ->where('listing_id', $id)
            ->select(DB::raw('MIN(price_per_night) as min_price'))
            ->first();

        $min_price = $minPriceRow ? $minPriceRow->min_price : null;

        $perPage = 10;
        $page = max(1, (int)$request->get('page', 1));
        $offset = ($page - 1) * $perPage;

        $reviewsQuery = DB::table('reviews')
            ->join('bookings', 'reviews.booking_id', '=', 'bookings.id')
            ->join('availability', 'bookings.availability_id', '=', 'availability.id')
            ->where('availability.listing_id', $id)
            ->orderByDesc('reviews.created_at');

        $totalReviews = (int)$reviewsQuery->count();

        $reviews = $reviewsQuery
            ->select('reviews.*')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $totalPages = (int)ceil($totalReviews / $perPage);

        return view('pages.listing', [
            'listing' => $listing,
            'photos' => $photos,
            'min_price' => $min_price,
            'reviews' => $reviews,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }
}
