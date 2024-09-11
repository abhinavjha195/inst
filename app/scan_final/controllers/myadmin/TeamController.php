<?php

namespace App\Http\Controllers\myadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\myadmin\Coordinator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Section;
use App\Models\Userdetail;
use App\Models\Researchinterest;
use App\Models\Researchhighlight;
use App\Models\Researchgroup;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
//use File;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;


class TeamController extends Controller {


  /**
     * @var array<string, string>
     */
    public array $statusArrays;

    /**
     * @var Collection<int, Section>
     */
    public Collection $catlists;


      /**
     * @var string[]
     */
    public array $roles;


      /**
     * @var int
     */
    public int $GrantSectionId;


    public function __construct()
    {
        $this->statusArrays = ['' => 'Status', 'inactive' => 'inActive', 'active' => 'Active'];
        $this->roles = ['scientists'];
        $this->GrantSectionId = 12;
        $this->catlists = Section::where('isactive', 1)->get();
    }


   
	public function scientistimagesAdmin(Request $request): View|Factory|RedirectResponse {
		$token = $request->query('_token');
		if (!is_string($token)) {
			return Redirect::route('scientists')->with('error', 'Invalid token');
		}
		$decryptedToken = Crypt::decrypt($token);
		$userInfo = User::findOrFail($decryptedToken);
        $lists  = Researchinterest::where('userid', $decryptedToken)->where('type','relatedimages')->orderBy('id', 'DESC')->get();
        return view('myadmin.scientistsadmin.scientistimageshtml', [
            'lists' => $lists,
            'userInfo' => $userInfo,
            'heading' => 'Related Images'
        ]);
    }
	public function dropzoneImagesStoreAdmin(Request $request): JsonResponse {
        $imagelists = $request->file('file');
        $size = is_array($imagelists) ? count($imagelists) : 1;
        $params = array();
        
        if (is_array($imagelists)) {
            foreach ($imagelists as $key => $imagelist) {
                $this->processImage($imagelist, $key, $request);
            }
        } elseif ($imagelists instanceof UploadedFile) {
            $this->processImage($imagelists, 0, $request);
        }
        
        return Response::json(["status" => "success", "data" => 'Content has been updated successfully']);
    }

    private function processImage(UploadedFile $image, int $key, Request $request): void {
        $imageName = $key.'_'.uniqid().'.'.$image->getClientOriginalExtension();
        $image->move(public_path('userpics'), $imageName);
        $userDetailsInfo = new Researchinterest();
        $userDetailsInfo->type = $request->input('type');
        $userDetailsInfo->description = $imageName;
        $userDetailsInfo->userid = $request->input('userid');
        $userDetailsInfo->save();
    }

