<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApartmentRequest;
use App\Models\Apartment;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{

    public function index()
    {
       $apartments= Apartment::where('is_approved',true)->get();
        return response()->json($apartments,201);
    }

    public function store(StoreApartmentRequest $request)
    {
        $validatedData=$request->validated();
       $validatedData['owner_id']=$request->user()->id;

         $apartments= Apartment::create($validatedData);
        return response()->json([
            'message'=>'Apartment created successfuly. waiting for admin to approve.',
            'apartment'=> $apartments],201);
    }

    public function show(int $id)
    {
        //
    }


    public function update(Request $request, int $id)
    {
        //
    }

    public function destroy(int $id)
    {
        //
    }
}
