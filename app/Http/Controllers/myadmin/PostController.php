<?php

namespace App\Http\Controllers\myadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\myadmin\Post;
use App\Models\myadmin\category;
use Validator;
use Cviebrock\EloquentSluggable\Services\Slugservice;
use Illuminate\Support\Str;

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class PostController extends Controller {
	
	   /**
     * @var array<string, string>
     */
    protected array $statusArrays;

    /**
     * @var Collection<int, category>
     */
    protected Collection $catlists;

	 /**
     * @var array<string, string>
     */
    protected array $imageposition;

	 /**
     * @var array<string, string>
     */
    protected array $templates;
   
	public function __Construct() {
		
		$this->statusArrays = ['' => 'Status', 'inactive' => 'inActive', 'active' => 'Active'];

        $this->catlists = new Collection(category::query()
            ->select("id", "catname")
            ->where('type', 'activities')
            ->where('isactive', 'active')
            ->orderBy('catname', 'ASC')
            ->get());

		$this->imageposition = [''=>'None', 'left'=>'Left','right'=>'Right','top'=>'Top','bottom'=>'Bottom'];
		
		$this->templates = ['default'=>'Default Template','annualreports'=>'Annual Reports Template','newsupdates'=>'News & Updates Template','latestupdates'=>'Latest updates Template','adminstaff'=>'Staff photo Template','mou'=>'MOU Template','albums'=>'Gallery Template','bogs'=>'BOG Template','raac'=>'RAAC Template','conferencesworkshop'=>'conference & Workshops Template','student'=>'Student Template','scientists'=>'Scientists Template','researchunites'=>'Research unit Template','honoraryadjunctfaculty'=>'Honorary Adjunct Faculty Template','admissions'=>'Admission Template','tenders'=>'Tenders Template','cif'=>'Central Iinstrument Facility Template','technology'=>'Techonology Template','deans'=>'Deans Template','contactus'=>'Contactus Template','downloads'=>'Downloads Template'];
		
		
	}
    public function index(Request $request) : View|Factory{
		$posts = Post::query();
		$search = $request->query('search');
		
		$search = $request->input('search', ''); // Default to empty string if not provided
 
if (is_string($search) && $search !== '') {
    $posts->where('pagename_en', 'like', '%' . $search . '%');
}
		// if( request('search') ) {
		// 	$posts->where('pagename_en','LIKE','%'.request('search').'%');
		// }
		$lists = $posts->orderBy('sortorder','ASC')->where('pagetype','pages')->paginate(50);
		return view('myadmin.cmspageshtml',['lists' =>$lists, 'search'=>$search,'totalrecords'=>'Pages : '.$lists->total().' Records found'] );
    }
    public function create(): View|Factory {
        return view('myadmin.createpagehtml',[
				'statusArrays'=>$this->statusArrays,
				'imageposition'=>$this->imageposition,
				'templates'=>$this->templates
			]
		);
    }
    public function store(Request $request): RedirectResponse {
        $validator = $request->validate(
			[
				'pagename_en' => 'required|max:200',
				'isactive' => 'required'
			], 
			[
				'pagename_en.required' => 'The Page name is required',
				'isactive.required' => 'The Published status is required',
			]
		);
		//$page_slug = Slugservice::createSlug(Post::class,'slug', $request->pagename);

		$pge_en=$request->input('pagename_en');

		// Ensure $pge_en is a string
		if (!is_string($pge_en)) {
			$pge_en = ''; // Default value or handle as needed
		}
		$page_slug = $this->generateUniqueSlug($pge_en);
		$post = new Post();
		$post->pagename_en = $request->input('pagename_en');
		$post->pagename_hi = $request->input('pagename_hi');
		$post->description_en = $request->input('description_en');
		$post->description_hi = $request->input('description_hi');
		$post->slug = $page_slug;
		$post->target_blank = $request->input('target_blank');
		$post->external_link = $request->input('external_link');


		if ($request->hasFile('you_tube')) {
			$video = $request->file('you_tube');
			if ($video instanceof \Illuminate\Http\UploadedFile) {
				$videoName = Auth::id() . '_' . time() . '.' . $video->getClientOriginalExtension();
				$video->move(public_path('uploads/videos'), $videoName);
				$post->you_tube_link = $videoName;
			}
		}
		
		$post->isactive = $request->input('isactive');
		$post->template = $request->input('template');
		$post->pagetype = $request->input('pagetype');
		//$post->pagebanner = $request->pagebanner;
		$post->imageposition = $request->input('imageposition');
		$post->meta_title = $request->input('meta_title');
		$post->meta_description = $request->input('meta_description');
		$post->meta_keyword = $request->input('meta_keyword');
		if( !empty($request->postdate)) {
			$post->postdate = convertdate($request->postdate);
		}
		if( !empty($request->regpostdate)) {
			$post->regpostdate = convertdate($request->regpostdate);
		}
		$featureimageName = '';
		$pagebannerName = '';
		if ($request->hasFile('feature_image')) {
			$featureimage = $request->file('feature_image');
			if ($featureimage instanceof \Illuminate\Http\UploadedFile) {
				$featureimageName = Auth::id() . '_' . time() . '.' . $featureimage->getClientOriginalExtension();
				$featureimage->move(public_path('uploads/images'), $featureimageName);
			}
		}
		if ($request->hasFile('pagebanner')) {
			$pagebanner = $request->file('pagebanner');
			if ($pagebanner instanceof \Illuminate\Http\UploadedFile) {
				$pagebannerName = Auth::id() . '_' . time() . '.' . $pagebanner->getClientOriginalExtension();
				$pagebanner->move(public_path('uploads/images'), $pagebannerName);
			}
		}
		if( !empty($featureimageName) ) {
			$post->feature_image = $featureimageName;
		}
		if( !empty($pagebannerName) ) {
			$post->pagebanner = $pagebannerName;
		}
		$post->meta_keyword = $request->input('meta_keyword');
		$post->user_id = Auth::id();
		$post->save();
	
		// Ensure pagetype is a string
		$pagetype = $request->input('pagetype', 'pages');

		// Ensure $pagetype is a string
		if (!is_string($pagetype)) {
			$pagetype = 'pages'; // Fallback to a default route if the type is not correct
		}
		return Redirect::route($pagetype)->with('status', ' Content has been saved successfully');
    }
    public function edit(int $id): View|Factory|RedirectResponse {
		$posts = Post::where('id',$id)->where('pagetype','pages')->first();
		if($posts) {
			return view('myadmin.editcmspagehtml', [
					'statusArrays'=>$this->statusArrays,
					'imageposition'=>$this->imageposition,
					'templates'=>$this->templates,
					'info'=>$posts
				]
			);
		} else {
			return Redirect::route('pages')->with('status', 'Mentioned Id does not exist.');
		}  
    }
    public function update(Request $request, int $id): RedirectResponse {
		$validator = $request->validate(
			[
				'pagename_en' => 'required|max:200',
				'isactive' => 'required'
			], 
			[
				'pagename_en.required' => 'The Page name is required',
				'isactive.required' => 'The Published status is required',
			]
		);
		$post = Post::find($id);
		if( $post ){
			//$page_slug = $this->generateUniqueSlug($request->pagename_en);
			//$post->slug = $page_slug;
			$post->pagename_en = $request->input('pagename_en');
			$post->pagename_hi = $request->input('pagename_hi');
			$post->description_en = $request->input('description_en');
			$post->description_hi = $request->input('description_hi');
			$post->target_blank = $request->input('target_blank');
			$post->external_link = $request->input('external_link');
			if ($request->hasFile('you_tube')) {
				$video = $request->file('you_tube');
				if ($video instanceof \Illuminate\Http\UploadedFile) {
					$videoName = Auth::id() . '_' . time() . '.' . $video->getClientOriginalExtension();
					$video->move(public_path('uploads/videos'), $videoName);
					$post->you_tube_link = $videoName;
				}
			}
	
			//$post->feature_image = $request->feature_image;
			$post->isactive = $request->input('isactive');
			$post->template = $request->input('template');
			$post->pagetype = $request->input('pagetype');
			//$post->pagebanner = $request->pagebanner;
			$post->imageposition = $request->input('imageposition');
			$post->meta_title = $request->input('meta_title');
			$post->meta_description = $request->input('meta_description');
			$post->meta_keyword = $request->input('meta_keyword');
			if( !empty($request->postdate)) {
				$post->postdate = convertdate($request->postdate);
			}
			if( !empty($request->regpostdate)) {
				$post->regpostdate = convertdate($request->regpostdate);
			}
			$featureimageName = '';
			$pagebannerName = '';
			$cifpdf = '';
			if ($request->hasFile('feature_image')) {
				$featureimage = $request->file('feature_image');
				if ($featureimage instanceof \Illuminate\Http\UploadedFile) {
					$featureimageName = Auth::id() . '_' . time() . '.' . $featureimage->getClientOriginalExtension();
					$featureimage->move(public_path('uploads/images'), $featureimageName);
				}
			}
			if ($request->hasFile('pagebanner')) {
				$pagebanner = $request->file('pagebanner');
				if ($pagebanner instanceof \Illuminate\Http\UploadedFile) {
					$pagebannerName = Auth::id() . '_' . time() . '.' . $pagebanner->getClientOriginalExtension();
					$pagebanner->move(public_path('uploads/images'), $pagebannerName);
				}
			}
			if ($request->hasFile('cif_image')) {
				$cifimage = $request->file('cif_image');
				if ($cifimage instanceof \Illuminate\Http\UploadedFile) {
					$cifpdf = Auth::id() . '_' . time() . '.' . $cifimage->getClientOriginalExtension();
					$cifimage->move(public_path('uploads/images'), $cifpdf);
				}
			}
			if( !empty($featureimageName) ) {
				$post->feature_image = $featureimageName;
			}
			if( !empty($pagebannerName) ) {
				$post->pagebanner = $pagebannerName;
			}
			if( !empty($cifpdf) ) {
				$post->cif_image = $cifpdf;
			}
			$post->user_id = Auth::id();
			$post->save();
			
			// Ensure pagetype is a string
			$pagetype = $request->input('pagetype', 'pages');

			// Ensure $pagetype is a string
			if (!is_string($pagetype)) {
				$pagetype = 'pages'; // Fallback to a default route if the type is not correct
			}
			return Redirect::route($pagetype)->with('status', ' Content has been updated successfully');
		} else {
			
			// Ensure pagetype is a string
			$pagetype = $request->input('pagetype', 'pages');

			// Ensure $pagetype is a string
			if (!is_string($pagetype)) {
				$pagetype = 'pages'; // Fallback to a default route if the type is not correct
			}
			return Redirect::route($pagetype)->with('status', 'Mentioned Id does not exist.');
		}
    }
	public function commponcmsstatus( Request $request):JsonResponse {
		$pageids = $request->input('post_id');
		$status_type = $request->input('status_type');
		Post::whereIn('id',$pageids)->update(['isactive' => $status_type]);
		return Response::json(['status'=>true]);
	}
	/*************************/
	public function newsevents(Request $request): View|Factory {
	//	$search = $request->query('search');
		$catid = $request->query('catid');
		$query = Post::join('categories', 'categories.id', '=', 'posts.catid')
				->where('posts.pagetype','=','newsevents')
				->orderBy('posts.id','DESC');	

				$search = $request->input('search', ''); // Default to empty string if not provided
 
if (is_string($search) && $search !== '') {
    $query->where('pagename', 'like', '%' . $search . '%');
}
		// if( request('search') ) {
		// 	$query->where( 'pagename','LIKE','%'.request('search').'%');
		// }	
		if( request('catid') ) {
			$query->where( 'catid','=',request('catid') );
		}
		$lists = $query->paginate(40,['posts.*', 'categories.catname']);
		
		return view('myadmin.newsevents.newseventshtml',['lists' =>$lists,'catlists'=>$this->catlists,  'catid'=>$catid, 'search'=>$search,'totalrecords'=>'News & events : '.$lists->total().' Records found'] );
    }
	public function createnewsevent(): View|Factory {
        return view('myadmin.newsevents.createnewseventhtml',[
				'statusArrays'=>$this->statusArrays,
				'catlists' => $this->catlists
			]
		);
    }
	public function editnewsevent(int $id): View|Factory|RedirectResponse {
		$posts = Post::where('id',$id)->where('pagetype','newsevents')->first();
		if($posts) {
			return view('myadmin.newsevents.editnewseventhtml', [
					'statusArrays'=>$this->statusArrays,
					'imageposition'=>$this->imageposition,
					'templates'=>$this->templates,
					'catlists'=>$this->catlists,
					'info'=>$posts
				]
			);
		} else {
			return Redirect::route('newsevents')->with('status', 'Mentioned Id does not exist.');
		}  
    }
	public function deleterecords(Request $request):JsonResponse {
		$ids = $request->input('post_id');
        Post::whereIn('id',$ids)->delete();
		return Response::json(['status'=>true]);
    }
	
	private function generateUniqueSlug(string $title): string {
        $temp = str_slug($title, '-');
        if(!Post::all()->where('slug',$temp)->isEmpty()){
			$i = 1;
			$newslug = $temp . '-' . $i;
			while(!Post::all()->where('slug',$newslug)->isEmpty()){
				$i++;
				$newslug = $temp . '-' . $i;
			}
			$temp =  $newslug;
		}
		return $temp;
	}
	////////////////////////////////////////////////////////////////////
public function removepages(Request $request): RedirectResponse {
		$ids = $request->input('pageid');
		// dd($ids);
        Post::where('id',$ids)->delete();
		return Redirect::route('pages')->with('status', 'Page Remove Successfully');
    }
	////////////////////////////////////////////////////////////////////


	public function websitepagesUpdateOrder(Request $request):JsonResponse
	{
   /** 
         * @var array<array{id: int, position: int}> $orders
         */
		$orders = $request->input('order');
		
		foreach ($orders as $order) {
			$id = $order['id'];
			$position = $order['position'];
	
 // Retrieve a single Post model instance
            /** @var \App\Models\myadmin\Post|null $list */

			$list = Post::
				where('id', $id)
				->firstOrFail();
				if ($list !== null) {
					// Ensure $list is not null before accessing properties
					$list->sortorder = $position;
					$list->save();
				}
		
		}
	

		return Response::json(['status' => 'success']);
	} 

	// public function storeconference(Request $request)
	// {
	// 	$this->validate($request, [
	// 		'photo_file' => 'mimes:png,jpeg,jpg,gif|max:3000',
	// 		'audio_file' => 'mimes:mpga,wav', // mpga = mp3
	// 		'video_file' => 'mimes:mp4,ogv,webm'
	// 	]);

	// 	$next_nor_no = Topic::where('webmaster_id', '=', $webmasterId)->max('row_no');
	// 	if ($next_nor_no < 1) {
	// 		$next_nor_no = 1;
	// 	} else {
	// 		$next_nor_no++;
	// 	}

	// 	// Start of Upload Files
	// 	$formFileName = "photo_file";
	// 	$fileFinalName = "";
	// 	if ($request->input($formFileName) != "") {
	// 		$fileFinalName = time() . rand(1111,
	// 				9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
	// 		$path = $this->getUploadPath();
	// 		$request->file($formFileName)->move($path, $fileFinalName);
	// 	}

	// 	$formFileName = "audio_file";
	// 	$audioFileFinalName = "";
	// 	if ($request->input($formFileName) != "") {
	// 		$audioFileFinalName = time() . rand(1111,
	// 				9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
	// 		$path = $this->getUploadPath();
	// 		$request->file($formFileName)->move($path, $audioFileFinalName);
	// 	}

	// 	$formFileName = "attach_file";
	// 	$attachFileFinalName = "";
	// 	if ($request->input($formFileName) != "") {
	// 		$attachFileFinalName = time() . rand(1111,
	// 				9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
	// 		$path = $this->getUploadPath();
	// 		$request->file($formFileName)->move($path, $attachFileFinalName);
	// 	}

	// 	if ($request->input('video_type') == 3) {
	// 		$videoFileFinalName = $request->input('embed_link');
	// 	} elseif ($request->input('video_type') == 2) {
	// 		$videoFileFinalName = $request->input('vimeo_link');
	// 	} elseif ($request->input('video_type') == 1) {
	// 		$videoFileFinalName = $request->input('youtube_link');
	// 	} else {
	// 		$formFileName = "video_file";
	// 		$videoFileFinalName = "";
	// 		if ($request->input($formFileName) != "") {
	// 			$videoFileFinalName = time() . rand(1111,
	// 					9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
	// 			$path = $this->getUploadPath();
	// 			$request->file($formFileName)->move($path, $videoFileFinalName);
	// 		}

	// 	}
	// 	// End of Upload Files


	// 	// create new topic
	// 	$Topic = new Topic;

	// 	// Save topic details
	// 	$Topic->row_no = $next_nor_no;
	// 	$Topic->title_ar = $request->input('title_ar');
	// 	$Topic->title_en = $request->input('title_en');

	// 	$Topic->details_ar = $request->input('details_ar');
	// 	$Topic->details_en = $request->input('details_en');
	// 	$Topic->date = $request->input('date');
	// 	if (@$request->input('expire_date') != "") {
	// 		$Topic->expire_date = $request->input('expire_date');
	// 	}
	// 	if ($fileFinalName != "") {
	// 		$Topic->photo_file = $fileFinalName;
	// 	}
	// 	if ($audioFileFinalName != "") {
	// 		$Topic->audio_file = $audioFileFinalName;
	// 	}
	// 	if ($attachFileFinalName != "") {
	// 		$Topic->attach_file = $attachFileFinalName;
	// 	}
	// 	if ($videoFileFinalName != "") {
	// 		$Topic->video_file = $videoFileFinalName;
	// 	}
	// 	$Topic->icon = $request->input('icon');
	// 	$Topic->video_type = $request->input('video_type');
	// 	$Topic->webmaster_id = $webmasterId;
	// 	$Topic->created_by = Auth::user()->id;
	// 	$Topic->visits = 0;
	// 	$Topic->status = 1;
	// 	if ($webmasterId == 17){
	// 	$Topic->speakers = serialize($request->input('speakers'));
		
	// 	}
	// 	if ($webmasterId == 19){
	// 	$Topic->r_unit = serialize($request->input('r_unit'));
		
	// 	}
		
		
	// 	  if ($request->input('scientist_id') != "") {
	// 		// Save scientist
	// 		foreach ($request->input('scientist_id') as $scientist) {
	// 			if ($scientist > 0) {
	// 				$TopicScientist = new TopicScientist;
	// 				$TopicScientist->topic_id = $Topic->id;
	// 				$TopicScientist->user_id = $scientist;
	// 				$TopicScientist->save();
	// 			}
	// 		}
	// 	}

	// 	// Save additional Fields
	// 	if (count($WebmasterSection->customFields) > 0) {
	// 		foreach ($WebmasterSection->customFields as $customField) {
	// 			$field_value_var = "customField_" . $customField->id;

	// 			if ($request->input($field_value_var) != "") {
	// 				if ($customField->type == 8 || $customField->type == 9 || $customField->type == 10) {
	// 					// upload file
	// 					if ($request->input($field_value_var) != "") {
	// 						$uploadedFileFinalName = time() . rand(1111,
	// 								9999) . '.' . $request->file($field_value_var)->getClientOriginalExtension();
	// 						$path = $this->getUploadPath();
	// 						$request->file($field_value_var)->move($path, $uploadedFileFinalName);
	// 						$field_value = $uploadedFileFinalName;
	// 					}
	// 				} elseif ($customField->type == 7) {
	// 					// if multi check
	// 					$field_value = implode(", ", $request->input($field_value_var));
	// 				} else {
	// 					$field_value = $request->input($field_value_var);
	// 				}
	// 				$TopicField = new TopicField;
	// 				$TopicField->topic_id = $Topic->id;
	// 				$TopicField->field_id = $customField->id;
	// 				$TopicField->field_value = $field_value;
	// 				$TopicField->save();
	// 			}

	// 		}
	// 	}


	// }
	
}
