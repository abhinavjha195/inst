<?php

namespace App\Http\Controllers\myadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Section;
use App\Models\Userdetail;
use App\Models\Researchinterest;
use App\Models\Researchhighlight;
use App\Models\Researchgroup;
use Illuminate\Support\Facades\Hash;
use File;

class ScientistController extends Controller {
    public function __Construct() {
		$this->statusArrays = array(''=>'Status', '0'=>'inActive','1'=>'Active');
        $this->roles = 'scientists';
	}
    
    public function index(Request $request)  {
        if( getcurrentUserRole() != 'users') {
			return redirect()->route('singleprofile');
		}
        $search = $request->query('search');
        $userlists = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.*', 'userdetails.profilepic'])->where('users.roles',$this->roles)
				->orderBy('id','DESC');	
		if( request('search') ) {
			$userlists->where( 'users.name','LIKE','%'.request('search').'%');
		}	
		$lists = $userlists->paginate(40);
        return view('myadmin.scientists.listhtml',['lists' => $lists,'search'=> $search,'totalrecords'=>'Scientists : '.$lists->count().' Records found'] );
    }
    public function create(){
        if( getcurrentUserRole() != 'users') {
			return redirect()->route('singleprofile');
		}
        $sections = Section::where('isactive',1)->where('type','scientists')->get();
        return view('myadmin.scientists.createhtml')
        ->with('statusArrays',$this->statusArrays)
        ->with('sections',$sections)
        ->with('heading','Add new scientist');
    }
    public function store(Request $request) {
		$validator = $request->validate(
			[
                'sirname' => 'required|max:200',
                'name' => 'required|max:200',
                'sectionid' => 'required',
                'email' => 'required|email|unique:users',
                'profilepic' => 'mimes:png,jpeg,jpg,gif|max:3000',
				'isactive' => 'required',
                'password' => 'required|confirmed|min:6','regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
			], 
			[
                'sirname.required' => 'The sirname is required',
                'sectionid.required' => 'The Section is required',
                'name.required' => 'The name is required',
                'email.required' => 'The email is required',
                'profilepic.required' => 'The profilepic is required',
				'isactive.required' => 'The status is required',
				'password.required' => 'The password is required',
			]
		);
       
		$user = new User();
        $user->sirname = $request->sirname;
        $user->name = $request->name;
        $user->password = Hash::make($request->password);
        $user->roles = $this->roles;
        $user->email = $request->email;
        $user->isactive = $request->isactive;
        $user->save();
        $profilepicName = '';
        if( !empty($request->file('profilepic')) ) {
            $profilepic = $request->file('profilepic');
            $profilepicName = $user->id.'_'.time().'.'.$profilepic->extension();
            $profilepic->move(public_path('userpics'),$profilepicName);
        }
        Userdetail::create([
            'userid'=> $user->id,
            'profilepic'=>$profilepicName,
            'aboutme'=> $request->aboutme,
            'sectionid'=> $request->sectionid,
        ]);
		return redirect()->route('scientists')->with('status', ' Content has been saved successfully');
    }
    public function show($id) {

    }
    public function edit($userid) {
        if( getcurrentUserRole() != 'users') {
			return redirect()->route('singleprofile');
		}
        $sections = Section::where('isactive',1)->where('type','scientists')->get();
        $userinfo = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.*', 'userdetails.profilepic','userdetails.aboutme','userdetails.sectionid'])->where('users.roles',$this->roles)->where('users.id', $userid)->first();	
		if($userinfo) {
			return view('myadmin.scientists.edithtml')
				->with('statusArrays',$this->statusArrays)
                ->with('heading','Edit scientist')
                ->with('sections',$sections)
				->with('userinfo',$userinfo);
		} else {
			return redirect()->route('scientists')->with('status', 'Mentioned Id does not exist.');
		}
    }
    public function update(Request $request, $userid) {
        $validator = $request->validate(
			[
                'sirname' => 'required|max:200',
                'name' => 'required|max:200',
                'sectionid' => 'required',
                'email' => 'max:100|required|unique:users,email,'.$userid,
                'profilepic' => 'mimes:png,jpeg,jpg,gif|max:3000',
				'isactive' => 'required'
			], 
			[
                'sectionid.required' => 'The Section is required',
                'sirname.required' => 'The sirname is required',
                'name.required' => 'The name is required',
                'email.required' => 'The email is required',
                'email.unique' => 'This email is already in use please try another',
                'profilepic.required' => 'The profilepic is required',
				'isactive.required' => 'The status is required'
			]
		);
        $updatePassword = false;
        if(  $request->password != '' || $request->password_confirmation != '') {
            if( $request->password == $request->password_confirmation ) {
                $updatePassword = true;
            }else {
                return redirect()->route('scientists')->with('status', ' Password & confirm password did not matched');
            }
        }
        
		$userInfo = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.*', 'userdetails.profilepic','userdetails.aboutme'])->where('users.roles',$this->roles)->where('users.id', $userid)->first();
    
        if( $userInfo) {
            $userInfo->name = $request->name;
            $userInfo->sirname = $request->sirname;
            if($updatePassword) {
                $userInfo->password = Hash::make($request->password_confirmation);
            }
            $userInfo->roles = $this->roles;
            $userInfo->email = $request->email;
            $userInfo->isactive = $request->isactive;
            $userInfo->save();
            $profilepicName = '';
            if( !empty($request->file('profilepic')) ) {
                $profilepic = $request->file('profilepic');
                $profilepicName = $userid.'_'.time().'.'.$profilepic->extension();
                $profilepic->move(public_path('userpics'),$profilepicName);
            }
            if( !empty($profilepicName) ) {
                UserDetail::where('userid', $userid)
                ->update([
                    'profilepic'=>$profilepicName,
                    'aboutme'=> $request->aboutme,
                    'sectionid'=> $request->sectionid
                ]);
            } else {
                UserDetail::where('userid', $userid)
                ->update([
                    'aboutme'=> $request->aboutme,
                    'sectionid'=> $request->sectionid
                ]);
            }
            return redirect()->route('scientists')->with('status', ' Profile has been updated successfully');
        } else{
            return redirect()->route('scientists')->with('status', ' Mentioned Id does not exist');
        }
    }
    public function destroy($id) {
        
        $info  = Researchinterest::where('userid', Auth()->user()->id)->where('id',$id)->where('type','relatedimages')->first();
      
        if ($info->description != "") {
            File::delete(public_path('userpics') .'/'. $info->description);
            Researchinterest::where('id', $id)->delete();
        } 
        return redirect()->route('scientistimages')->with('status', ' Content has been updated successfully');
    }
    public function scientiststatus( Request $request) {
        
		$userids = $request->post_id;
		$status_type = $request->status_type;
		User::whereIn('id',$userids)->update(['isactive' => $status_type]);
		return response()->json(['status'=>true]);
	}

