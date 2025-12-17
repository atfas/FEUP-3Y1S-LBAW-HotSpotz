<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use App\Models\Listing;

class ListingController extends Controller
{
    private function assertNoOverlaps(array $intervals)
    {
        $normalized = [];
        foreach ($intervals as $it) {
            if (empty($it['start_date']) || empty($it['end_date'])) continue;
            $s = $it['start_date'];
            $e = $it['end_date'];
            $normalized[] = ['s' => $s, 'e' => $e];
        }
        usort($normalized, function($a, $b){ return strcmp($a['s'], $b['s']); });
        $prevEnd = null;
        foreach ($normalized as $n) {
            if ($prevEnd !== null) {
                if ($prevEnd >= $n['s']) {
                    throw new \Exception('Availabilities cannot overlap.');
                }
            }
            $prevEnd = $n['e'];
        }
    }

    public function show($id, Request $request): View
    {
        $listing = Listing::where('id', $id)->where('is_active', true)->first();

        if (!$listing) {
            abort(404);
        }

        $photos = DB::table('listing_photos')
            ->where('listing_id', $id)
            ->orderBy('id')
            ->pluck('url')
            ->all();

        $checkin = $request->query('checkin');
        $checkout = $request->query('checkout');

        $availQuery = DB::table('availability')->where('listing_id', $id)->orderBy('start_date','asc');
        $availRows = $availQuery->get();
        $min_price = null;
        if (!empty($availRows) && count($availRows) > 0) {
            $today = date('Y-m-d');
            $selected = null;
            if (!empty($checkin) && !empty($checkout)) {
                foreach ($availRows as $r) {
                    if ($r->start_date <= $checkin && $r->end_date >= $checkout) { $selected = $r; break; }
                }
            } elseif (!empty($checkin)) {
                foreach ($availRows as $r) { if ($r->start_date >= $checkin) { $selected = $r; break; } }
            } elseif (!empty($checkout)) {
                for ($i = count($availRows)-1; $i >= 0; $i--) { $r = $availRows[$i]; if ($r->start_date <= $checkout) { $selected = $r; break; } }
            } else {
                foreach ($availRows as $r) { if ($r->start_date >= $today) { $selected = $r; break; } }
            }

            if ($selected) {
                $min_price = $selected->price_per_night;
            } else {
                $min = null; foreach ($availRows as $r) { if ($min === null || $r->price_per_night < $min) $min = $r->price_per_night; };
                $min_price = $min;
            }
        }

        $availPaginator = $availQuery->paginate(3, ['*'], 'avail_page');
        $availPaginator->getCollection()->transform(function($r){ return [
            'id' => $r->id ?? null,
            'start_date' => $r->start_date,
            'end_date' => $r->end_date,
            'price_per_night' => (float)$r->price_per_night,
            'check_in_time' => $r->check_in_time ?? null,
            'check_out_time' => $r->check_out_time ?? null,
        ]; });

        $perPage = 5;
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
            'availabilities' => $availRows->map(function($r){ return [
                'id' => $r->id ?? null,
                'start_date' => $r->start_date,
                'end_date' => $r->end_date,
                'price_per_night' => (float)$r->price_per_night,
                'check_in_time' => $r->check_in_time ?? null,
                'check_out_time' => $r->check_out_time ?? null,
            ]; })->all(),
            'availPaginator' => $availPaginator,
            'listing_tags' => DB::table('tags')
                ->join('listing_tags', 'tags.id', '=', 'listing_tags.tag_id')
                ->where('listing_tags.listing_id', $id)
                ->select('tags.id','tags.name')
                ->get()
                ->map(function($t){ return ['id' => $t->id, 'name' => $t->name]; })->all(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Listing::class);

        $allTags = DB::table('tags')->orderBy('name')->get();
        return view('pages.addListing', ['allTags' => $allTags]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Listing::class);
        $user = Auth::user();

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'max_occupancy' => 'required|integer|min:1',
            'location_address' => 'required|string|max:512',
            'minimum_stay' => 'required|integer|min:1',
            'cancellation_policy' => 'nullable|string|max:1024',
            'photos' => 'required|array|min:3',
            'photos.*' => 'image|max:5120', 
            'tags' => 'required|array|min:1',
            'tags.*' => 'integer',
            'amenities' => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $availJson = $request->input('availabilities');
        $availArr = [];
        if (empty($availJson)) {
            return redirect()->back()->withErrors(['availabilities' => 'At least one availability is required.'])->withInput();
        }
        $availArr = json_decode($availJson, true);
        if (!is_array($availArr) || count($availArr) < 1) {
            return redirect()->back()->withErrors(['availabilities' => 'At least one availability is required.'])->withInput();
        }

        try {
            $this->assertNoOverlaps($availArr);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['availabilities' => $e->getMessage()])->withInput();
        }

