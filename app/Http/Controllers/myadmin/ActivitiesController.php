<?php

namespace App\Http\Controllers\myadmin;

use Illuminate\Http\Request;
use App\Models\myadmin\Coordinator;
use App\Models\myadmin\category;
use App\Models\myadmin\Post;
use App\Models\myadmin\Albumimage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;

class ActivitiesController extends Controller
{

	   /**
     * @var array<string, string>
     */
    protected array $statusArrays;

    /**
     * @var Collection<int, category>
     */
    protected Collection $catlists;

    public function __construct()
    {
        $this->statusArrays = ['' => 'Status', 'inactive' => 'inActive', 'active' => 'Active'];

        $this->catlists = new Collection(category::query()
            ->select("id", "catname")
            ->where('type', 'activities')
            ->where('isactive', 'active')
            ->orderBy('catname', 'ASC')
            ->get());
    }
	public function index(Request $request): View|Factory
	{

		$albumid = $request->query('albumid');
		$search = $request->query('search');
		$Query = Albumimage::query();
		if (request('search')) {
			$Query->where('title', 'Like', '%' . request('search') . '%');
		}
		$albuminfo = Coordinator::where('id', $albumid)->where('type', 'albums')->first();
		// dd($request->query('albumid'));
		$lists =  $Query->orderBy('order', 'ASC')->where('albumid', $albumid)->paginate(30)->appends('albumid', $albumid);

		return view('myadmin.albumsimages.listhtml', ['lists' => $lists, 'search' => $search, 'albuminfo' => $albuminfo, 'totalrecords' => 'Album ' . $albuminfo->name . ' : ' . $lists->total() . ' images found']);
	}
	public function create(Request $request): View|Factory
	{
		$albumid = $request->query('albumid');
		$albuminfo = Coordinator::where('id', $albumid)->where('type', 'albums')->first();
		return view(
			'myadmin.albumsimages.createhtml',
			[
				'statusArrays' => $this->statusArrays,
				'albuminfo' => $albuminfo,
			]
		);
	}
	public function edit(int $albumid): View|Factory|RedirectResponse
	{

		$albuminfo = Coordinator::where('id', $albumid)->where('type', 'albums')->first();
		if ($albumid) {
			return view(
				'myadmin.albumsimages.edithtml',
				[
					'statusArrays' => $this->statusArrays,
					'catlists' => $this->catlists,
					'info' => $albuminfo
				]
			);
		} else {
			return Redirect::route('albumimages')->with('status', 'Mentioned Id does not exist.');
		}
	}
	// public function store(Request $request) {
	// 	$validator = $request->validate(
	// 		[
	// 			'albumid' => 'required|max:100',
	// 			'subimagesarray' => 'required',
	// 		], 
	// 		[
	// 			'albumid.required' => 'The album name is required',
	// 			'subimagesarray.required' => 'The Image Image is required',
	// 		]
	// 	);
	// 	$albumid = $request->albumid;
	// 	$tititle = $request->name;
	// 	$captionsarray = $request->subimagesarray;
	// 	if( !empty($captionsarray) ) {
	// 		$image_urls = explode(',',$captionsarray);
	// 		foreach ($image_urls as $image_url ):
	// 			$image_url_params[] = array(
	// 				'albumid' => $albumid,
	// 				'tititle' => $tititle,
	// 				'isactive' => 1,
	// 				'created_at' => date('Y-m-d H:i:s'),
	// 				'feature_image' => str_replace(array('["','"]','"'),array(''),$image_url)
	// 			);
	// 		endforeach;
	// 		Albumimage::insert($image_url_params);
	// 	}
	// 	return redirect()->route('albumimages',['albumid' =>$albumid])->with('status', ' Content has been saved successfully');
	// }
	public function store(Request $request): RedirectResponse
	{

		$validator = $request->validate([
			'albumid' => 'required|max:100',
			'feature_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
			'photoname' => 'required',
		]);

		$albumid = $request->input('albumid');
		$name = $request->input('name');
		$photoname = $request->input('photoname');
		$campus = '';
		if (!empty($request->file('feature_image'))) {
			$feature_image = $request->file('feature_image');
			$campus = time() . '.' . $feature_image->getClientOriginalExtension();
			$feature_image->move(public_path('/uploads/campus-tour'), $campus);
		}
		$image_path = '/uploads/campus-tour/' . $campus;

		$image_data = [
			'albumid' => $albumid,
			'tititle' => $name,
			'photoname' => $photoname,
			'isactive' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'feature_image' => $image_path,
		];

		$save = Albumimage::insert($image_data);
		return Redirect::route('albumimages', ['albumid' => $albumid])
			->with('status', 'Content has been saved successfully');
	}