    /********Profile************/
    public function singleprofile(){
        $userinfo  = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.id','users.name','users.email','users.sirname', 'userdetails.*'])->where('users.roles',$this->roles)->where('users.id', Auth()->user()->id)->first();
        return view('myadmin.scientists.singleprofilehtml')
            ->with('userinfo',$userinfo )
            ->with('heading','Profile');
    }
    public function scientistimages(){
        $lists  = Researchinterest::where('userid', Auth()->user()->id)->where('type','relatedimages')->orderBy('id', 'DESC')->get();
        return view('myadmin.scientists.scientistimageshtml')
        ->with('lists',$lists )
        ->with('heading','Related Images');
    }
    public function scientistresearchinterest(){
        $lists  = Researchinterest::where('userid', Auth()->user()->id)->where('type','researchinterest')->orderBy('id', 'DESC')->paginate(50);
        return view('myadmin.scientists.researchinteresthtml')
        ->with('lists',$lists )
        ->with('heading','Research Interest');
    }
    public function createscientistresearchinterest(Request $request) {
        $info = array();
        if( !empty($request->tokenid) ) {
            $info  = Researchinterest::where('userid', Auth()->user()->id)->where('id',$request->tokenid)->first();
        }
		return view('myadmin.scientists.modals.create_researchinteresthtml')
        ->with('info',$info)
        ->with('heading','Research Interest');
    }
    public function scientistresearchhighlights(){
        $lists  = Researchinterest::where('userid', Auth()->user()->id)->where('type','researchhighlights')->orderBy('id', 'DESC')->paginate(50);
        return view('myadmin.scientists.scientistresearchhighlightshtml')
        ->with('lists',$lists )
        ->with('heading','Research Highlights');
    }
    public function createscientistresearchhighlights(Request $request) {
        $info = array();
        if( !empty($request->tokenid) ) {
            $info  = Researchinterest::where('userid', Auth()->user()->id)->where('id',$request->tokenid)->first();
        }
		return view('myadmin.scientists.modals.create_scientistresearchhighlightshtml')
        ->with('info',$info)
        ->with('heading','Research Highlights');
    }
    public function scientistresearchgroups(Request $request){
        $sections = Section::where('isactive',1)->where('type','researchgroup')->get();
        $scholorlists = Section::where('isactive',1)->where('type','coregroupmembers')->get();
        $corembrid = $request->query('corembrid');
        $sectionid = $request->query('sectionid');
        $lists = Researchgroup::join('sections','sections.id','=','researchgroups.sectionid')->where('researchgroups.userid', Auth()->user()->id)->where('sections.type','researchgroup')->leftJoin('sections as corembrsection','corembrsection.id','=','researchgroups.corembrid')
        ->select(['sections.sectionname', 'researchgroups.*','corembrsection.sectionname as scholarname']);
        
        if (request('sectionid')) {
            $lists->where('researchgroups.sectionid', '=', request('sectionid') );
        }
        if (request('corembrid')) {
            $lists->where('researchgroups.corembrid', '=', request('corembrid') );
        }
        
        $lists = $lists->orderBy('id', 'DESC')->paginate(50);

        return view('myadmin.scientists.scientistresearchgroupshtml')
        ->with('lists',$lists )
        ->with('sections',$sections )
        ->with('scholorlists',$scholorlists )
        ->with('sectionid',$sectionid )
        ->with('corembrid',$corembrid )
        ->with('heading','Research Groups');
    }
    public function createscientistresearchgroups(Request $request) {
        $info = array();
        if( !empty($request->tokenid) ) {
            $info  = Researchgroup::where('userid', Auth()->user()->id)->where('id',$request->tokenid)->first();
        }
        $sections = Section::where('isactive',1)->where('type','researchgroup')->get();
        $scholorlists = Section::where('isactive',1)->where('type','coregroupmembers')->get();
		return view('myadmin.scientists.modals.create_scientistresearchgroupshtml')
        ->with('sections',$sections)
        ->with('info',$info)
        ->with('scholorlists',$scholorlists)
        ->with('heading','Research Groups');
    }
    public function scientistpublications(){
        $sections = Section::where('isactive',1)->where('type','researchpublications')->get();
        $lists = Researchinterest::join('sections','sections.id','=','researchinterests.sectionid')->where('userid', Auth()->user()->id)->where('researchinterests.type','researchpublications')
        ->select(['sections.sectionname', 'researchinterests.*'])
        ->orderBy('id', 'DESC')->paginate(50);
        return view('myadmin.scientists.scientistpublicationshtml')
        ->with('lists',$lists )
        ->with('sections',$sections )
        ->with('heading','Publications');
    }
    public function createscientistpublications(Request $request) {
        $info = array();
        if( !empty($request->tokenid) ) {
            $info  = Researchinterest::where('userid', Auth()->user()->id)->where('id',$request->tokenid)->first();
        }
        $sections = Section::where('isactive',1)->where('type','researchpublications')->get();
		return view('myadmin.scientists.modals.create_scientistpublicationshtml')
        ->with('sections',$sections)
        ->with('info',$info)
        ->with('heading','Publications');
    }
    public function scientistbiodata(){
        $sections = Section::where('isactive',1)->where('type','researchbiodata')->get();
        $lists = Researchinterest::join('sections','sections.id','=','researchinterests.sectionid')->where('userid', Auth()->user()->id)->where('researchinterests.type','researchbiodata')->select(['sections.sectionname', 'researchinterests.*'])
        ->orderBy('id', 'DESC')->paginate(50);
        return view('myadmin.scientists.researchbiodatahtml')
        ->with('lists',$lists )
        ->with('sections',$sections )
        ->with('heading','Bio Data');
    }
    public function createscientistbiodata(Request $request) {
        $info = array();
        if( !empty($request->tokenid) ) {
            $info  = Researchinterest::where('userid', Auth()->user()->id)->where('id',$request->tokenid)->first();
        }
        $sections = Section::where('isactive',1)->where('type','researchbiodata')->get();
		return view('myadmin.scientists.modals.create_researchbiodatahtml')
        ->with('info',$info)
        ->with('sections',$sections)
        ->with('heading','Bio Data');
    }
    public function updatescientistsprofile(Request $request, $userid) {
        $validator = $request->validate(
			[
                'name' => 'required|max:200',
                'profilepic' => 'mimes:png,jpeg,jpg,gif|max:3000'
			], 
			[
                'name.required' => 'The name is required',
                'profilepic.required' => 'The profilepic is required',
			]
		);
        $userInfo  = User::find(Auth()->user()->id);
        $httpredirect = $request->httpredirect;
        if( $userInfo) {
            $userInfo->sirname = $request->sirname;
            $userInfo->name = $request->name;
            $userInfo->save();
            $profilepicName = '';
            if( !empty($request->file('profilepic')) ) {
                $profilepic = $request->file('profilepic');
                $profilepicName = $userid.'_'.time().'.'.$profilepic->extension();
                $profilepic->move(public_path('userpics'),$profilepicName);
            }
            $userDetailsInfo = UserDetail::find($userid);
            $userDetailsInfo->aboutme = $request->aboutme;
            $userDetailsInfo->designation = $request->designation;
            $userDetailsInfo->personalgroupinfo = $request->personalgroupinfo;
            $userDetailsInfo->googlelink = $request->googlelink;
            if( !empty($profilepicName) ) {
                $userDetailsInfo->profilepic = $profilepicName;
            }
            $userDetailsInfo->save();

            return redirect()->route($httpredirect)->with('status', ' Basic profile has been updated successfully');
        } else{
            return redirect()->route($httpredirect)->with('status', ' Mentioned Id does not exist');
        }
    }
    public function deleteextrarecords(Request $request) {
		$ids = $request->post_id;
		if($request->tag == 'researchgroups') {
            Researchgroup::whereIn('id',$ids)->delete();
        } else {
            Researchinterest::whereIn('id',$ids)->delete();
        }
		return response()->json(['status'=>true]);
    }
    public function saveOthercientistsprofile(Request $request) {
        if( !empty($request->tokenid) ) {
            $findData = Researchinterest::find($request->tokenid);
            if( $findData ) {
                $findData->title = $request->title;
                $findData->description = $request->description;
                $findData->type = $request->type;
                $findData->sectionid = $request->sectionid;
                $findData->save();
                if( !empty($request->postdate)) {
                    $findData->postdate = convertdate($request->postdate);
                }
                $findData->save();
                return response()->json(array('status'=>true,'message'=> 'Content has been updated successfully'), 200);
            } else {
                return response()->json(array('status'=>true,'message'=> 'Issue with update, refresh the page and try again'), 200);
            }
        } else {
            $userDetailsInfo = new Researchinterest();
            $userDetailsInfo->title = $request->title;
            $userDetailsInfo->description = $request->description;
            $userDetailsInfo->type = $request->type;
            $userDetailsInfo->sectionid = $request->sectionid;
            $userDetailsInfo->userid = Auth()->user()->id;
            $userDetailsInfo->save();
        }
        return response()->json(array('status'=>true,'message'=> 'Content has been saved successfully'), 200);
        //return redirect()->route($request->httpredirect)->with('status', ' Content has been updated successfully');
    }
    public function dropzoneImagesStore(Request $request){

        $imagelists = $request->file('file');
        $size = sizeof($imagelists);
        $params = array();
        foreach( $imagelists as $key=>$imagelist ) {
            $imageName = $key.'_'.uniqid().'.'.$imagelist->extension();
            $imagelist->move(public_path('userpics'),$imageName);
            $userDetailsInfo = new Researchinterest();
            $userDetailsInfo->type = $request->type;
            $userDetailsInfo->description = $imageName;
            $userDetailsInfo->userid = Auth()->user()->id;
            $userDetailsInfo->save();
        }
        return response()->json(["status" => "success", "data" => 'Content has been updated successfully']);
    }
    public function savescientistresearchgroup(Request $request) {
        if( !empty($request->tokenid) ) {
            $findData = Researchgroup::find($request->tokenid);
            if( $findData ) {
                $findData->name = $request->name;
                $findData->email = $request->email;
                $findData->regno = $request->regno;
                if( !empty($request->enddate)) {
                    $findData->enddate = convertdate($request->enddate);
                }
                if( !empty($request->workingsince)) {
                    $findData->workingsince = convertdate($request->workingsince);
                }
                $findData->corembrid = $request->corembrid;
                $findData->sectionid = $request->sectionid;
                $interimageName = '';
                if( !empty($request->file('interimage')) ) {
                    $interimage = $request->file('interimage');
                    $interimageName = Auth()->user()->id.'_'.time().'.'.$interimage->extension();
                    $interimage->move(public_path('userpics'),$interimageName);
                }
                if( !empty($interimageName) ) {
                    $findData->interimage = $interimageName;
                }
                $findData->save();
                return response()->json(array('status'=>true,'message'=> 'Content has been updated successfully'), 200);
                
            } else {
                return response()->json(array('status'=>true,'message'=> 'Issue with update, refresh the page and try again'), 200);
            }
        } else {
            $userDetailsInfo = new Researchgroup();
            $userDetailsInfo->name = $request->name;
            $userDetailsInfo->email = $request->email;
            $userDetailsInfo->regno = $request->regno;
            if( !empty($request->workingsince)) {
                $userDetailsInfo->workingsince = convertdate($request->workingsince);
            }
            if( !empty($request->enddate)) {
                $userDetailsInfo->enddate = convertdate($request->enddate);
            }
            $userDetailsInfo->corembrid = $request->corembrid;
            $userDetailsInfo->sectionid = $request->sectionid;
            
            $interimageName = '';
            if( !empty($request->file('interimage')) ) {
                $interimage = $request->file('interimage');
                $interimageName = Auth()->user()->id.'_'.time().'.'.$interimage->extension();
                $interimage->move(public_path('userpics'),$interimageName);
            }
            $userDetailsInfo->userid = Auth()->user()->id;
            if( !empty($interimageName) ) {
                $userDetailsInfo->interimage = $interimageName;
            }
            $userDetailsInfo->save();
            return response()->json(array('status'=>true,'message'=> 'Content has been saved successfully'), 200);
        }
        
    }

