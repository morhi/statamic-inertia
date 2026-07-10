<?php

namespace Morhi\StatamicInertia\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Morhi\StatamicInertia\Support\EntryListing\EntryListingQuery;
use Statamic\Facades\Collection;

class EntryListingController
{
    public function __invoke(Request $request, EntryListingQuery $query)
    {
        $data = $request->validate([
            'collection' => ['required', 'string', Rule::in(Collection::handles())],
            'page'       => ['nullable', 'integer', 'min:1'],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        return response()->json($query->paginate(
            $data['collection'],
            (int) ($data['per_page'] ?? 6),
            (int) ($data['page'] ?? 2),
        ));
    }
}
