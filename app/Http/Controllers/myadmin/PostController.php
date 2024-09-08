<?php

namespace App\Http\Controllers\myadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\myadmin\Post;
use App\Models\myadmin\category;
use Validator;
use Cviebrock\EloquentSluggable\Services\Slugservice;
use Illuminate\Support\Str;

class PostController extends Controller {
	
	public function __Construct() {
		
		$this->statusArrays = array(''=>'Choose status', 'inactive'=>'inActive','active'=>'Active');
		$this->imageposition = array(''=>'None', 'left'=>'Left','right'=>'Right','top'=>'Top','bottom'=>'Bottom');
		
		$this->templates = array('default'=>'Default Template','annualreports'=>'Annual Reports Template','newsupdates'=>'News & Updates Template','latestupdates'=>'Latest updates Template','adminstaff'=>'Staff photo Template','mou'=>'MOU Template','albums'=>'Gallery Template','bogs'=>'BOG Template','raac'=>'RAAC Template','conferencesworkshop'=>'conference & Workshops Template','student'=>'Student Template','scientists'=>'Scientists Template','researchunites'=>'Research unit Template','honoraryadjunctfaculty'=>'Honorary Adjunct Faculty Template','admissions'=>'Admission Template','tenders'=>'Tenders Template','cif'=>'Central Iinstrument Facility Template','technology'=>'Techonology Template','deans'=>'Deans Template','contactus'=>'Contactus Template','downloads'=>'Downloads Template');
		
		$this->catlists = category::select("id","catname")->where('type','newsevents')->where('isactive','active')->orderBy('catname','ASC')->get();
	}
    public function index(Request $request) {
		$posts = Post::query();
		$search = $request->query('search');
		
		if( request('search') ) {
			$posts->where('pagename_en','LIKE','%'.request('search').'%');
		}
		$lists = $posts->orderBy('sortorder','ASC')->where('pagetype','pages')->paginate(50);
		return view('myadmin.cmspageshtml',['lists' =>$lists, 'search'=>$search,'totalrecords'=>'Pages : '.$lists->count().' Records found'] );
    }
    public function create() {
        return view('myadmin.createpagehtml',[
				'statusArrays'=>$this->statusArrays,
				'imageposition'=>$this->imageposition,
				'templates'=>$this->templates
			]
		);
    }
    public function store(Request $request) {
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
		$page_slug = $this->generateUniqueSlug($request->pagename_en);
		$post = new Post();
		$post->pagename_en = $request->pagename_en;
		$post->pagename_hi = $request->pagename_hi;
		$post->description_en = $request->description_en;
		$post->description_hi = $request->description_hi;
		$post->slug = $page_slug;
		$post->target_blank = $request->target_blank;
		$post->external_link = $request->external_link;


		if( !empty($request->file('you_tube')) ) {
			$video = $request->file('you_tube');
			$videoName = Auth()->id().'_'.time().'.'.$video->extension();
			$video->move(public_path('uploads/videos'),$videoName);
			$post->you_tube_link = $videoName;
		}
		
		$post->isactive = $request->isactive;
		$post->template = $request->template;
		$post->pagetype = $request->pagetype;
		//$post->pagebanner = $request->pagebanner;
		$post->imageposition = $request->imageposition;
		$post->meta_title = $request->meta_title;
		$post->meta_description = $request->meta_description;
		$post->meta_keyword = $request->meta_keyword;
		if( !empty($request->postdate)) {
			$post->postdate = convertdate($request->postdate);
		}
		if( !empty($request->regpostdate)) {
			$post->regpostdate = convertdate($request->regpostdate);
		}
		$featureimageName = '';
		$pagebannerName = '';
		if( !empty($request->file('feature_image')) ) {
			$featureimage = $request->file('feature_image');
			$featureimageName = Auth()->id().'_'.time().'.'.$featureimage->extension();
			$featureimage->move(public_path('uploads/images'),$featureimageName);
		}
		if( !empty($request->file('pagebanner')) ) {
			$pagebanner = $request->file('pagebanner');
			$pagebannerName = Auth()->id().'_'.time().'.'.$pagebanner->extension();
			$pagebanner->move(public_path('uploads/images'),$pagebannerName);
		}
		if( !empty($featureimageName) ) {
			$post->feature_image = $featureimageName;
		}
		if( !empty($pagebannerName) ) {
			$post->pagebanner = $pagebannerName;
		}
		$post->meta_keyword = $request->meta_keyword;
		$post->user_id = Auth()->id();
		$post->save();
		return redirect()->route($request->pagetype)->with('status', ' Content has been saved successfully');
    }
    public function edit($id) {
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
			return redirect()->route('pages')->with('status', 'Mentioned Id does not exist.');
		}  
    }
    public function update(Request $request, $id) {
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
			$post->pagename_en = $request->pagename_en;
			$post->pagename_hi = $request->pagename_hi;
			$post->description_en = $request->description_en;
			$post->description_hi = $request->description_hi;
			$post->target_blank = $request->target_blank;
			$post->external_link = $request->external_link;
			if( !empty($request->file('you_tube')) ) {
				$video = $request->file('you_tube');
				$videoName = Auth()->id().'_'.time().'.'.$video->extension();
				$video->move(public_path('uploads/videos'),$videoName);
				$post->you_tube_link = $videoName;

			}
	
			//$post->feature_image = $request->feature_image;
			$post->isactive = $request->isactive;
			$post->template = $request->template;
			$post->pagetype = $request->pagetype;
			//$post->pagebanner = $request->pagebanner;
			$post->imageposition = $request->imageposition;
			$post->meta_title = $request->meta_title;
			$post->meta_description = $request->meta_description;
			$post->meta_keyword = $request->meta_keyword;
			if( !empty($request->postdate)) {
				$post->postdate = convertdate($request->postdate);
			}
			if( !empty($request->regpostdate)) {
				$post->regpostdate = convertdate($request->regpostdate);
			}
			$featureimageName = '';
			$pagebannerName = '';
			$cifpdf = '';
			if( !empty($request->file('feature_image')) ) {
				$featureimage = $request->file('feature_image');
				$featureimageName = Auth()->id().'_'.time().'.'.$featureimage->extension();
				$featureimage->move(public_path('uploads/images'),$featureimageName);
			}
			if( !empty($request->file('pagebanner')) ) {
				$pagebanner = $request->file('pagebanner');
				$pagebannerName = Auth()->id().'_'.time().'.'.$pagebanner->extension();
				$pagebanner->move(public_path('uploads/images'),$pagebannerName);
			}
			if( !empty($request->file('cif_image')) ) {
				$cifimage = $request->file('cif_image');
				$cifpdf = Auth()->id().'_'.time().'.'.$cifimage->extension();
				$cifimage->move(public_path('uploads/images'),$cifpdf);
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
			$post->user_id = Auth()->id();
			$post->save();
			return redirect()->route($request->pagetype)->with('status', ' Content has been updated successfully');
		} else {
			return redirect()->route($request->pagetype)->with('status', 'Mentioned Id does not exist.');
		}
    }
	public function commponcmsstatus( Request $request) {
		$pageids = $request->post_id;
		$status_type = $request->status_type;
		Post::whereIn('id',$pageids)->update(['isactive' => $status_type]);
		return response()->json(['status'=>true]);
	}
	/*************************/
	public function newsevents(Request $request) {
		$search = $request->query('search');
		$catid = $request->query('catid');
		$query = Post::join('categories', 'categories.id', '=', 'posts.catid')
				->where('posts.pagetype','=','newsevents')
				->orderBy('posts.id','DESC');	
		if( request('search') ) {
			$query->where( 'pagename','LIKE','%'.request('search').'%');
		}	
		if( request('catid') ) {
			$query->where( 'catid','=',request('catid') );
		}
		$lists = $query->paginate(40,['posts.*', 'categories.catname']);
		
		return view('myadmin.newsevents.newseventshtml',['lists' =>$lists,'catlists'=>$this->catlists,  'catid'=>$catid, 'search'=>$search,'totalrecords'=>'News & events : '.$lists->count().' Records found'] );
    }
	public function createnewsevent() {
        return view('myadmin.newsevents.createnewseventhtml',[
				'statusArrays'=>$this->statusArrays,
				'catlists' => $this->catlists
			]
		);
    }
	public function editnewsevent($id) {
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
			return redirect()->route('newsevents')->with('status', 'Mentioned Id does not exist.');
		}  
    }
	public function deleterecords(Request $request) {
		$ids = $request->post_id;
        Post::whereIn('id',$ids)->delete();
		return response()->json(['status'=>true]);
    }
	
	private function generateUniqueSlug($title) {
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
public function removepages(Request $request) {
		$ids = $request->pageid;
		// dd($ids);
        Post::where('id',$ids)->delete();
		return redirect()->route('pages')->with('status', 'Page Remove Successfully');
    }
	////////////////////////////////////////////////////////////////////


	public function websitepagesUpdateOrder(Request $request)
	{

		$orders = $request->input('order');
		
		foreach ($orders as $order) {
			$id = $order['id'];
			$position = $order['position'];
	
			$list = Post::
				where('id', $id)
				->firstOrFail();
			$list->sortorder = $position;
			$list->save();
		
		}
	

		return response()->json(['status' => 'success']);
	} 

	public function storeconference(Request $request)
	{
		$this->validate($request, [
			'photo_file' => 'mimes:png,jpeg,jpg,gif|max:3000',
			'audio_file' => 'mimes:mpga,wav', // mpga = mp3
			'video_file' => 'mimes:mp4,ogv,webm'
		]);

		$next_nor_no = Topic::where('webmaster_id', '=', $webmasterId)->max('row_no');
		if ($next_nor_no < 1) {
			$next_nor_no = 1;
		} else {
			$next_nor_no++;
		}

		// Start of Upload Files
		$formFileName = "photo_file";
		$fileFinalName = "";
		if ($request->$formFileName != "") {
			$fileFinalName = time() . rand(1111,
					9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
			$path = $this->getUploadPath();
			$request->file($formFileName)->move($path, $fileFinalName);
		}

		$formFileName = "audio_file";
		$audioFileFinalName = "";
		if ($request->$formFileName != "") {
			$audioFileFinalName = time() . rand(1111,
					9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
			$path = $this->getUploadPath();
			$request->file($formFileName)->move($path, $audioFileFinalName);
		}

		$formFileName = "attach_file";
		$attachFileFinalName = "";
		if ($request->$formFileName != "") {
			$attachFileFinalName = time() . rand(1111,
					9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
			$path = $this->getUploadPath();
			$request->file($formFileName)->move($path, $attachFileFinalName);
		}

		if ($request->video_type == 3) {
			$videoFileFinalName = $request->embed_link;
		} elseif ($request->video_type == 2) {
			$videoFileFinalName = $request->vimeo_link;
		} elseif ($request->video_type == 1) {
			$videoFileFinalName = $request->youtube_link;
		} else {
			$formFileName = "video_file";
			$videoFileFinalName = "";
			if ($request->$formFileName != "") {
				$videoFileFinalName = time() . rand(1111,
						9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
				$path = $this->getUploadPath();
				$request->file($formFileName)->move($path, $videoFileFinalName);
			}

		}
		// End of Upload Files


		// create new topic
		$Topic = new Topic;

		// Save topic details
		$Topic->row_no = $next_nor_no;
		$Topic->title_ar = $request->title_ar;
		$Topic->title_en = $request->title_en;

		$Topic->details_ar = $request->details_ar;
		$Topic->details_en = $request->details_en;
		$Topic->date = $request->date;
		if (@$request->expire_date != "") {
			$Topic->expire_date = $request->expire_date;
		}
		if ($fileFinalName != "") {
			$Topic->photo_file = $fileFinalName;
		}
		if ($audioFileFinalName != "") {
			$Topic->audio_file = $audioFileFinalName;
		}
		if ($attachFileFinalName != "") {
			$Topic->attach_file = $attachFileFinalName;
		}
		if ($videoFileFinalName != "") {
			$Topic->video_file = $videoFileFinalName;
		}
		$Topic->icon = $request->icon;
		$Topic->video_type = $request->video_type;
		$Topic->webmaster_id = $webmasterId;
		$Topic->created_by = Auth::user()->id;
		$Topic->visits = 0;
		$Topic->status = 1;
		if ($webmasterId == 17){
		$Topic->speakers = serialize($request->speakers);
		
		}
		if ($webmasterId == 19){
		$Topic->r_unit = serialize($request->r_unit);
		
		}
		
		// Meta title
		$Topic->seo_title_ar = $request->title_ar;
		$Topic->seo_title_en = $request->title_en;

		// URL Slugs
		$slugs = Helper::URLSlug($request->title_ar, $request->title_en, "topic", 0);
		$Topic->seo_url_slug_ar = $slugs['slug_ar'];
		$Topic->seo_url_slug_en = $slugs['slug_en'];

		// Meta Description
		$Topic->seo_description_ar = mb_substr(strip_tags(stripslashes($request->details_ar)), 0, 165, 'UTF-8');
		$Topic->seo_description_en = mb_substr(strip_tags(stripslashes($request->details_en)), 0, 165, 'UTF-8');


		$Topic->save();

		if ($request->section_id != "" && $request->section_id != 0) {
			// Save categories
			foreach ($request->section_id as $category) {
				if ($category > 0) {
					$TopicCategory = new TopicCategory;
					$TopicCategory->topic_id = $Topic->id;
					$TopicCategory->section_id = $category;
					$TopicCategory->save();
				}
			}
		}
		
		
		
		  if ($request->scientist_id != "") {
			// Save scientist
			foreach ($request->scientist_id as $scientist) {
				if ($scientist > 0) {
					$TopicScientist = new TopicScientist;
					$TopicScientist->topic_id = $Topic->id;
					$TopicScientist->user_id = $scientist;
					$TopicScientist->save();
				}
			}
		}

		// Save additional Fields
		if (count($WebmasterSection->customFields) > 0) {
			foreach ($WebmasterSection->customFields as $customField) {
				$field_value_var = "customField_" . $customField->id;

				if ($request->$field_value_var != "") {
					if ($customField->type == 8 || $customField->type == 9 || $customField->type == 10) {
						// upload file
						if ($request->$field_value_var != "") {
							$uploadedFileFinalName = time() . rand(1111,
									9999) . '.' . $request->file($field_value_var)->getClientOriginalExtension();
							$path = $this->getUploadPath();
							$request->file($field_value_var)->move($path, $uploadedFileFinalName);
							$field_value = $uploadedFileFinalName;
						}
					} elseif ($customField->type == 7) {
						// if multi check
						$field_value = implode(", ", $request->$field_value_var);
					} else {
						$field_value = $request->$field_value_var;
					}
					$TopicField = new TopicField;
					$TopicField->topic_id = $Topic->id;
					$TopicField->field_id = $customField->id;
					$TopicField->field_value = $field_value;
					$TopicField->save();
				}

			}
		}


	}
	
}