    public function savescientistresearchgroupByAdmin(Request $request) {
        if( !empty($request->tokenid) ) {
            $findData = Researchgroup::find($request->tokenid);
            if( $findData ) {
                $findData->name = $request->name;
                $findData->email = $request->email;
                $findData->personalemail	 = $request->personalemail;
                $findData->regno = $request->regno;
                $findData->isactive = $request->isactive;
                $findData->userid = $request->userid;
                dd($findData);
                if( !empty($request->enddate)) {
                    $findData->enddate = convertdate($request->enddate);
                }
                if( !empty($request->workingsince)) {
                    $findData->workingsince = convertdate($request->workingsince);
                }
                $findData->corembrid = $request->corembrid;
                $findData->sectionid = $request->sectionid;
                $interimageName = '';
                if( !empty($request->file('interimage')) ) {
                    $interimage = $request->file('interimage');
                    $interimageName = $request->userid.'_'.time().'.'.$interimage->extension();
                    $interimage->move(public_path('userpics'),$interimageName);
                }
                if( !empty($interimageName) ) {
                    $findData->interimage = $interimageName;
                }
                $findData->save();
                return redirect()->route('students')->with('status', ' Content has been saved successfully');
                
            } else {
                return response()->json(array('status'=>true,'message'=> 'Issue with update, refresh the page and try again'), 200);
            }
        } else {
            $userDetailsInfo = new Researchgroup();
            $userDetailsInfo->name = $request->name;
            $userDetailsInfo->email = $request->email;
            $userDetailsInfo->regno = $request->regno;
            $userDetailsInfo->personalemail	 = $request->personalemail;
            dd($findData);
            if( !empty($request->workingsince)) {
                $userDetailsInfo->workingsince = convertdate($request->workingsince);
            }
            if( !empty($request->enddate)) {
                $userDetailsInfo->enddate = convertdate($request->enddate);
            }
            $userDetailsInfo->corembrid = $request->corembrid;
            $userDetailsInfo->sectionid = $request->sectionid;
            
            $interimageName = '';
            if( !empty($request->file('interimage')) ) {
                $interimage = $request->file('interimage');
                $interimageName = $request->userid.'_'.time().'.'.$interimage->extension();
                $interimage->move(public_path('userpics'),$interimageName);
            }
            $userDetailsInfo->userid = $request->userid;
            if( !empty($interimageName) ) {
                $userDetailsInfo->interimage = $interimageName;
            }
            $userDetailsInfo->save();
            return redirect()->route('students')->with('status', ' Content has been saved successfully');
        }
        
    }
    
}