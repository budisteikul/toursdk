<?php

namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use budisteikul\toursdk\Helpers\CategoryHelper;
use budisteikul\toursdk\DataTables\CategoryDataTable;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function structure()
    {
        $root_categories = Category::where('parent_id',0)->get();
        return view('toursdk::category.show',['root_categories'=>$root_categories]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(CategoryDataTable $dataTable)
    {
        return $dataTable->render('toursdk::category.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('toursdk::category.create',['categories'=>$categories]);
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
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        $name =  $request->input('name');
        $parent_id =  $request->input('parent_id');

        $category = new Category();
        $category->name = $name;
        $category->parent_id = $parent_id;
        $category->slug = Str::slug($name,"-");
        $category->save();

        return response()->json([
                    "id" => "1",
                    "message" => 'Success'
                ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        $allChild = CategoryHelper::getChild($category->id);
        $categories = Category::where('id','<>',$category->id)->whereNotIn('id',$allChild)->orderBy('name')->get();
        return view('toursdk::category.edit',['category'=>$category,'categories'=>$categories]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        $name =  $request->input('name');
        $parent_id =  $request->input('parent_id');
        
        $category->name = $name;
        $category->parent_id = $parent_id;
        $category->slug = Str::slug($name,"-");
        $category->save();

        return response()->json([
                    "id" => "1",
                    "message" => 'Success'
                ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        Category::where('parent_id',$category->id)->update(['parent_id'=>0]);
        $category->delete();
    }
}
