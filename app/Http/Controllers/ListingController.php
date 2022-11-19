<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    //Common controller
    //index - show all data
    //show - show single data
    //create - show form to create data
    //store - save new data
    //edit - show form to edit data
    //update - update data
    //destroy - delete data

    //show all listing
    public function index(){
        return view('listings.index', [
            'listing' => Listing::latest()->filter(request(['tag', 'search']))->paginate(4)
        ]);
    }

    public function show(Listing $listing){
        return view('listings.show',[
            'listing' => $listing
        ]);
    }

    public function create(){
        return view('listings.create');
    }

    public function store(Request $request){
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if($request->hasFile('logo')){
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $formFields['user_id'] = auth()->id(); //get the id of the current logged in user

        Listing::create($formFields);

        return redirect('/listings/manage')->with('message', 'Listing created successfully!');
    }

    public function edit(Listing $listing){
        //Check if the user logged in is the owner
        if($listing->user_id != auth()->id()){
            abort(403, 'Unauthorized action');
        }
        return view('listings.edit', ['listing' => $listing]);
    }

    public function update(Request $request, Listing $listing){
        //Check if the user logged in is the owner
        if($listing->user_id != auth()->id()){
            abort(403, 'Unauthorized action');
        }

        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required'],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if($request->hasFile('logo')){
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $listing->update($formFields);

        return back()->with('message', 'Listing updated successfully!');
    }

    public function destroy(Listing $listing){
        //Check if the user logged in is the owner
        if($listing->user_id != auth()->id()){
            abort(403, 'Unauthorized action');
        }

        $listing->delete();
        return back()->with('message', 'Listing deleted successfully!');
    }

    public function manage(){
        return view('listings.manage', [
            'listing' => auth()->user()->listings()->get()
        ]);
    }
}
