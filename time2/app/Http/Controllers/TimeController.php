<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Time;
use App\Http\Requests\StoreTimeRequest;

class TimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $times = Time::orderByDesc('start')->paginate(5);

        return view('times.index')->with('times', $times);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('times.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreTimeRequest $request
     * @return Response
     */
    public function store(StoreTimeRequest $request)
    {
        $time = new Time([
            'start' => $request->get('start'),
            'end' => $request->get('end'),
        ]);
        $time->save();

        return redirect('/times')->with('succes', 'Time saved!');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @param Time $time
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Time $time)
    {
        return view('times.edit')->with('time', $time);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreTimeRequest $request
     * @param Time $time
     * @return \Illuminate\Http\Response
     */
    public function update(StoreTimeRequest $request, Time $time)
    {
        $time->start = $request->input('start');
        $time->end = $request->input('end');
        $time->save();

        return redirect('/times')->with('succes', 'Time updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $time = Time::find($id);
        $time->delete();

        return redirect('/times')->with('success', 'Time deleted!');
    }
}
