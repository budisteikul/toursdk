<?php

namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Models\Review;
use budisteikul\toursdk\Models\Channel;
use budisteikul\toursdk\Models\Product;
use budisteikul\toursdk\DataTables\ReviewDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ReviewDataTable $dataTable)
    {
        return $dataTable->render('toursdk::review.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();
        $channels = Channel::orderBy('name')->get();
        return view('toursdk::review.create',['products'=>$products,'channels'=>$channels]);
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
            'user' => ['required', 'string', 'max:255'],
            'text' => ['required'],
            'channel_id' => ['required'],
            'product_id' => ['required'],
        ]);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        $product_id = $request->input('product_id');
        $user = $request->input('user');
        $title = $request->input('title');
        $text = $request->input('text');
        $date = $request->input('date');
        $rating = $request->input('rating');
        $channel_id = $request->input('channel_id');
        $link = $request->input('link');
        
        $review = new Review();
        $review->product_id = $product_id;
        $review->user = $user;
        $review->title = $title;
        $review->text = $text;
        $review->date = $date;
        $review->rating = $rating;
        $review->link = $link;
        $review->channel_id = $channel_id;
        $review->save();
        
        return response()->json([
                    "id" => "1",
                    "message" => 'Success'
                ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function show(Review $review)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function edit(Review $review)
    {
        $products = Product::orderBy('name')->get();
        $channels = Channel::orderBy('name')->get();
        return view('toursdk::review.edit',['products'=>$products,'channels'=>$channels,'review'=>$review]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Review $review)
    {
        $validator = Validator::make($request->all(), [
            'user' => ['required', 'string', 'max:255'],
            'text' => ['required'],
        ]);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }
        
        $product_id = $request->input('product_id');
        $user = $request->input('user');
        $title = $request->input('title');
        $text = $request->input('text');
        $date = $request->input('date');
        $rating = $request->input('rating');
        $channel_id = $request->input('channel_id');
        $link = $request->input('link');
        
        $review->product_id = $product_id;
        $review->user = $user;
        $review->title = $title;
        $review->text = $text;
        $review->date = $date;
        $review->rating = $rating;
        $review->link = $link;
        $review->channel_id = $channel_id;
        $review->save();
        
        return response()->json([
                    "id" => "1",
                    "message" => 'Success'
                ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function destroy(Review $review)
    {
        $review->delete();
    }
}
