<?php

namespace App\Http\Controllers\myadmin;

use App\Http\Controllers\Controller;
use App\Models\myadmin\ConferenceSponsers;
use App\Models\myadmin\Post;
use App\Models\myadmin\Sponsers;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Contracts\View\Factory;

class ConferenceSponsersController extends Controller
{
    private array $statusArrays;

    public function __construct()
    {
        $this->statusArrays = ['' => 'Status', 'inactive' => 'inActive', 'active' => 'Active'];
    }

    public function sponsers(Request $request): View|Factory
    {
        $search = $request->query('search');
        $sponsers = Sponsers::where('title_en','!=','')
        ->where(function($query) use($search){
            $query->where('title_en','LIKE','%'.$search.'%');
        })
        ->orderBy('sortorder', 'ASC')->get();
        $totalrecords = Sponsers::count();

        return view('myadmin.conference_sponsers.listhtml', [
            'search' => $search,
            'totalrecords' => $totalrecords,
            'lists' => $sponsers
        ]);
    }

    public function create(): View|Factory
    {
        return view('myadmin.conference_sponsers.createhtml', [
            'statusArrays' => $this->statusArrays
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'pagename_en' => 'required',
            'isactive' => 'required',
            'image_file' => 'required',
            'sponser_category' => 'required',
        ]);

        $post = new Sponsers();
        $post->title_en = $request->input('pagename_en');
        $post->sponser_category = $request->input('sponser_category');
        $post->isactive = $request->input('isactive');
        $post->link = $request->input('link');
        $post->title_hi = $request->input('pagename_hi');
        $image_path = "";
        if ($request->hasFile('image_file')) {
            $feature_image = $request->file('image_file');
            $image = time() . '.' . $feature_image->getClientOriginalExtension();
            $feature_image->move(public_path('/uploads/conference'), $image);
            $image_path = '/uploads/conference/' . $image;
        }
        $post->image_file = $image_path;
        $post->save();

        return Redirect::route('sponsers')->with('success', 'Sponser Added Successfully!');
    }

    public function edit(int $id): View|Factory
    {
        $edit = Sponsers::where('id', $id)->first();

        return view('myadmin.conference_sponsers.edithtml', [
            'statusArrays' => $this->statusArrays,
            'info' => $edit
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'title_en' => 'required',
            'isactive' => 'required',
            'sponser_category' => 'required',
        ]);

        $post = Sponsers::find($request->input('sponserid'));

        if (!$post) {
            return Redirect::route('sponsers')->with('error', 'Sponser not found!');
        }

        $post->title_en = $request->input('title_en');
        $post->sponser_category = $request->input('sponser_category');
        $post->isactive = $request->input('isactive');
        $post->link = $request->input('link');
        $post->title_hi = $request->input('title_hi');

        $image_path = "";
        if ($request->hasFile('image_file')) {
            $feature_image = $request->file('image_file');
            $image = time() . '.' . $feature_image->getClientOriginalExtension();
            $feature_image->move(public_path('/uploads/conference'), $image);
            $image_path = '/uploads/conference/' . $image;
        }else{
            $image_path = $post->image_file;
        }
        $post->image_file = $image_path;
        $post->save();

        return Redirect::route('sponsers')->with('success', 'Sponser Updated Successfully!');
    }

    public function deletesponsers(Request $request): JsonResponse
    {
        $ids = $request->input('post_id');
        Sponsers::whereIn('id',$ids)->delete();
        return Response::json(['status'=>true]);
    }

    public function sponserstatus(Request $request): JsonResponse
    {
        // dd($request->all());
        $pageids = $request->input('post_id');
        $status_type = $request->input('status_type');
        Sponsers::whereIn('id',$pageids)->update(['isactive' => $status_type]);
        return Response::json(['status'=>true]);
    }

    public function commponupdateorderascdesc(Request $request): JsonResponse
	{

		$orders = $request->input('order');
		// dd($orders); 
		foreach ($orders as $order) {
			
			$id = $order['id'];
				$position = $order['position'];

			$list = Sponsers::where('id', $id)->firstOrFail();
			$list->sortorder = $position;
			$list->save();
			// print_r($order);
		}
		// die;

		return Response::json(['status' => 'success']);
	}

}
