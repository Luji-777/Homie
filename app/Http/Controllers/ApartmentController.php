<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApartmentRequest;
use App\Http\Requests\UpdateApartmentRequest;
use App\Models\Apartment;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class ApartmentController extends Controller
{

    public function index()
    {
        $apartments = FacadesAuth::user()->apartments;
        $apartments = Apartment::where('is_approved', true)->get();
        return response()->json($apartments, 201);
    }

    public function store(StoreApartmentRequest $request)
    {
        $validatedData = $request->validated();
        $oner_id = FacadesAuth::user()->id;
        $validatedData['owner_id'] = $oner_id;

        $apartments = Apartment::create($validatedData);
        return response()->json([
            'message' => 'Apartment created successfuly. waiting for admin to approve.',
            'apartment' => $apartments
        ], 201);
    }

    public function show(int $id)
    {
        $apartments = Apartment::findorfail($id);
        return response()->json([
            'apartment' => $apartments
        ], 201);
    }


    public function update(UpdateApartmentRequest $request, int $id)
    {
        $validatedData = $request->validated();
        $validatedData['owner_id'] = $request->user()->id;

        $apartments = Apartment::findorfail($id);
        $apartments->update($request->all());
        return response()->json([
            'apartment' => $apartments
        ], 200);
    }

    public function destroy(int $id)
    {
        $apartments = Apartment::findorfail($id);
        $apartments->delete;
        return response()->json([

            'message' => 'the apartment deleted successfuly.'
        ], 200);
    }
}
