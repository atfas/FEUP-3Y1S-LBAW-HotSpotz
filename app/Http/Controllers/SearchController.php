<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $where = trim((string)$request->query('where', ''));
        $checkin = $request->query('checkin');
        $checkout = $request->query('checkout');
        $price = $request->query('price');
        $guests = $request->query('guests');
        $orderBy = $request->query('order_by', 'rating');
        $orderDir = strtolower($request->query('order_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $minRating = $request->query('min_rating');
        $maxRating = $request->query('max_rating');
        $minStay = $request->query('min_stay');

        $priceRanges = [
            '0' => [0, 50],
            '50' => [50, 100],
            '100' => [100, 200],
            '200' => [200, 500],
            '500' => [500, null],
        ];

        $query = DB::table('listings')->where('is_active', true);

        if (!empty($minStay) && is_numeric($minStay)) {
            $query->where('minimum_stay', '>=', (int)$minStay);
        }

        if ($minRating !== null && $minRating !== '') {
            if (is_numeric($minRating)) {
                $query->where('average_rating', '>=', (float)$minRating);
            }
        }
        if ($maxRating !== null && $maxRating !== '') {
            if (is_numeric($maxRating)) {
                $query->where('average_rating', '<=', (float)$maxRating);
            }
        }

        if ($where !== '') {
            $query->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$where]);
            if ($orderBy === 'relevance') {
                $query->orderByRaw("ts_rank(search_vector, plainto_tsquery('english', ?)) {$orderDir}", [$where]);
            }
        }

        $hasCheckin = !empty($checkin);
        $hasCheckout = !empty($checkout);
        $hasPrice = isset($price) && array_key_exists($price, $priceRanges);

        if ($hasCheckin && $hasCheckout) {
            $minP = $hasPrice ? $priceRanges[$price][0] : null;
            $maxP = $hasPrice ? $priceRanges[$price][1] : null;
            $query->whereExists(function($q) use ($checkin, $checkout, $minP, $maxP) {
                $q->select(DB::raw(1))->from('availability')
                  ->whereRaw('availability.listing_id = listings.id')
                  ->where('start_date', '<=', $checkin)
                  ->where('end_date', '>=', $checkout);
                if ($minP !== null && $maxP !== null) {
                    $q->whereBetween('price_per_night', [$minP, $maxP]);
                } elseif ($minP !== null && $maxP === null) {
                    $q->where('price_per_night', '>=', $minP);
                }
            });
            try {
                $d1 = new \DateTime($checkin);
                $d2 = new \DateTime($checkout);
                $interval = $d2->diff($d1);
                $days = (int)$interval->format('%a');
                if ($days > 0) {
                    $query->where('minimum_stay', '<=', $days);
                }
            } catch (\Exception $e) {
            }
        } elseif ($hasCheckin) {
            $minP = $hasPrice ? $priceRanges[$price][0] : null;
            $maxP = $hasPrice ? $priceRanges[$price][1] : null;
            $query->whereExists(function($q) use ($checkin, $minP, $maxP) {
                $q->select(DB::raw(1))->from('availability')
                  ->whereRaw('availability.listing_id = listings.id')
                  ->where('end_date', '>=', $checkin);
                if ($minP !== null && $maxP !== null) {
                    $q->whereBetween('price_per_night', [$minP, $maxP]);
                } elseif ($minP !== null && $maxP === null) {
                    $q->where('price_per_night', '>=', $minP);
                }
            });
        } elseif ($hasCheckout) {
            $minP = $hasPrice ? $priceRanges[$price][0] : null;
            $maxP = $hasPrice ? $priceRanges[$price][1] : null;
            $query->whereExists(function($q) use ($checkout, $minP, $maxP) {
                $q->select(DB::raw(1))->from('availability')
                  ->whereRaw('availability.listing_id = listings.id')
                  ->where('start_date', '<=', $checkout);
                if ($minP !== null && $maxP !== null) {
                    $q->whereBetween('price_per_night', [$minP, $maxP]);
                } elseif ($minP !== null && $maxP === null) {
                    $q->where('price_per_night', '>=', $minP);
                }
            });
        } else {
            if ($hasPrice) {
                $minP = $priceRanges[$price][0];
                $maxP = $priceRanges[$price][1];
                $query->whereExists(function($q) use ($minP, $maxP) {
                    $q->select(DB::raw(1))->from('availability')
                      ->whereRaw('availability.listing_id = listings.id');
                    if ($maxP !== null) {
                        $q->whereBetween('price_per_night', [$minP, $maxP]);
                    } else {
                        $q->where('price_per_night', '>=', $minP);
                    }
                });
            }
        }

        if ($orderBy === 'rating') {
            $query->orderBy('average_rating', $orderDir);
        }

        $listings = $query->limit(50)->get();

        $listingIds = $listings->pluck('id')->all();

        $photosGrouped = [];
        if (!empty($listingIds)) {
            $photos = DB::table('listing_photos')
                ->whereIn('listing_id', $listingIds)
                ->orderBy('id')
                ->get()
                ->groupBy('listing_id');
            $photosGrouped = $photos;
        }

        $prices = [];
        if (!empty($listingIds)) {
            $availQuery = DB::table('availability')->whereIn('listing_id', $listingIds);

            if ($hasCheckin && $hasCheckout) {
                $availQuery->where('start_date', '<=', $checkin)->where('end_date', '>=', $checkout);
            } elseif ($hasCheckin) {
                $availQuery->where('end_date', '>=', $checkin);
            } elseif ($hasCheckout) {
                $availQuery->where('start_date', '<=', $checkout);
            }

            if ($hasPrice) {
                $minP = $priceRanges[$price][0];
                $maxP = $priceRanges[$price][1];
                if ($maxP !== null) {
                    $availQuery->whereBetween('price_per_night', [$minP, $maxP]);
                } else {
                    $availQuery->where('price_per_night', '>=', $minP);
                }
            }

            $priceRows = $availQuery->select('listing_id', DB::raw('MIN(price_per_night) as min_price'))
                ->groupBy('listing_id')
                ->get()
                ->keyBy('listing_id');

            foreach ($priceRows as $lid => $row) {
                $prices[$lid] = $row->min_price;
            }
        }

        $listings = $listings->map(function($l) use ($photosGrouped, $prices) {
            $l->photos = isset($photosGrouped[$l->id]) ? $photosGrouped[$l->id]->pluck('url')->all() : [];
            $l->min_price = $prices[$l->id] ?? null;
            return $l;
        });

        if ($orderBy === 'price') {
            $listings = $listings->sortBy(function($l) {
                return $l->min_price === null ? PHP_INT_MAX : $l->min_price;
            }, SORT_REGULAR, $orderDir === 'desc')->values();
        }

        if ($request->ajax()) {
            $html = view('pages._search_results', ['listings' => $listings])->render();
            return response($html, 200)->header('Content-Type', 'text/html');
        }

        return view('pages.search', [
            'listings' => $listings,
            'where' => $where,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'price' => $price,
            'guests' => $guests,
            'order_by' => $orderBy,
            'order_dir' => $orderDir,
            'min_rating' => $minRating,
            'max_rating' => $maxRating,
            'min_stay' => $minStay,
        ]);
    }
}
