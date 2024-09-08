<?php

namespace App\Http\Controllers\myadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\myadmin\category;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;

class CategoryController extends Controller {
	
   /**
     * @var array<int|string, string>
     */
    protected array $statusArrays;


    public function __Construct() {
		$this->statusArrays = array(''=>'Status', 0=>'inactive',1=>'Active');
	}
	
    public function index(Request $request): View|Factory {
		$current_uri = request()->segments();
		$cat_type =  end($current_uri);
		
		$category = category::query();
		$search = $request->query('search');
		
		if( request('search') ) {
			$category->where('catname','LIKE','%'.request('search').'%');
		}
		$lists = $category->orderBy('id','ASC')->where('type',$cat_type)->paginate(20);
		return view('myadmin.categories.listhtml',['lists' =>$lists,'cat_type'=> $cat_type, 'search'=>$search,'totalrecords'=>ucwords($cat_type).' Categories : '.$lists->count().' Records found'] );
    }
    public function create(): View|Factory {
		$current_uri = request()->segments();
		$cat_type =  end($current_uri);
		$parentcats = category::where('parentid',0)->where('type','blogs')->get();
        return view('myadmin.categories.createhtml',['cat_type'=> $cat_type,'statusArrays'=>$this->statusArrays, 'parentcats'=> $parentcats]);
    }	
	public function store(Request $request): RedirectResponse {
		$validator = $request->validate(
			[
				'catname' => 'required|max:100',
				'isactive' => 'required'
			], 
			[
				'catname.required' => 'The Category name is required',
				'isactive.required' => 'The Status is required',
			]
		);
		$category = new category();
		$category->catname = $request->input('catname');
		$category->parentid = $request->input('parentid');
		$category->isactive = $request->input('isactive');
		$category->type = $request->input('type');
		$category->user_id = Auth()->id();
		$category->save();
		return Redirect::route('categories', $request->input('type'))->with('status', ' Categories has been saved successfully');
    }
	public function edit(Request $request, int $id): View|Factory|RedirectResponse {
		$categories = category::where('id',$id)->first();
		$parentcats = category::where('parentid',0)->where('type','blogs')->get();
		if($categories) {
			return view('myadmin.categories.edithtml')
				->with('statusArrays',$this->statusArrays)
				->with('info',$categories)
				->with('parentcats',$parentcats);
		} else {
			return Redirect::route('banners')->with('status', 'Permission denied for this.');
		}
    }
	public function update(Request $request, int $id): RedirectResponse {
		$request_data = $request->all();       
		$validator = $request->validate(
			[
				'catname' => 'required|max:100',
				'isactive' => 'required'
			], 
			[
				'catname.required' => 'The Category name is required',
				'isactive.required' => 'The Status is required',
			]
		);
		$category = category::find($id);
		if( $category ){
			$category->catname = $request->input('catname');
			$category->isactive = $request->input('isactive');
			$category->type = $request->input('type');
			$category->parentid = $request->input('parentid');
			$category->user_id = Auth()->id();
			$category->save();
			return Redirect::route('categories', $request->input('type'))->with('status', ' Categories has been updated successfully');
		} else {
			return Redirect::route('categories', $request->input('type'))->with('status', 'Missing parameters');
		}
    }
	public function activitiescats(Request $request): View|Factory {
		$category = category::query();
		$search = $request->query('search');
		
		if( request('search') ) {
			$category->where('catname','LIKE','%'.request('search').'%');
		}
		$lists = $category->orderBy('id','DESC')->where('type','activities')->paginate(20);
		return view('myadmin.categories.listhtml',['lists' =>$lists, 'search'=>$search,'totalrecords'=>'School Activities Category : '.$lists->count().' Records found'] );
    }

}
