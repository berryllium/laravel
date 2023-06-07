<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RealtorListingController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Listing::class, 'listing');
    }

    public function index(Request $request) {
        $filters = [
            'deleted' => $request->boolean('deleted'),
            ...$request->only(['by', 'order'])
        ];
        return inertia(
                'Realtor/Index',
                [
                    'filters' => $filters,
                    'listings' => Auth::user()
                        ->listings()
                        ->filter($filters)
                        ->withCount('images')
                        ->withCount('offers')
                        ->paginate(5)
                        ->withQueryString()
                ]
            );
    }

    public function destroy(Listing $listing)
    {
        $listing->deleteOrFail();
        return redirect()->back()
            ->with('success', 'Listing was deleted');
    }

    public function edit(Listing $listing)
    {
        return inertia(
            'Realtor/Edit',
            [
                'listing' => $listing
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Listing  $listing
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Listing $listing)
    {
        $listing->update($request->validate([
            'beds' => 'required|integer|max:20',
            'baths' => 'required|integer|max:20',
            'area' => 'required|integer|min:20',
            'city' => 'required',
            'code' => 'required',
            'street' => 'required',
            'street_nr' => 'required|integer|min:1',
            'price' => 'required|integer',
        ]));
        return redirect()->route('realtor.listing.index')->with('success', 'Listing was changed');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return inertia('Realtor/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->user()->listings()->create($request->validate([
            'beds' => 'required|integer|max:20',
            'baths' => 'required|integer|max:20',
            'area' => 'required|integer|min:20',
            'city' => 'required',
            'code' => 'required',
            'street' => 'required',
            'street_nr' => 'required|integer|min:1',
            'price' => 'required|integer',
        ]));
        return redirect()->route('realtor.listing.index')->with('success', 'Listing was added');
    }

    public function restore(Listing $listing) {
        $listing->restore();
        return redirect()->route('realtor.listing.index')->with('success', 'Listing was restored');
    }

    public function show(Listing $listing) {
        return inertia('Realtor/Show', ['listing' => $listing->load('offers')]);
    }

}
