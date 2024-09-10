<?php

namespace App\Http\Controllers\myadmin;

use App\Http\Controllers\Controller;
use App\Models\myadmin\Banner;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;

class BannerController extends Controller {
     /**
     * @var array<int|string, string>
     */
    protected array $statusArrays;


    public function __Construct() {
		$this->statusArrays = array(''=>'Status', 0=>'inactive',1=>'Active');
	}
    public function index(Request $request): View|Factory|RedirectResponse
    {
		if( getcurrentUserRole() != 'users') {
			return Redirect::route('scientists');
		}
		$search = $request->query('search');
		$Banner = Banner::query();
       

		$search = $request->input('search', ''); // Default to empty string if not provided

if (is_string($search) && $search !== '') {
    $Banner->where('title_en', 'like', '%' . $search . '%');
}
        $banners =  $Banner->orderBy('id', 'DESC')->paginate(20);
        return view('myadmin.bannershtml', [
            'lists' => $banners,
            'search' => $search,
            'totalrecords' => 'Banners : ' . $banners->total() . ' Records found'
        ]);
    }
    public function create(): View|Factory|RedirectResponse
    {
		if( getcurrentUserRole() != 'users') {
			return Redirect::route('scientists');
		}
        return view('myadmin.createbannerhtml', [
            'statusArrays' => $this->statusArrays
        ]);
    }
    public function store(Request $request): RedirectResponse
    {
		$validator = $request->validate(
			[
				'bannerimage' => 'required',
				'isactive' => 'required',
			], 
			[
				'bannerimage.required' => 'The Banner Image is required',
				'isactive.required' => 'The Status is required',
			]
		);
		$banner = new Banner();
		$banner->title_en = $request->input('title_en');
		$banner->title_hi = $request->input('title_hi');
		$banner->titletwo_en = $request->input('titletwo_en');
		$banner->titletwo_hi = $request->input('titletwo_hi');
		//$banner->bannerimage = $request->bannerimage;
		$banner->isactive = $request->input('isactive');
		$banner->description_en = $request->input('description_en');
		$banner->description_hi = $request->input('description_hi');
		// $banner->user_id = Auth()->id();
		$banner->user_id = 1;
		$bannerimageName = '';
		if ($request->hasFile('bannerimage')) {
			$bannerimage = $request->file('bannerimage');
			if (is_array($bannerimage)) {
				$bannerimage = $bannerimage[0]; // Take the first file if it's an array
			}
			if ($bannerimage instanceof \Illuminate\Http\UploadedFile) {
				$bannerimageName = '1' . '_' . time() . '.' . $bannerimage->getClientOriginalExtension();
				$bannerimage->move(public_path('uploads/images'), $bannerimageName);
			}
		}
		if (!empty($bannerimageName)) {
			$banner->bannerimage = $bannerimageName;
		}
		$banner->save();
		return Redirect::route('banners')->with('status', ' Banner has been saved successfully');
    }
	public function edit(Request $request, int $bannerid): View|Factory|RedirectResponse
    {
		if( getcurrentUserRole() != 'users') {
			return Redirect::route('scientists');
		}
		$banners = Banner::where('id',$bannerid)->first();
		if($banners) {
			return view('myadmin.editbannerhtml', [
				'statusArrays' => $this->statusArrays,
				'info' => $banners
			]);
		} else {
			return Redirect::route('banners')->with('status', 'Mentioned Id does not exist.');
		}
    }
	public function update(Request $request, int $bannerid): RedirectResponse
    {
		if( getcurrentUserRole() != 'users') {
			return Redirect::route('scientists');
		}
		$validator = $request->validate(
			[
			
				'isactive' => 'required',
			], 
			[
				
				'isactive.required' => 'The Status is required',
			]
		);
		$banner = Banner::findOrFail($bannerid);
		if( $banner ) {
			$banner->title_en = $request->input('title_en');
			$banner->title_hi = $request->input('title_hi');
			$banner->titletwo_en = $request->input('titletwo_en');
			$banner->titletwo_hi = $request->input('titletwo_hi');
			$banner->isactive = $request->input('isactive');
			$banner->description_en = $request->input('description_en');
			$banner->description_hi = $request->input('description_hi');
			// $banner->user_id = Auth()->id();
			$bannerimageName = '';
			if ($request->hasFile('bannerimage')) {
				$bannerimage = $request->file('bannerimage');
				if (is_array($bannerimage)) {
					$bannerimage = $bannerimage[0]; // Take the first file if it's an array
				}
				if ($bannerimage instanceof \Illuminate\Http\UploadedFile) {
					$bannerimageName = '1' . '_' . time() . '.' . $bannerimage->getClientOriginalExtension();
					$bannerimage->move(public_path('uploads/images'), $bannerimageName);
				}
			}
			if (!empty($bannerimageName)) {
				$banner->bannerimage = $bannerimageName;
			}
			$banner->save();
			return Redirect::route('banners')->with('status', ' Banner has been updated successfully');
		} else {
			return Redirect::route('banners')->with('status', 'Mentioned Id does not exist.');
		}
    }
    public function deleterecords(Request $request): JsonResponse
    {
		if( getcurrentUserRole() != 'users') {
			return Response::json(['status' => false, 'message' => 'Unauthorized'], 403);
		}
		$bannerids = $request->input('post_id');
        Banner::whereIn('id',$bannerids)->delete();
		return Response::json(['status'=>true]);
    }
	public function commponstatus(Request $request): JsonResponse
    {
		if( getcurrentUserRole() != 'users') {
			return Response::json(['status' => false, 'message' => 'Unauthorized'], 403);
		}
		$bannerids = $request->input('post_id');
		$status_type = $request->input('status_type');
		Banner::whereIn('id',$bannerids)->update(['isactive' => $status_type]);
        //DB::table('banners')->whereIn('bannerid', $bannerids)->update(['isactive' => $status_type]);
		return Response::json(['status'=>true]);
    }
}