	public function destroy(Request $request, int $id): RedirectResponse {
		if($request->input('tag') == 'researchgroups') {
            $info  = Researchgroup::where('userid', $request->input('userid'))->where('id',$id)->first();
            
            /** @var Researchgroup $info */
            if ($info->interimage != "") {
                if (File::exists(public_path('userpics') . '/' . $info->interimage)) {
                    File::delete(public_path('userpics') . '/' . $info->interimage);
                }
            }
			Researchgroup::where('id',$id)->delete();
        } else if($request->input('tag') == 'researchinterest') {
            Researchinterest::where('id',$id)->delete();
        } else if($request->input('tag') == 'relatedimages') {
            $info  = Researchinterest::where('userid', $request->input('userid'))->where('id',$id)->where('type','relatedimages')->first();
          
          /** @var Researchinterest $info */
            if ($info->description != "") {
                if (File::exists(public_path('userpics') . '/' . $info->description)) {
                    File::delete(public_path('userpics') . '/' . $info->description);
                }
            }
			Researchinterest::where('id', $id)->delete();			
        }
        return Redirect::back()->with('status', ' Content has been removed successfully');
    }
	public function scientistresearchinterestAdmin(Request $request): View|Factory|RedirectResponse {
    $token = $request->query('_token');
    
    if (!is_string($token)) {
        return Redirect::route('scientists')->with('error', 'Invalid token');
    }

    try {
        $decryptedToken = Crypt::decrypt($token);
    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
        return Redirect::route('scientists')->with('error', 'Invalid token');
    }

    $userInfo = User::find($decryptedToken);
    
    if (empty($userInfo)) {
        return Redirect::route('scientists');
    }

    $lists  = Researchinterest::where('userid', $decryptedToken)->where('type','researchinterest')->orderBy('id', 'DESC')->paginate(100);
    return view('myadmin.scientistsadmin.researchinteresthtml', [
        'lists' => $lists,
        'userInfo' => $userInfo,
        'heading' => 'Research Interest'
    ]);
}
	public function createscientistresearchinterestAdmin(Request $request): View|Factory|RedirectResponse {
		$token = $request->query('_token');
		if (!is_string($token)) {
			return Redirect::route('scientists')->with('error', 'Invalid token');
		}
		$decryptedToken = Crypt::decrypt($token);
		$userInfo = User::findOrFail($decryptedToken);
		
        $info = [];
        if( !empty($request->input('tokenid')) ) {
            $info  = Researchinterest::where('userid', $decryptedToken)->where('id',$request->input('tokenid'))->first();
        }
		return view('myadmin.scientistsadmin.modals.create_researchinteresthtml', [
            'info' => $info,
            'token' => $decryptedToken,
            'heading' => 'Research Interest'
        ]);
    }
	public function scientistresearchhighlightsAdmin(Request $request): View|Factory|RedirectResponse {
		$token = $request->query('_token');
		if (!is_string($token)) {
			return Redirect::route('scientists')->with('error', 'Invalid token');
		}
		$decryptedToken = Crypt::decrypt($token);
		$userInfo = User::findOrFail($decryptedToken);
        $lists  = Researchinterest::where('userid', $decryptedToken)->where('type','researchhighlights')->orderBy('sortorder', 'ASC')->paginate(100);
        return view('myadmin.scientistsadmin.scientistresearchhighlightshtml', [
            'lists' => $lists,
            'userInfo' => $userInfo,
            'heading' => 'Research Highlights'
        ]);
    }
    public function createscientistresearchhighlightsAdmin(Request $request): View|Factory|RedirectResponse {
        $token = $request->query('_token');
		if (!is_string($token)) {
			return Redirect::route('scientists')->with('error', 'Invalid token');
		}
		$decryptedToken = Crypt::decrypt($token);
		$userInfo = User::findOrFail($decryptedToken);
		
		$info = [];
        if( !empty($request->input('tokenid')) ) {
            $info  = Researchinterest::where('userid', $decryptedToken)->where('id',$request->input('tokenid'))->first();
        }
		return view('myadmin.scientistsadmin.modals.create_scientistresearchhighlightshtml', [
            'info' => $info,
            'token' => $decryptedToken,
            'heading' => 'Research Highlights'
        ]);
    }
	public function scientistresearchgroupsAdmin(Request $request): View|Factory|RedirectResponse {
      
		$token = $request->query('_token');
		if (!is_string($token)) {
			return Redirect::route('scientists')->with('error', 'Invalid token');
		}
		$decryptedToken = Crypt::decrypt($token);
		$userInfo = User::findOrFail($decryptedToken);
        $sections = Section::where('isactive',1)->where('type','researchgroup')->get();
        $scholorlists = Section::where('isactive',1)->where('type','coregroupmembers')->get();
        $corembrid = $request->query('corembrid');
        $sectionid = $request->query('sectionid');
        $search = $request->query('search');
        $lists = Researchgroup::join('sections','sections.id','=','researchgroups.sectionid')->where('researchgroups.userid', $decryptedToken)->where('sections.type','researchgroup')->leftJoin('sections as corembrsection','corembrsection.id','=','researchgroups.corembrid')
        ->select(['sections.sectionname', 'researchgroups.*','corembrsection.sectionname as scholarname']);
        
        if ($request->filled('sectionid')) {
            $lists->where('researchgroups.sectionid', '=', $request->input('sectionid'));
        }
		if($request->filled('search')) {
			$lists->where('researchgroups.name','LIKE','%'.$request->input('search').'%');
		}
        if ($request->filled('corembrid')) {
            $lists->where('researchgroups.corembrid', '=', $request->input('corembrid'));
        }
        if(!empty($corembrid) && ($corembrid == '02')){
            $lists = Researchgroup::join('sections','sections.id','=','researchgroups.sectionid')->where('sections.type','researchgroup')->join('users','users.id','=','researchgroups.userid')->join('sections as corembrsection','corembrsection.id','=','researchgroups.corembrid')
            ->whereDate('researchgroups.enddate', '<=', now()->toDateString())->select(['corembrsection.sectionname', 'researchgroups.*','corembrsection.sectionname as scholarname','users.name as professorname']);
    
            }
        $lists = $lists->orderBy('id', 'DESC')->paginate(100);

        return view('myadmin.scientistsadmin.scientistresearchgroupshtml', [
            'lists' => $lists,
            'search' => $search,
            'sections' => $sections,
            'scholorlists' => $scholorlists,
            'sectionid' => $sectionid,
            'userInfo' => $userInfo,
            'corembrid' => $corembrid,
            'heading' => 'Research Groups'
        ]);
    }
    public function createscientistresearchgroupsAdmin(Request $request): View|Factory|RedirectResponse {
        $token = $request->query('_token');
		if (!is_string($token)) {
			return Redirect::route('scientists')->with('error', 'Invalid token');
		}
		$decryptedToken = Crypt::decrypt($token);
		$userInfo = User::findOrFail($decryptedToken);
		
		$info = [];
        if( !empty($request->input('tokenid')) ) {
            $info  = Researchgroup::where('userid', $decryptedToken)->where('id',$request->input('tokenid'))->first();
        }
        $sections = Section::where('isactive',1)->where('type','researchgroup')->get();
        $scholorlists = Section::where('isactive',1)->where('type','coregroupmembers')->get();
		return view('myadmin.scientistsadmin.modals.create_scientistresearchgroupshtml', [
            'sections' => $sections,
            'info' => $info,
            'token' => $decryptedToken,
            'scholorlists' => $scholorlists,
            'heading' => 'Research Groups'
        ]);
    }
	public function scientistpublicationsAdmin(Request $request): View|Factory|RedirectResponse {

		$token = $request->query('_token');
		if (!is_string($token)) {
			return Redirect::route('scientists')->with('error', 'Invalid token');
		}
		$decryptedToken = Crypt::decrypt($token);

        $yearlists = Researchgroup::whereNotIn('workingsince', [0, 1970])->orderBy('workingsince', 'desc')->select('workingsince')->groupBy('workingsince')->distinct()->get();
		$years = array();
		foreach ($yearlists as $yearlist) {
			$years[] = array(
				'year' =>  Carbon::parse($yearlist->workingsince)->format('Y')
			);
		}
		$years = array_unique($years, SORT_REGULAR);


		$userInfo = User::findOrFail($decryptedToken);
        $sections = Section::where('isactive',1)->where('type','researchpublications')->get();
        $lists = Researchinterest::join('sections','sections.id','=','researchinterests.sectionid')->where('userid', $decryptedToken)->where('researchinterests.type','researchpublications')->select(['sections.sectionname', 'researchinterests.*']);
        if($request->filled('search')) {
			$lists->where('researchinterests.title','LIKE','%'.$request->input('search').'%');
		}
        if($request->filled('sectionid')) {
			$lists->where('researchinterests.sectionid','=',$request->input('sectionid'));
		}
        if($request->filled('year')) {
            $lists->where('researchinterests.tenure','=',$request->input('year'));
        }
        $lists = $lists->orderBy('researchinterests.sortorder', 'ASC')->get();
       $year = $request->input('year');
       
        return view('myadmin.scientistsadmin.scientistpublicationshtml', [
            'lists' => $lists,
            'search' => $request->input('search'),
            'sectionid' => $request->input('sectionid'),
            'sections' => $sections,
            'year' => $year,
            'userInfo' => $userInfo,
            'yearlists' => $years,
            'heading' => 'Publications'
        ]);
    }
    public function createscientistpublicationsAdmin(Request $request): View|Factory|RedirectResponse {
        $token = $request->query('_token');
		if (!is_string($token)) {
			return Redirect::route('scientists')->with('error', 'Invalid token');
		}
		$decryptedToken = Crypt::decrypt($token);
		$userInfo = User::findOrFail($decryptedToken);
		
		$info = [];
        if( !empty($request->input('tokenid')) ) {
            $info  = Researchinterest::where('userid', $decryptedToken)->where('id',$request->input('tokenid'))->first();
        }
        $sections = Section::where('isactive',1)->where('type','researchpublications')->get();
		return view('myadmin.scientistsadmin.modals.create_scientistpublicationshtml', [
            'sections' => $sections,
            'info' => $info,
            'token' => $decryptedToken,
            'heading' => 'Publications'
        ]);
    }
	public function biodataresearchespawardsAdmin( Request $request ): View|Factory|RedirectResponse {
		$token = $request->query('_token');
		if (!is_string($token)) {
			return Redirect::route('scientists')->with('error', 'Invalid token');
		}
		$decryptedToken = Crypt::decrypt($token);
		$userInfo = User::findOrFail($decryptedToken);
		
        $sectionid = $request->input('sectionid');
        $lists = Researchinterest::join('sections','sections.id','=','researchinterests.sectionid')->where(['userid'=>$decryptedToken,'researchinterests.type'=>'researchbiodata','researchinterests.sectionid'=>$sectionid ])->select(['sections.sectionname', 'researchinterests.*']);
        if($request->filled('search')) {
			$lists->where('researchinterests.description','LIKE','%'.$request->input('search').'%');
		}
        $lists = $lists->orderBy('order', 'ASC')->paginate(100);
        return view('myadmin.scientistsadmin.researchbiodataawardshtml', [
            'lists' => $lists,
            'sectionid' => $sectionid,
            'userInfo' => $userInfo,
            'search' => $request->input('search'),
            'heading' => ($sectionid == 10 ? 'Research Experience' : 'Awards & Honours')
        ]);
    }
    ///////////////////////////////////////////////////////////////////////////////////////
	public function sortOrders(Request $request): JsonResponse {
		$orders = $request->input('order', []);
		foreach ($orders as $order) {
			if (!is_array($order)) continue;
			$id = $order['id'] ?? null;
			$position = $order['position'] ?? null;
			$userid = $order['userid'] ?? null;
			$sectionid = $order['sectionid'] ?? null;
			if ($id === null || $position === null || $userid === null || $sectionid === null) continue;
        // dd($id);
		// dd($catid);
	
			$list = Researchinterest::where('type', 'researchbiodata')
						   ->where('userid', $userid)
						   ->where('sectionid', $sectionid)
						   ->where('id', $id)
						   ->firstOrFail();
			$list->order = $position;
             /** @var Researchinterest $list */
			$list->save();
		}
		

        return Response::json(['status' => 'success']);
	}
    //////////////////////////////////////////////////////////////////////////////////////
	public function createbiodataresearchespawardsAdmin(Request $request): View|Factory|RedirectResponse {
        $token = $request->query('_token');
		if (!is_string($token)) {
			return Redirect::route('scientists')->with('error', 'Invalid token');
		}
		$decryptedToken = Crypt::decrypt($token);
		$userInfo = User::findOrFail($decryptedToken);
		
		$info = [];
        if( !empty($request->input('tokenid')) && !empty($request->input('sectionid')) ) {
            $info  = Researchinterest::where(['userid'=>$decryptedToken,'id'=>$request->input('tokenid'), 'sectionid'=>$request->input('sectionid') ])->first();
        }
		return view('myadmin.scientistsadmin.modals.create_researchbiodataawardshtml', [
            'info' => $info,
            'token' => $decryptedToken,
            'sectionid' => $request->input('sectionid'),
            'heading' => ($request->input('sectionid') == 10 ? 'Research Experience' : 'Awards & Honours')
        ]);
    }
	public function biodataresearchegrantAdmin( Request $request ): View|Factory|RedirectResponse {
		$token = $request->query('_token');
		if (!is_string($token)) {
			return Redirect::route('scientists')->with('error', 'Invalid token');
		}
		$decryptedToken = Crypt::decrypt($token);
		$userInfo = User::findOrFail($decryptedToken);
        $lists = Researchinterest::join('sections','sections.id','=','researchinterests.sectionid')->where(['userid'=>$decryptedToken,'researchinterests.type'=>'researchbiodata','researchinterests.sectionid'=>$this->GrantSectionId ])->select(['sections.sectionname', 'researchinterests.*']);
        if($request->filled('search')) {
			$lists->where('researchinterests.description','LIKE','%'.$request->input('search').'%');
		}
        $lists = $lists->orderBy('id', 'DESC')->paginate(50);
        return view('myadmin.scientistsadmin.biodataresearchegranthtml', [
            'lists' => $lists,
            'userInfo' => $userInfo,
            'search' => $request->input('search'),
            'heading' => 'Research Grants Secured'
        ]);
    }
    public function createbiodataresearchegrantAdmin(Request $request): View|Factory|RedirectResponse {
        $token = $request->query('_token');
		if (!is_string($token)) {
			return Redirect::route('scientists')->with('error', 'Invalid token');
		}
		$decryptedToken = Crypt::decrypt($token);
		$userInfo = User::findOrFail($decryptedToken);
		
		$info = [];
        if( !empty($request->input('tokenid')) ) {
            $info  = Researchinterest::where([
                'userid'=>$decryptedToken,
                'id'=>$request->input('tokenid'),
                'sectionid'=>$this->GrantSectionId,
                ])->first();
        }
		return view('myadmin.scientistsadmin.modals.create_biodataresearchegranthtml', [
            'info' => $info,
            'token' => $decryptedToken,
            'sectionid' => $this->GrantSectionId,
            'heading' => 'Research Grants Secured'
        ]);
    }
	public function savescientistresearchgroupAdmin(Request $request): JsonResponse
    {
        if (!empty($request->input('tokenid'))) {
            $findData = Researchgroup::find($request->input('tokenid'));
            if ($findData) {
                $findData->name = $request->input('name');
                $findData->email = $request->input('email');
                $findData->regno = $request->input('regno');
                $findData->presentaffiliation = $request->input('presentaffiliation');
                $findData->enddate = $request->input('enddate') ? convertdate($request->input('enddate')) : null;
                $findData->workingsince = $request->input('workingsince') ? convertdate($request->input('workingsince')) : null;
                $findData->corembrid = $request->input('corembrid');
                $findData->sectionid = $request->input('sectionid');
                $findData->userid = $request->input('userid');

                if ($request->hasFile('interimage')) {
                    $interimage = $request->file('interimage');
                    if ($interimage instanceof UploadedFile) {
                        $interimageName = $request->input('userid') . '_' . time() . '.' . $interimage->getClientOriginalExtension();
                        $interimage->move(public_path('userpics'), $interimageName);
                        $findData->interimage = $interimageName;
                    }
                }

                $findData->save();
                return Response::json(['status' => true, 'message' => 'Content has been updated successfully'], 200);
            } else {
                return Response::json(['status' => true, 'message' => 'Issue with update, refresh the page and try again'], 200);
            }
        } else {
            $userDetailsInfo = new Researchgroup();
            $userDetailsInfo->name = $request->input('name');
            $userDetailsInfo->email = $request->input('email');
            $userDetailsInfo->regno = $request->input('regno');
            $userDetailsInfo->presentaffiliation = $request->input('presentaffiliation');
            $userDetailsInfo->userid = $request->input('userid');
            $userDetailsInfo->workingsince = $request->input('workingsince') ? convertdate($request->input('workingsince')) : null;
            $userDetailsInfo->enddate = $request->input('enddate') ? convertdate($request->input('enddate')) : null;
            $userDetailsInfo->corembrid = $request->input('corembrid');
            $userDetailsInfo->sectionid = $request->input('sectionid');

            if ($request->hasFile('interimage')) {
                $interimage = $request->file('interimage');
                if ($interimage instanceof UploadedFile) {
                    $interimageName = $request->input('userid') . '_' . time() . '.' . $interimage->getClientOriginalExtension();
                    $interimage->move(public_path('userpics'), $interimageName);
                    $userDetailsInfo->interimage = $interimageName;
                }
            }

            $userDetailsInfo->save();
            return Response::json(['status' => true, 'message' => 'Content has been saved successfully'], 200);
        }
    }