	public function deletealbumimage(Request $request): JsonResponse
	{
		$ids = $request->input('post_id');
		Albumimage::whereIn('id', $ids)->delete();
		return Response::json(['status' => true]);
	}

	public function deletealbumimage_direct(Request $request): RedirectResponse 
	{
		$itemid = $request->input('itemid');
		Albumimage::where('id', $itemid)->delete();
		return Redirect::back()->with('status', ' Content has been deleted successfully');;
	}


	public function update(Request $request, int $id): RedirectResponse
	{
		$request_data = $request->all();
		$validator = $request->validate(
			[
				'name' => 'required|max:100',
				'isactive' => 'required'
			],
			[
				'name.required' => 'The Name is required',
				'isactive.required' => 'The Status is required',
			]
		);
		unset($request_data['_token']);
		$result_query = DB::table('coordinators')->where('id', $id)->update($request_data);
		return Redirect::route($request->input('type'))->with('status', ' Content has been updated successfully');
	}



	public function indexfaulity(Request $request): View|Factory
	{
		$tokenid = $request->query('tokenid');
		$search = $request->query('search');
		$Query = Albumimage::query();
		if (request('search')) {
			$Query->where('title', 'Like', '%' . request('search') . '%');
		}
		$albuminfo = Post::where('id', $tokenid)->first();
		$lists =  $Query->orderBy('id', 'DESC')->where('albumid', $tokenid)->paginate(30)->appends('albumid', $tokenid);
		return view('myadmin.faulities.listhtml', ['lists' => $lists, 'search' => $search, 'albuminfo' => $albuminfo, 'totalrecords' => $albuminfo->pagename_en . ' : ' . $lists->total() . ' records found']);
	}
	public function createfaulity(Request $request): View|Factory
	{
		$tokenid = $request->query('tokenid');
		$albuminfo = Post::where('id', $tokenid)->first();
		return view(
			'myadmin.faulities.createhtml',
			[
				'statusArrays' => $this->statusArrays,
				'heading' => $albuminfo->pagename_en . ': faculities',
				'albuminfo' => $albuminfo,
			]
		);
	}
	public function editfaulity(int $tokenid): View|Factory|RedirectResponse
	{
		$albuminfo = Post::where('id', $tokenid)->first();
		if ($posts) {
			return view(
				'myadmin.faulities.edithtml',
				[
					'statusArrays' => $this->statusArrays,
					'heading' => $albuminfo->pagename_en . ': faculities',
					'info' => $albuminfo
				]
			);
		} else {
			return Redirect::route('indexfaulity')->with('status', 'Mentioned Id does not exist.');
		}
	}

