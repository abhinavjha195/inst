<?php

namespace App\Http\Controllers;

use App\Models\SpeakerAssignedCategories;
use App\Models\SpeakerCategories;
use App\Models\Speakers;
use Illuminate\Http\Request;

class SpeakersController extends Controller
{
    public function __Construct()
    {
        $this->statusArrays = array('' => 'Status', 'inactive' => 'inActive', 'active' => 'Active');
    }


    public function speakers(Request $request)
    {
        $search = $request->query('search');
        $sponsers = Speakers::where('title_en','!=','')
        ->where(function($query) use($search){
            $query->where('title_en','LIKE','%'.request('search').'%');
        })
        ->orderBy('id', 'ASC')
        ->paginate(10);
        $totalrecords = Speakers::count();
        return
            view('myadmin.speakers.listhtml')->with([ 'search'=>$search, 'totalrecords' => 'Speaker List : ' . $totalrecords.' Records founds', 'lists' => $sponsers]);
    }


    public function create()
    {
        $categories = SpeakerCategories::where('isactive', 'active')->get();
        // dd($categories);
        return view('myadmin.speakers.createhtml')->with(['statusArrays' => $this->statusArrays, 'categories' => $categories]);
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'pagename_en' => 'required',
            'isactive' => 'required',
            // 'image_file' => 'required',
            'category' => 'required'
        ]);

        $post = new Speakers();
        $post->title_en = $request->pagename_hi;
        $post->isactive = $request->isactive;
        $post->title_hi = $request->pagename_hi;
        $post->description = $request->description;
        $image_path = "";
        if (!empty($request->file('image_file'))) {
            $feature_image = $request->file('image_file');
            $image = time() . '.' . $feature_image->getClientOriginalExtension();
            $feature_image->move(public_path('/uploads/conference'), $image);
            $image_path = '/uploads/conference/' . $image;
        }
        $post->image_file = $image_path;
        if ($post->save()) {
            $categories = $request->category;
            foreach ($categories as $category) {

                $savecategory =  new SpeakerAssignedCategories();
                $savecategory->speaker_id = $post->id;
                $savecategory->category_id = $category;
                // dd($savecategory);

                $savecategory->save();
            }
        }

        return redirect()->route('speakers')->with('success', 'Sponser Added Successfully!');
    }
    public function edit($id)
    {
        $edit = Speakers::where('id', $id)->first();
        $assignedcategories = SpeakerAssignedCategories::where('speaker_id',$id)->get()->toArray();
        $categories = SpeakerCategories::where('isactive','active')->get();
        $arrayfilter = array_column($assignedcategories, 'category_id');
        
        // echo "<pre/>";print_r($arrayfilter);
        // echo "hii";
        // echo "<pre/>";print_r($categories);die;
        return view('myadmin.speakers.edithtml')->with(['statusArrays' => $this->statusArrays, 'info' => $edit , 'categories'=>$categories ,'assignedcategories' => $arrayfilter]);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'title_en' => 'required',
            'isactive' => 'required',
        ]);
        $post =  Speakers::where('id', $request->sponserid)->first();
        $post->title_en = $request->title_en;
        $post->isactive = $request->isactive;
        $post->title_hi = $request->title_hi;
        $post->description = $request->description;
        $image_path = "";
        if (!empty($request->file('image_file'))) {
            $feature_image = $request->file('image_file');
            $image = time() . '.' . $feature_image->getClientOriginalExtension();
            $feature_image->move(public_path('/uploads/conference'), $image);
            $image_path = '/uploads/conference/' . $image;
        } else {
            $image_path = $post->image_file;
        }
        $post->image_file = $image_path;
        $post->save();
        if($request->category){

            SpeakerAssignedCategories::where('speaker_id',$post->id)->delete();
            $categories = $request->category;
            foreach($categories as $category){
                

                $storecategory = new SpeakerAssignedCategories();
                $storecategory->speaker_id = $post->id;
                $storecategory->category_id  = $category;
                $storecategory->save();
            }
        }
        
        return redirect()->route('speakers')->with('success', 'Sponser Added Successfully!');
    }

    public function deleteSpeakers(Request $request)
    {

        $ids = $request->post_id;
        Speakers::whereIn('id', $ids)->delete();
        return response()->json(['status' => true]);
    }

    public function speakerstatus(Request $request)
    {

        $pageids = $request->post_id;
        $status_type = $request->status_type;
        Speakers::whereIn('id', $pageids)->update(['isactive' => $status_type]);
        return response()->json(['status' => true]);
    }





    public function categories()
    {
        $categories = SpeakerCategories::get();
        $totalrecords = SpeakerCategories::count();
        return
            view('myadmin.speakersCategory.listhtml')->with(['totalrecords'=>'Speaker Category : '.$totalrecords.' Records found', 'lists' => $categories]);
    }


    public function createcategory()
    {
        return view('myadmin.speakersCategory.createhtml')->with(['statusArrays' => $this->statusArrays]);
    }


    public function storecategory(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'isactive' => 'required',

        ]);

        $post = new SpeakerCategories();
        $post->title = $request->title;
        $post->isactive = $request->isactive;
        $post->save();

        return redirect()->route('categories')->with('success', 'Category Added Successfully!');
    }
    public function editcategory($id)
    {
        $edit = SpeakerCategories::where('id', $id)->first();

        return view('myadmin.speakersCategory.edithtml')->with(['statusArrays' => $this->statusArrays, 'info' => $edit]);
    }

    public function updatecategory(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'isactive' => 'required',
        ]);
        $post =  SpeakerCategories::where('id', $request->categoryId)->first();
        $post->title = $request->title;
        $post->isactive = $request->isactive;
        $post->save();

        return redirect()->route('categories')->with('success', 'Sponser Added Successfully!');
    }

    public function deletecategory(Request $request)
    {

        $ids = $request->post_id;
        SpeakerCategories::whereIn('id', $ids)->delete();
        return response()->json(['status' => true]);
    }

    public function categorystatus(Request $request)
    {
        // dd($request->all());
        $pageids = $request->post_id;
        $status_type = $request->status_type;
        SpeakerCategories::whereIn('id', $pageids)->update(['isactive' => $status_type]);
        return response()->json(['status' => true]);
    }
}