    public function saveOthercientistsprofileAdmin(Request $request): JsonResponse
    {
        if (!empty($request->input('tokenid'))) {
            $findData = Researchinterest::find($request->input('tokenid'));
            if ($findData) {
                $findData->fill($request->only([
                    'title', 'description', 'type', 'pi', 'copi', 'isactive', 'amount',
                    'tenure', 'agency', 'volumes', 'userid', 'journalname',
                    'journalconference', 'bookpublisher', 'sectionid'
                ]));

                $findData->enddate = $request->input('enddate') ? convertdate($request->input('enddate')) : null;
                
                if ($request->filled('postdate')) {
                    $findData->postdate = convertdate($request->input('postdate'));
                }
                
                $findData->save();
                return Response::json(['status' => true, 'message' => 'Content has been updated successfully'], 200);
            } else {
                return Response::json(['status' => true, 'message' => 'Issue with update, refresh the page and try again'], 200);
            }
        } else {

            $getlast = Researchinterest::where('type', $request->input('type'))
                                       ->where('userid', $request->input('userid'))
                                       ->orderBy('sortorder', 'DESC')
                                       ->first();
                                        /** @var Researchinterest $getlast */
                                        //@phpstan-ignore-next-line
            $sortorder = $getlast ? ($getlast->sortorder + 1) : 1;

            $userDetailsInfo = new Researchinterest();
            $userDetailsInfo->fill($request->only([
                'title', 'description', 'type', 'pi', 'copi', 'isactive', 'journalname',
                'journalconference', 'bookpublisher', 'amount', 'tenure', 'agency',
                'volumes', 'userid', 'sectionid'
            ]));

            $userDetailsInfo->sortorder = $sortorder;
            $userDetailsInfo->enddate = $request->input('enddate') ? convertdate($request->input('enddate')) : null;
            $userDetailsInfo->save();
        }
        return Response::json(['status' => true, 'message' => 'Content has been saved successfully'], 200);
    }

