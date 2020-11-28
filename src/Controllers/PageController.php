<?php

namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use budisteikul\toursdk\DataTables\PageDataTable;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(PageDataTable $dataTable)
    {
        return $dataTable->render('toursdk::page.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('toursdk::page.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:pages,title',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        $title =  $request->input('title');
        $content =  $request->input('content');

        $page = new Page();
        $page->title = $title;
        $page->slug = Str::slug($title,"-");
        $page->content = $content;
        $page->save();

        return response()->json([
                    "id" => "1",
                    "message" => 'Success'
                ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function show(Page $page)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function edit(Page $page)
    {
        return view('toursdk::page.edit',['page'=>$page]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Page $page)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:pages,title,'. $page->id,
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        $title =  $request->input('title');
        $content =  $request->input('content');

        $page->title = $title;
        $page->slug = Str::slug($title,"-");
        $page->content = $content;
        $page->save();

        return response()->json([
                    "id" => "1",
                    "message" => 'Success'
                ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function destroy(Page $page)
    {
        $page->delete();
    }
}