        $userId = Auth::id() ?? 1; 

        $uploaded = [];
        foreach ($request->file('photos') as $file) {
            $path = $file->store('listing_photos', 'public');
            $url = Storage::url($path); 
            $uploaded[] = $url;
        }

        $newId = null;
        DB::beginTransaction();
        try {
            $newId = DB::table('listings')->insertGetId([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'max_occupancy' => (int)$request->input('max_occupancy'),
                'location_address' => $request->input('location_address'),
                'minimum_stay' => (int)$request->input('minimum_stay'),
                'cancellation_policy' => $request->input('cancellation_policy'),
                'amenities' => $request->input('amenities'),
                'host_id' => $userId,
                'is_active' => true,
            ]);

            foreach ($uploaded as $u) {
                DB::table('listing_photos')->insert([
                    'url' => $u,
                    'listing_id' => $newId,
                ]);
            }

            $tags = $request->input('tags', []);
            if (!is_array($tags) || count($tags) < 1) {
                throw new \Exception('A listing must have at least one tag.');
            }
            foreach ($tags as $tid) {
                DB::table('listing_tags')->insert([
                    'tag_id' => (int)$tid,
                    'listing_id' => $newId,
                ]);
            }

            foreach ($availArr as $av) {
                $s = $av['start_date'] ?? null;
                $e = $av['end_date'] ?? null;
                $p = isset($av['price_per_night']) ? (float)$av['price_per_night'] : null;
                $ci = $av['check_in_time'] ?? null;
                $co = $av['check_out_time'] ?? null;
                $today = date('Y-m-d');
                if (!$s || !$e || $s < $today || $e < $today || $e < $s) {
                    throw new \Exception('Invalid availability dates');
                }
                if (!is_numeric($p) || $p < 0) {
                    throw new \Exception('Invalid availability price');
                }
                if (!$ci || !$co) {
                    throw new \Exception('Invalid check-in/out times');
                }

                DB::table('availability')->insert([
                    'start_date' => $s,
                    'end_date' => $e,
                    'price_per_night' => $p,
                    'check_in_time' => $ci,
                    'check_out_time' => $co,
                    'listing_id' => $newId,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($uploaded as $u) {
                $storagePath = str_replace('/storage/', '', parse_url($u, PHP_URL_PATH));
                if ($storagePath && Storage::disk('public')->exists($storagePath)) {
                    Storage::disk('public')->delete($storagePath);
                }
            }
            return redirect()->back()->with('error', 'Failed to create listing: ' . $e->getMessage());
        }

        return redirect()->route('listing.show', ['id' => $newId])->with('success', 'Listing created.');
    }

    public function edit($id)
    {
        $listing = Listing::find($id);
        if (!$listing) abort(404);

        $this->authorize('update', $listing);

        $photos = DB::table('listing_photos')->where('listing_id', $id)->orderBy('id')->get();
        $availabilities = DB::table('availability')->where('listing_id', $id)->orderBy('start_date')->get();
        $allTags = DB::table('tags')->orderBy('name')->get();
        $selectedTags = DB::table('listing_tags')->where('listing_id', $id)->pluck('tag_id')->all();

        return view('pages.editListing', [
            'listing' => $listing,
            'photos' => $photos,
            'availabilities' => $availabilities,
            'allTags' => $allTags,
            'selectedTags' => $selectedTags,
        ]);
    }

    public function update(Request $request, $id)
    {
        $listing = Listing::find($id);
        if (!$listing) abort(404);

        $this->authorize('update', $listing);

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'max_occupancy' => 'required|integer|min:1',
            'location_address' => 'required|string|max:512',
            'minimum_stay' => 'required|integer|min:1',
            'cancellation_policy' => 'nullable|string|max:1024',
            'amenities' => 'nullable|string',
            'photos.*' => 'image|max:5120',
            'remove_photos' => 'array',
            'remove_photos.*' => 'integer',
            'tags' => 'required|array|min:1',
            'tags.*' => 'integer',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $availJson = $request->input('availabilities');
        $availArr = [];
        if (empty($availJson)) {
            return redirect()->back()->withErrors(['availabilities' => 'At least one availability is required.'])->withInput();
        }
        $availArr = json_decode($availJson, true);
        if (!is_array($availArr) || count($availArr) < 1) {
            return redirect()->back()->withErrors(['availabilities' => 'At least one availability is required.'])->withInput();
        }

        try {
            $this->assertNoOverlaps($availArr);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['availabilities' => $e->getMessage()])->withInput();
        }

        $newUploaded = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('listing_photos', 'public');
                $url = Storage::url($path);
                $newUploaded[] = ['url' => $url, 'path' => $path];
            }
        }

