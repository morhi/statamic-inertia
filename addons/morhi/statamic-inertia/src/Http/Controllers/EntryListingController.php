<?php

namespace Morhi\StatamicInertia\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Morhi\StatamicInertia\Support\EntryListing\EntryListingQuery;
use Statamic\Facades\Collection;

class EntryListingController
{
    public function __invoke(Request $request, EntryListingQuery $query)
    {
        // Built manually (rather than $request->validate()) so a failure always
        // returns a JSON 422, instead of Laravel's default redirect-back behavior
        // for requests without an `Accept: application/json` header.
        $validator = Validator::make($request->all(), [
            'collection' => ['required', 'string', Rule::in(
                array_intersect(
                    config('statamic-inertia.entry_listing.allowed_collections', []),
                    Collection::handles()->all()
                )
            )],
            'page'       => ['nullable', 'integer', 'min:1'],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        return response()->json($query->paginate(
            $data['collection'],
            (int) ($data['per_page'] ?? 6),
            (int) ($data['page'] ?? 2),
        ));
    }
}
