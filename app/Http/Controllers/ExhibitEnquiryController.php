<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExhibitEnquiryRequest;
use App\Models\ExhibitEnquiry;
use Illuminate\Http\RedirectResponse;

class ExhibitEnquiryController extends Controller
{
    public function store(StoreExhibitEnquiryRequest $request): RedirectResponse
    {
        ExhibitEnquiry::create($request->validated());

        return back()->with('success', 'Thank you. The exhibition team will contact you about booth availability.');
    }
}
