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

        $excludeTagsRaw = $request->query('exclude_tags');
        $excludeTags = [];
        if (!empty($excludeTagsRaw)) {
            if (is_array($excludeTagsRaw)) $excludeTags = array_map('intval', $excludeTagsRaw);
            else $excludeTags = array_filter(array_map('intval', explode(',', (string)$excludeTagsRaw))); 
        }

        if (!empty($minStay) && is_numeric($minStay)) {
            $query->where('minimum_stay', '>=', (int)$minStay);
        }

        if (!empty($guests) && is_numeric($guests)) {
            $query->where('max_occupancy', '>=', (int)$guests);
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

        // Note: variable is named "excludeTags" but behaves like an include-tags filter.
        // Require that a listing contains EVERY tag in the list (i.e. include-all semantics).
        if (!empty($excludeTags)) {
            $countTags = count($excludeTags);
            $query->whereExists(function($q) use ($excludeTags, $countTags): void {
                $q->select(DB::raw(1))
                  ->from('listing_tags as lt')
                  ->whereRaw('lt.listing_id = listings.id')
                  ->whereIn('lt.tag_id', $excludeTags)
                  ->groupBy('lt.listing_id')
                  ->havingRaw('COUNT(DISTINCT lt.tag_id) = ?', [$countTags]);
            });
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

        $tagsGrouped = [];
        if (!empty($listingIds)) {
            $tagRows = DB::table('listing_tags')
                ->join('tags', 'listing_tags.tag_id', '=', 'tags.id')
                ->whereIn('listing_id', $listingIds)
                ->select('listing_tags.listing_id', 'tags.id', 'tags.name')
                ->get()
                ->groupBy('listing_id');
            $tagsGrouped = $tagRows;
        }

        $prices = [];
        if (!empty($listingIds)) {
            $availQuery = DB::table('availability')->whereIn('listing_id', $listingIds);

            if ($hasPrice) {
                $minP = $priceRanges[$price][0];
                $maxP = $priceRanges[$price][1];
                if ($maxP !== null) {
                    $availQuery->whereBetween('price_per_night', [$minP, $maxP]);
                } else {
                    $availQuery->where('price_per_night', '>=', $minP);
                }
            }

            $availRows = $availQuery->select('id','listing_id','start_date','end_date','price_per_night')
                ->orderBy('start_date','asc')
                ->get()
                ->groupBy('listing_id');

            $today = date('Y-m-d');

            foreach ($availRows as $lid => $rows) {
                $selected = null;
                $rowsArr = $rows->values()->all();

                if ($hasCheckin && $hasCheckout) {
                    foreach ($rowsArr as $r) {
                        if ($r->start_date <= $checkin && $r->end_date >= $checkout) { $selected = $r; break; }
                    }
                } elseif ($hasCheckin) {
                    foreach ($rowsArr as $r) {
                        if ($r->start_date >= $checkin) { $selected = $r; break; }
                    }
                } elseif ($hasCheckout) {
                    for ($i = count($rowsArr)-1; $i >= 0; $i--) {
                        $r = $rowsArr[$i];
                        if ($r->start_date <= $checkout) { $selected = $r; break; }
                    }
                } else {
                    foreach ($rowsArr as $r) {
                        if ($r->start_date >= $today) { $selected = $r; break; }
                    }
                }

                if (!$selected) {
                    $min = null;
                    foreach ($rowsArr as $r) { if ($min === null || $r->price_per_night < $min) $min = $r->price_per_night; }
                    $prices[$lid] = $min;
                } else {
                    $prices[$lid] = $selected->price_per_night;
                }
            }
        }

        $listings = $listings->map(function($l) use ($photosGrouped, $prices, $tagsGrouped) {
            $l->photos = isset($photosGrouped[$l->id]) ? $photosGrouped[$l->id]->pluck('url')->all() : [];
            $l->min_price = $prices[$l->id] ?? null;
            $l->tags = [];
            if (isset($tagsGrouped[$l->id])) {
                $l->tags = $tagsGrouped[$l->id]->map(function($t){ return ['id' => $t->id, 'name' => $t->name]; })->values()->all();
            }
            return $l;
        });

        if ($orderBy === 'price') {
            $listings = $listings->sortBy(function($l) {
                return $l->min_price === null ? PHP_INT_MAX : $l->min_price;
            }, SORT_REGULAR, $orderDir === 'desc')->values();
        }
        $perPage = 10;
        $page = max(1, (int)$request->query('page', 1));
        $totalListings = $listings->count();

        if ($totalListings > $perPage) {
            $offset = ($page - 1) * $perPage;
            $itemsForPage = $listings->slice($offset, $perPage)->values();
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $itemsForPage,
                $totalListings,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $listingsForView = $paginator;
        } else {
            $listingsForView = $listings;
        }

        if ($request->ajax()) {
            $html = view('partials.cards', ['listings' => $listingsForView])->render();
            return response($html, 200)->header('Content-Type', 'text/html');
        }

        $allTags = DB::table('tags')->orderBy('name')->get();

        return view('pages.search', [
            'listings' => $listingsForView,
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
            'allTags' => $allTags,
            'exclude_tags' => $excludeTags,
        ]);
    }
}
