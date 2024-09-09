<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IdeaController extends Controller
{
    private $rules = 
        [
            'title' => 'required|string|max:100',
            'description'=>'required|string|max:300',
        ];
    private array $errorMessages = 
        [
            'title.required' => 'el campo titulo es obligatorio',
            'description.required' => 'el campo descripcion es obligatorio',
            'string'=>'Este campo debe ser de tipo string',
            'title.max'=>'El campo titulo no de ser mayor a 100 caracteres',
            'description.max'=>'El campo descripcion no de ser mayor a 300 caracteres',
        ];
    
    
    public function index(Request $request):View
     {
        $ideas =Idea::myIdeas($request->filtro)->teBest($request->filtro)->get();

        return view('ideas.index', ['ideas'=>$ideas]);
    }
    public function create():View
    {
        return view('ideas.create_or_edit');
    }
    public function store(Request $request):RedirectResponse
    {
        $validated = $request->validate($this->rules, $this->errorMessages);

        Idea::create([
            'user_id' => auth()->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);

        session()->flash('message', 'Idea creada correctamente');
        return redirect()->route('idea.index');
    }
    public function edit(Idea $idea): View
    {
        return view('ideas.create_or_edit')->with('idea', $idea);
    }
    public function update(Request $request, Idea $idea){

        $this->authorize('update',$idea);

        $validated = $request->validate($this->rules, $this->errorMessages);

        $idea->update($validated);
        session()->flash('message', 'Idea Actualizada correctamente');
        return redirect(route('idea.index'));
    }
    public function show(Idea $idea):view
    {
        return view('ideas.show')->with('idea', $idea);
    }
    public function delete(Idea $idea) :RedirectResponse
     {
        $this->authorize('delete',$idea);
        $idea->delete();
        session()->flash('message', 'Idea Eleminada correctamente');
        return redirect()->route('idea.index');
    }


    public function synchronizeLikes(Request $request, Idea $idea): RedirectResponse
    {
        $this->authorize('updateLikes', $idea);
        
        $request->user()->ideasLiked()->toggle([$idea->id]);

        $idea->update(['likes' => $idea->users()->count()]);

        return redirect()->route('idea.show', $idea);
    }
}