	public function storeadmissionsfile(Request $request): RedirectResponse
	{
		$validator = $request->validate(
			[
				'albumid' => 'required|max:100',
				'attachmentname' => 'required',
				'attachmentfile' => 'max:50000'
			],
			[
				'albumid.required' => 'The album name is required',
				'attachmentname.required' => 'The PDF is required',
				'attachmentfile.required' => 'The PDF is required & size should be less than 5MB',
			]
		);
		$attachmentfileName = '';
		if ($request->hasFile('attachmentfile')) {
			$attachmentfile = $request->file('attachmentfile');
			if (is_array($attachmentfile)) {
				$attachmentfile = $attachmentfile[0]; // Take the first file if it's an array
			}
			if ($attachmentfile instanceof \Illuminate\Http\UploadedFile) {
				$attachmentfileName = $request->input('albumid') . '_' . time() . '.' . $attachmentfile->getClientOriginalExtension();
				$attachmentfile->move(public_path('userpics'), $attachmentfileName);
			}
		}
		$userDetailsInfo = new Albumimage();
		$userDetailsInfo->albumid = $request->input('albumid');
		$userDetailsInfo->tititle = $request->input('attachmentname');
		$userDetailsInfo->isactive = $request->input('isactive');
		if (!empty($attachmentfileName)) {
			$userDetailsInfo->feature_image = $attachmentfileName;
		}
		$saved = $userDetailsInfo->save();
		return Redirect::route('editadmissions', ['id' => $request->input('albumid')])->with('status', ' Content has been updated successfully');
	}
	public function storetenders(Request $request): RedirectResponse
	{
		$validator = $request->validate(
			[
				'albumid' => 'required|max:100',
				'attachmentname' => 'required',
				'attachmentfile' => 'max:50000'
			],
			[
				'albumid.required' => 'The album name is required',
				'attachmentname.required' => 'The attachment is required',
				'attachmentfile.required' => 'The attachment is required & size should be less than 5MB',
			]
		);
		$attachmentfileName = '';
		if ($request->hasFile('attachmentfile')) {
			$attachmentfile = $request->file('attachmentfile');
			if (is_array($attachmentfile)) {
				$attachmentfile = $attachmentfile[0]; // Take the first file if it's an array
			}
			if ($attachmentfile instanceof \Illuminate\Http\UploadedFile) {
				$attachmentfileName = $request->input('albumid') . '_' . time() . '.' . $attachmentfile->getClientOriginalExtension();
				$attachmentfile->move(public_path('userpics'), $attachmentfileName);
			}
		}
		$userDetailsInfo = new Albumimage();
		$userDetailsInfo->albumid = $request->input('albumid');
		$userDetailsInfo->tititle = $request->input('attachmentname');
		$userDetailsInfo->isactive = $request->input('isactive');
		if (!empty($attachmentfileName)) {
			$userDetailsInfo->feature_image = $attachmentfileName;
		}
		$saved = $userDetailsInfo->save();
		return Redirect::route('edittenders', ['id' => $request->input('albumid')])->with('status', ' Content has been updated successfully');
	}


	public function editalbumimages(int $albumimageid): View|Factory|RedirectResponse
	{
		// dd('here');
		$editalbuminfo = Albumimage::where('id', $albumimageid)->first();
		$albuminfo = Coordinator::where('id', $editalbuminfo['albumid'])->where('type', 'albums')->first();
		// dd($albuminfo);
		if ($albumimageid) {
			return view(
				'myadmin.albumsimages.editalbumimagehtml',
				[
					'editalbuminfo' => $editalbuminfo,
					'albuminfo' => $albuminfo
				]
			);
		} else {
			return Redirect::route('albumimages')->with('status', 'Mentioned Id does not exist.');
		}
	}

	public function updatealbumimages(Request $request): RedirectResponse
	{

		$validator = $request->validate([
			'albumid' => 'required|max:100',
			// 'feature_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
			'photoname' => 'required',
		]);

		$albumid = $request->input('albumId');
		$name = $request->input('name');
		$photoname = $request->input('photoname');
		$campus = '';
		$getimg = Albumimage::where('id', $request->input('albumId'))->first();

		if (!empty($request->file('feature_image'))) {
			$feature_image = $request->file('feature_image');
			$campus = time() . '.' . $feature_image->getClientOriginalExtension();
			$feature_image->move(public_path('/uploads/campus-tour'), $campus);
			$image_path = '/uploads/campus-tour/' . $campus;
		} else {
			$getimg = Albumimage::where('id', $request->input('albumId'))->first();
			// dd($getimg);
			$image_path = $getimg['feature_image'];
		}
		$image_data = [
			'albumid' => $albumid,
			'tititle' => $name,
			'photoname' => $photoname,
			'isactive' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'feature_image' => $image_path,
		];
		// dd($getimg['albumid']);
		$update = Albumimage::where('id', $request->input('albumId'))->first();
		$update->tititle = $name;
		$update->photoname = $photoname;
		$update->feature_image = $image_path;

		
		$update->save();
		return Redirect::route('albumimages', ['albumid' => $getimg['albumid']])
			->with('status', 'Content has been Updated successfully');
	}

	public function rearrangecampus_images(Request $request): JsonResponse
	{

		 /** 
         * @var array<array{id: int, position: int}> $orders
         */
		$orders = $request->input('order');

		foreach ($orders as $order) {

			$id = $order['id'];
			$position = $order['position'];


			 // Retrieve a single Post model instance
            /** @var \App\Models\myadmin\Albumimage|null $list */
			$list = Albumimage::where('id', $id)
				->firstOrFail();
				if ($list !== null) {
					// Ensure $list is not null before accessing properties
					$list->sortorder = $position;
					$list->save();
				}
		}

		return Response::json(['status' => 'success']);
	}
}