        $removeIds = $request->input('remove_photos', []);

        DB::beginTransaction();
        try {
            DB::table('listings')->where('id', $id)->update([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'max_occupancy' => (int)$request->input('max_occupancy'),
                'location_address' => $request->input('location_address'),
                'minimum_stay' => (int)$request->input('minimum_stay'),
                'cancellation_policy' => $request->input('cancellation_policy'),
                'amenities' => $request->input('amenities'),
            ]);

            if (!empty($removeIds)) {
                $toDelete = DB::table('listing_photos')->where('listing_id', $id)->whereIn('id', $removeIds)->get();
                foreach ($toDelete as $p) {
                    $storagePath = str_replace('/storage/', '', parse_url($p->url, PHP_URL_PATH));
                    if ($storagePath && Storage::disk('public')->exists($storagePath)) {
                        Storage::disk('public')->delete($storagePath);
                    }
                }
                DB::table('listing_photos')->where('listing_id', $id)->whereIn('id', $removeIds)->delete();
            }

            foreach ($newUploaded as $nu) {
                DB::table('listing_photos')->insert([
                    'url' => $nu['url'],
                    'listing_id' => $id,
                ]);
            }

            $finalCount = (int)DB::table('listing_photos')->where('listing_id', $id)->count();
            if ($finalCount < 3) {
                throw new \Exception('A listing must have at least 3 photos.');
            }

            $incomingIds = array_filter(array_map(function($a){ return isset($a['id']) ? (int)$a['id'] : null; }, $availArr));
            $existing = DB::table('availability')->where('listing_id', $id)->pluck('id')->all();
            $toDeleteAvail = array_diff($existing, $incomingIds);
            if (!empty($toDeleteAvail)) {
                DB::table('availability')->whereIn('id', $toDeleteAvail)->delete();
            }

            foreach ($availArr as $av) {
                $s = $av['start_date'] ?? null;
                $e = $av['end_date'] ?? null;
                $p = isset($av['price_per_night']) ? (float)$av['price_per_night'] : null;
                $ci = $av['check_in_time'] ?? null;
                $co = $av['check_out_time'] ?? null;
                $today = date('Y-m-d');
                if (!$s || !$e || $s < $today || $e < $today || $e < $s) {
                    throw new \Exception('Invalid availability dates');
                }
                if (!is_numeric($p) || $p < 0) {
                    throw new \Exception('Invalid availability price');
                }
                if (!$ci || !$co) {
                    throw new \Exception('Invalid check-in/out times');
                }

                if (!empty($av['id'])) {
                    DB::table('availability')->where('id', (int)$av['id'])->update([
                        'start_date' => $s,
                        'end_date' => $e,
                        'price_per_night' => $p,
                        'check_in_time' => $ci,
                        'check_out_time' => $co,
                    ]);
                } else {
                    DB::table('availability')->insert([
                        'start_date' => $s,
                        'end_date' => $e,
                        'price_per_night' => $p,
                        'check_in_time' => $ci,
                        'check_out_time' => $co,
                        'listing_id' => $id,
                    ]);
                }
            }

            $availCount = DB::table('availability')->where('listing_id', $id)->count();
            if ($availCount < 1) {
                throw new \Exception('A listing must have at least one availability.');
            }

            $incomingTags = $request->input('tags', []);
            if (!is_array($incomingTags) || count($incomingTags) < 1) {
                throw new \Exception('A listing must have at least one tag.');
            }
            DB::table('listing_tags')->where('listing_id', $id)->delete();
            foreach ($incomingTags as $tid) {
                DB::table('listing_tags')->insert([ 'tag_id' => (int)$tid, 'listing_id' => $id ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($newUploaded as $nu) {
                if (!empty($nu['path']) && Storage::disk('public')->exists($nu['path'])) {
                    Storage::disk('public')->delete($nu['path']);
                }
            }
            return redirect()->back()->with('error', 'Failed to update listing: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('listing.show', ['id' => $id])->with('success', 'Listing updated.');
    }

    public function destroy(Request $request, $id)
    {
        $listing = Listing::find($id);
        if (!$listing) abort(404);

        $response = Gate::inspect('delete', $listing);
        if (!$response->allowed()) {
            return redirect()->route('listing.show', ['id' => $id])->with('error', $response->message());
        }

        $listing->is_active = false;
        $listing->save();

        return redirect()->route('profile.show')->with('success', 'Listing deleted.');
    }
}