    public function index(Request $request): View|Factory
    {
        $search = $request->query('search');
        $query = User::where('roles','scientists')
                ->orderBy('id','DESC');	
        if($request->filled('search')) {
            $query->where( 'name','LIKE','%'.$request->input('search').'%');
        }	
        $lists = $query->paginate(40);
        return view('myadmin.teams.listhtml', [
            'lists' => $lists,
            'search' => $search,
            'totalrecords' => 'Core member : '.$lists->total().' Records found',
            'catlists' => $this->catlists,
            'catid' => $request->input('catid')
        ]);
    }

    public function create(): View|Factory
    {
        return view('myadmin.teams.createhtml', [
            'statusArrays' => $this->statusArrays,
            'catlists' => $this->catlists
        ]);
    }

    public function edit(Request $request, int $id): View|Factory|RedirectResponse
    {
        $teams = Coordinator::where('id',$id)->first();
        if($teams) {
            return view('myadmin.teams.edithtml', [
                'statusArrays' => $this->statusArrays,
                'catlists' => $this->catlists,
                'info' => $teams
            ]);
        } else {
            return Redirect::route('teams')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function scientist_researchhighlights_UpdateOrder(Request $request): JsonResponse
    {

         /** 
         * @var array<array{id: int, position: int, type: string}> $orders
         */
        $orders = $request->input('order', []);
        foreach ($orders as $order) {
            
            $id = $order['id'];
            $position = $order['position'];
            $type = $order['type'];
          
          
          



             // Retrieve a single Post model instance
            /** @var \App\Models\Researchinterest|null $list */
            $list =   Researchinterest::where('id', $id)->where('type',$type)
                ->firstOrFail();
				if ($list !== null) {
					// Ensure $list is not null before accessing properties
					$list->sortorder = $position;
					$list->save();
				}
        }
        return Response::json(['status' => 'success']);
    }

    public function scientist_researchpublications_UpdateOrder(Request $request): JsonResponse
    {

         /** 
         * @var array<array{id: int, position: int, type: string}> $orders
         */
        $orders = $request->input('order', []);
        foreach ($orders as $order) {
             
            $id = $order['id'];
            $position = $order['position'];
            $type = $order['type'];
            
          
         


 // Retrieve a single Post model instance
            /** @var \App\Models\Researchinterest|null $list */
           $list =   Researchinterest::where('id', $id)->where('researchinterests.type','researchpublications')
                ->firstOrFail();
				if ($list !== null) {
					// Ensure $list is not null before accessing properties
					$list->sortorder = $position;
					$list->save();
				}

            
        }
        return Response::json(['status' => 'success']);
    }

    public function commonstatus_of_research_publications(Request $request): JsonResponse
    {
        $pageids = $request->input('post_id');
        $status_type = $request->input('status_type');
        if($status_type =='active')
        {
            $status_type = 1;
        }
        else{
            $status_type = 0;
        }
      foreach($pageids as $pageid){
      $update =  Researchinterest::where('id',$pageid)->update(['isactive' => $status_type]);
      }

        return Response::json(['status'=>true]);
    }
}
