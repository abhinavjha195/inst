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
use Illuminate\Support\Facades\Crypt;
use File;

class ScientistController extends Controller
{
    public function __Construct()
    {
        $this->statusArrays = array('' => 'Status', '0' => 'inActive', '1' => 'Active');
        $this->roles = 'scientists';
        $this->GrantSectionId = 12;
    }

    public function index(Request $request)
    {
        if (getcurrentUserRole() != 'users') {
            return redirect()->route('singleprofile');
        }
        $search = $request->query('search');
        $userlists = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.*', 'userdetails.profilepic', 'userdetails.designation'])->where('users.roles', $this->roles)
            ->orderBy('id', 'DESC');
        if (request('search')) {
            $userlists->where('users.name', 'LIKE', '%' . request('search') . '%');
        }
        $lists = $userlists->paginate(40);
        return view('myadmin.scientists.listhtml', ['lists' => $lists, 'search' => $search, 'totalrecords' => 'Scientists : ' . $lists->count() . ' Records found']);
    }
    public function create()
    {
        if (getcurrentUserRole() != 'users') {
            return redirect()->route('singleprofile');
        }
        $sections = Section::where('isactive', 1)->where('type', 'scientists')->get();
        return view('myadmin.scientists.createhtml')
            ->with('statusArrays', $this->statusArrays)
            ->with('sections', $sections)
            ->with('heading', 'Add new scientist');
    }
    public function sientistchangepassword()
    {
        $sections = Section::where('isactive', 1)->where('type', 'scientists')->get();
        return view('myadmin.scientists.changepasswordhtml')
            ->with('heading', 'Change Password');
    }
    public function updatesientistchangepassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);
        if (!Hash::check($request->old_password, auth()->user()->password)) {
            return back()->with("error", "Old Password Doesn't match!");
        }
        User::whereId(auth()->user()->id)->update([
            'password' => Hash::make($request->new_password),
            'ispasswordchange' => 1,
        ]);
        return back()->with("status", "Password changed successfully!");
    }
    public function store(Request $request)
    {
        $validator = $request->validate(
            [
                'sirname' => 'required|max:200',
                'name' => 'required|max:200',
                'sectionid' => 'required',
                'email' => 'required|email|unique:users',
                'profilepic' => 'mimes:png,jpeg,jpg,gif|max:3000',
                'isactive' => 'required',
                'password' => 'required|confirmed|min:6', 'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
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
        if (!empty($request->file('profilepic'))) {
            $profilepic = $request->file('profilepic');
            $profilepicName = $user->id . '_' . time() . '.' . $profilepic->extension();
            $profilepic->move(public_path('userpics'), $profilepicName);
        }
        Userdetail::create([
            'userid' => $user->id,
			'sectionid' => $request->sectionid,
			'designation' => $request->designation,
            'profilepic' => $profilepicName,
            'aboutme' => $request->aboutme
        ]);
        return redirect()->route('scientists')->with('status', ' scientist has been created successfully');
    }
    public function show($id)
    {
    }
    public function edit($userid)
    {
        if (getcurrentUserRole() != 'users') {
            return redirect()->route('singleprofile');
        }
        $sections = Section::where('isactive', 1)->where('type', 'scientists')->get();
        $userinfo = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.*', 'userdetails.profilepic', 'userdetails.aboutme', 'userdetails.sectionid', 'userdetails.designation', 'userdetails.googlelink', 'userdetails.personalgroupinfo'])->where('users.roles', $this->roles)->where('users.id', $userid)->first();
        if ($userinfo) {
            return view('myadmin.scientists.edithtml')
                ->with('statusArrays', $this->statusArrays)
                ->with('heading', 'Edit scientist')
                ->with('sections', $sections)
                ->with('userinfo', $userinfo);
        } else {
            return redirect()->route('scientists')->with('status', 'Mentioned Id does not exist.');
        }
    }
    public function update(Request $request, $userid)
    {
        $validator = $request->validate(
            [
                'sirname' => 'required|max:200',
                'name' => 'required|max:200',
                'designation' => 'required',
                'sectionid' => 'required',
                'email' => 'max:100|required|unique:users,email,' . $userid,
                'profilepic' => 'mimes:png,jpeg,jpg,gif|max:3000',
                'isactive' => 'required'
            ],
            [
                'sectionid.required' => 'The Section is required',
                'sirname.required' => 'The sirname is required',
                'name.required' => 'The name is required',
                'designation.required' => 'The Designation is required',
                'email.required' => 'The email is required',
                'email.unique' => 'This email is already in use please try another',
                'profilepic.required' => 'The profilepic is required',
                'isactive.required' => 'The status is required'
            ]
        );
        $updatePassword = false;
        if ($request->password != '' || $request->password_confirmation != '') {
            if ($request->password == $request->password_confirmation) {
                $updatePassword = true;
            } else {
                return redirect()->back()->with('status', ' Password & confirm password did not matched');
            }
        }

        $userInfo = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.*', 'userdetails.profilepic', 'userdetails.aboutme'])->where('users.roles', $this->roles)->where('users.id', $userid)->first();

        if ($userInfo) {
            $userInfo->name = $request->name;
            $userInfo->sirname = $request->sirname;
            if ($updatePassword) {
                $userInfo->password = Hash::make($request->password_confirmation);
            }
            $userInfo->roles = $this->roles;
            $userInfo->email = $request->email;
            $userInfo->isactive = $request->isactive;
            $userInfo->save();
            $profilepicName = '';
            if (!empty($request->file('profilepic'))) {
                $profilepic = $request->file('profilepic');
                $profilepicName = $userid . '_' . time() . '.' . $profilepic->extension();
                $profilepic->move(public_path('userpics'), $profilepicName);
            }
            if (!empty($profilepicName)) {
                UserDetail::where('userid', $userid)
                    ->update([
                        'profilepic' => $profilepicName,
                        'designation' => $request->designation,
                        'aboutme' => $request->aboutme,
                        'sectionid' => $request->sectionid,
                        'personalgroupinfo' => $request->personalgroupinfo,
                        'googlelink' => $request->googlelink
                    ]);
            } else {
                UserDetail::where('userid', $userid)
                    ->update([
                        'designation' => $request->designation,
                        'aboutme' => $request->aboutme,
                        'sectionid' => $request->sectionid,
                        'personalgroupinfo' => $request->personalgroupinfo,
                        'googlelink' => $request->googlelink
                    ]);
            }
            return redirect()->route('scientists')->with('status', ' Profile has been updated successfully');
        } else {
            return redirect()->route('scientists')->with('status', ' Mentioned Id does not exist');
        }
    }
    public function destroy(Request $request, $id)
    {
        if ($request->tag == 'researchgroups') {
            $info  = Researchgroup::where('userid', Auth()->user()->id)->where('id', $id)->first();
            if ($info->interimage != "") {
                File::delete(public_path('userpics') . '/' . $info->interimage);
                Researchgroup::where('id', $id)->delete();
            }
        } else if ($request->tag == 'researchinterest') {
            Researchinterest::where('id', $id)->delete();
        } else if ($request->tag == 'relatedimages') {
            $info  = Researchinterest::where('userid', Auth()->user()->id)->where('id', $id)->where('type', 'relatedimages')->first();
            if ($info->description != "") {
                File::delete(public_path('userpics') . '/' . $info->description);
                Researchinterest::where('id', $id)->delete();
            }
        }
        return redirect()->back()->with('status', ' Content has been removed successfully');
    }
    ////////////////////////////////////////////////////////
    public function removescientists($pageid)
    {

        User::where('id', $pageid)->delete();
        return redirect()->route('scientists')->with('status', 'Page Remove Successfully');
    }
    ////////////////////////////////////////////////////
    public function scientiststatus(Request $request)
    {

        $userids = $request->post_id;
        $status_type = $request->status_type;
        User::whereIn('id', $userids)->update(['isactive' => $status_type]);
        return response()->json(['status' => true]);
    }

    /********Profile************/
    public function singleprofile()
    {
        if (Auth()->user()->ispasswordchange == 0) {
            return redirect()->route('sientistchangepassword');
        }
        $userinfo  = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.id', 'users.name', 'users.email', 'users.sirname', 'userdetails.*'])->where('users.roles', $this->roles)->where('users.id', Auth()->user()->id)->first();
        return view('myadmin.scientists.singleprofilehtml')
            ->with('userinfo', $userinfo)
            ->with('heading', 'Profile');
    }
    public function scientistimages()
    {
        if (Auth()->user()->ispasswordchange == 0) {
            return redirect()->route('sientistchangepassword');
        }
        $lists  = Researchinterest::where('userid', Auth()->user()->id)->where('type', 'relatedimages')->orderBy('id', 'DESC')->get();
        return view('myadmin.scientists.scientistimageshtml')
            ->with('lists', $lists)
            ->with('heading', 'Related Images');
    }
    public function scientistresearchinterest()
    {
        if (Auth()->user()->ispasswordchange == 0) {
            return redirect()->route('sientistchangepassword');
        }
        $lists  = Researchinterest::where('userid', Auth()->user()->id)->where('type', 'researchinterest')->orderBy('sortorder', 'ASC')->paginate(50);

        return view('myadmin.scientists.researchinteresthtml')
            ->with('lists', $lists)
            ->with('heading', 'Research Interest');
    }
    public function createscientistresearchinterest(Request $request)
    {
        $info = array();
        if (!empty($request->tokenid)) {
            $info  = Researchinterest::where('userid', Auth()->user()->id)->where('id', $request->tokenid)->first();
        }
        return view('myadmin.scientists.modals.create_researchinteresthtml')
            ->with('info', $info)
            ->with('statusArrays', $this->statusArrays)
            ->with('heading', 'Research Interest');
    }
    public function scientistresearchhighlights()
    {
        if (Auth()->user()->ispasswordchange == 0) {
            return redirect()->route('sientistchangepassword');
        }
        $lists  = Researchinterest::where('userid', Auth()->user()->id)->where('type', 'researchhighlights')->orderBy('sortorder', 'ASC')->paginate(50);
        return view('myadmin.scientists.scientistresearchhighlightshtml')
            ->with('lists', $lists)
            ->with('heading', 'Research Highlights');
    }
    public function createscientistresearchhighlights(Request $request)
    {
        $info = array();
        if (!empty($request->tokenid)) {
            $info  = Researchinterest::where('userid', Auth()->user()->id)->where('id', $request->tokenid)->first();
        }
        return view('myadmin.scientists.modals.create_scientistresearchhighlightshtml')
            ->with('info', $info)
            ->with('statusArrays', $this->statusArrays)
            ->with('heading', 'Research Highlights');
    }
    public function scientistresearchgroups(Request $request)
    {
       
        if (Auth()->user()->ispasswordchange == 0) {
            return redirect()->route('sientistchangepassword');
        }
        $sections = Section::where('isactive', 1)->where('type', 'researchgroup')->get();
        $scholorlists = Section::where('isactive', 1)->where('type', 'coregroupmembers')->get();
        $corembrid = $request->query('corembrid');
        $sectionid = $request->query('sectionid');
        $lists = Researchgroup::join('sections', 'sections.id', '=', 'researchgroups.sectionid')->where('researchgroups.userid', Auth()->user()->id)->where('sections.type', 'researchgroup')->leftJoin('sections as corembrsection', 'corembrsection.id', '=', 'researchgroups.corembrid')
            ->select(['sections.sectionname', 'researchgroups.*', 'corembrsection.sectionname as scholarname']);

        if (request('sectionid')) {
            $lists->where('researchgroups.sectionid', '=', request('sectionid'));
        }
        if (request('corembrid')) {
            $lists->where('researchgroups.corembrid', '=', request('corembrid'));
        }

        $lists = $lists->orderBy('sortorder', 'ASC')->paginate(50);

        return view('myadmin.scientists.scientistresearchgroupshtml')
            ->with('lists', $lists)
            ->with('sections', $sections)
            ->with('scholorlists', $scholorlists)
            ->with('sectionid', $sectionid)
            ->with('corembrid', $corembrid)
            ->with('heading', 'Research Groups');
    }
    public function createscientistresearchgroups(Request $request)
    {
        $info = array();
        if (!empty($request->tokenid)) {
            $info  = Researchgroup::where('userid', Auth()->user()->id)->where('id', $request->tokenid)->first();
        }
        $sections = Section::where('isactive', 1)->where('type', 'researchgroup')->get();
        $scholorlists = Section::where('isactive', 1)->where('type', 'coregroupmembers')->get();
        return view('myadmin.scientists.modals.create_scientistresearchgroupshtml')
            ->with('sections', $sections)
            ->with('info', $info)
            ->with('statusArrays', $this->statusArrays)
            ->with('scholorlists', $scholorlists)
            ->with('heading', 'Research Groups');
    }
    public function scientistpublications(Request $request)
    {

        if (Auth()->user()->ispasswordchange == 0) {
            return redirect()->route('sientistchangepassword');
        }
        $sections = Section::where('isactive', 1)->where('type', 'researchpublications')->get();
        $lists = Researchinterest::join('sections', 'sections.id', '=', 'researchinterests.sectionid')->where('userid', Auth()->user()->id)->where('researchinterests.type', 'researchpublications')->select(['sections.sectionname', 'researchinterests.*']);
        if (request('search')) {
            $lists->where('researchinterests.title', 'LIKE', '%' . request('search') . '%');
        }
        if (request('sectionid')) {
            $lists->where('researchinterests.sectionid', '=', $request->sectionid);
        }
        $lists = $lists->orderBy('sortorder') // Order by sortorder column
        ->paginate(100);
        // $lists = $lists->whereNotNull('researchinterests.order')->orderBy('researchinterests.order','ASC')->paginate(50);
        return view('myadmin.scientists.scientistpublicationshtml')
            ->with('lists', $lists)
            ->with('search', $request->search)
            ->with('sectionid', $request->sectionid)
            ->with('sections', $sections)
            ->with('heading', 'Publications');
    }
    /////////////////////////////////////////////////////////////////
    public function researchupdateOrder(Request $request)
    {
        $posts = Researchinterest::all();
     
        $maxOrder = Researchinterest::max('order');

        // Create the new record with the next order value
        $attributes['order'] = $maxOrder + 1;
        foreach ($posts as $post) {
            foreach ($request->order as $order) {
                if ($order['id'] == $post->id) {
                    $post->update(['order' => $order['position']]);
                }
            }
        }

        return response('Update Successfully.', 200);
    }
    /////////////////////////////////////////////////////
    public function createscientistpublications(Request $request)
    {
        $info = array();
        if (!empty($request->tokenid)) {
            $info  = Researchinterest::where('userid', Auth()->user()->id)->where('id', $request->tokenid)->first();
        }
        $sections = Section::where('isactive', 1)->where('type', 'researchpublications')->get();
        return view('myadmin.scientists.modals.create_scientistpublicationshtml')
            ->with('sections', $sections)
            ->with('info', $info)
            ->with('statusArrays', $this->statusArrays)
            ->with('heading', 'Publications');
    }

    public function updatescientistsprofile(Request $request, $userid)
    {
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
        if ($userInfo) {
            $userInfo->sirname = $request->sirname;
            $userInfo->name = $request->name;
            $userInfo->save();
            $profilepicName = '';
            if (!empty($request->file('profilepic'))) {
                $profilepic = $request->file('profilepic');
                $profilepicName = $userid . '_' . time() . '.' . $profilepic->extension();
                $profilepic->move(public_path('userpics'), $profilepicName);
            }
            $userDetailsInfo = UserDetail::find($userid);
            $userDetailsInfo->aboutme = $request->aboutme;
            $userDetailsInfo->designation = $request->designation;
            $userDetailsInfo->personalgroupinfo = $request->personalgroupinfo;
            $userDetailsInfo->googlelink = $request->googlelink;
            if (!empty($profilepicName)) {
                $userDetailsInfo->profilepic = $profilepicName;
            }
            $userDetailsInfo->save();

            return redirect()->route($httpredirect)->with('status', ' Basic profile has been updated successfully');
        } else {
            return redirect()->route($httpredirect)->with('status', ' Mentioned Id does not exist');
        }
    }
    public function deleteextrarecords(Request $request)
    {
        // $itemid = $request->itemid;
        // if($request->tag == 'researchgroups') {
        //     Researchgroup::where('id',$itemid)->delete();
        // } else if($request->tag == 'researchinterest') {
        //     Researchinterest::where('id',$itemid)->delete();
        // }
        // return redirect()->back()->with('status', ' Removed successfully');
    }
    public function saveOthercientistsprofile(Request $request)
    {
        if (!empty($request->tokenid)) {
            $findData = Researchinterest::find($request->tokenid);
            if ($findData) {
                $findData->title = $request->title;
                $findData->description = $request->description;
                $findData->type = $request->type;
                $findData->pi = $request->pi;
                $findData->journalname = $request->journalname;
                $findData->journalconference = $request->journalconference;
                $findData->bookpublisher = $request->bookpublisher;
                $findData->copi = $request->copi;
                $findData->amount = $request->amount;
                $findData->tenure = $request->tenure;
                $findData->agency = $request->agency;
                $findData->isactive = $request->isactive;
                $findData->volumes = $request->volumes;
                if (!empty($request->enddate)) {
                    $findData->enddate = convertdate($request->enddate);
                }
                $findData->sectionid = $request->sectionid;
                $findData->save();
                if (!empty($request->postdate)) {
                    $findData->postdate = convertdate($request->postdate);
                }
                $findData->save();
                return response()->json(array('status' => true, 'message' => 'Profile has been updated successfully'), 200);
            } else {
                return response()->json(array('status' => true, 'message' => 'Issue with update, refresh the page and try again'), 200);
            }
        } else {
            $userDetailsInfo = new Researchinterest();
            $userDetailsInfo->title = $request->title;
            $userDetailsInfo->description = $request->description;
            $userDetailsInfo->type = $request->type;
            $userDetailsInfo->pi = $request->pi;
            $userDetailsInfo->journalname = $request->journalname;
            $userDetailsInfo->journalconference = $request->journalconference;
            $userDetailsInfo->bookpublisher = $request->bookpublisher;
            $userDetailsInfo->copi = $request->copi;
            $userDetailsInfo->amount = $request->amount;
            $userDetailsInfo->tenure = $request->tenure;
            $userDetailsInfo->agency = $request->agency;
            $userDetailsInfo->isactive = $request->isactive;
            $userId = Auth()->user()->id;
            $getdata = Researchinterest::where('type',$request->type)->where('userid',$userId)->where('sectionid',$request->sectionid)->get();
            // print_r($getdata)); die;
            if(!empty($getdata))
            {
                $last = Researchinterest::where('type',$request->type)->where('userid',$userId)->where('sectionid',$request->sectionid)->orderBy('sortorder','DESC')->first();
                $order = isset($last->sortorder) ? $last->sortorder : 0;
                // dd($order);
              
                $userDetailsInfo->sortorder = $order + 1;

            }else{
                $userDetailsInfo->sortorder = 1;

            }
             
            $userDetailsInfo->isactive = $request->isactive;


            $userDetailsInfo->volumes = $request->volumes;
            if (!empty($request->enddate)) {
                $userDetailsInfo->enddate = convertdate($request->enddate);
            }
            $userDetailsInfo->sectionid = $request->sectionid;
            $userDetailsInfo->userid = Auth()->user()->id;
            $userDetailsInfo->save();
        }
        return response()->json(array('status' => true, 'message' => 'Profile has been saved successfully'), 200);
        //return redirect()->route($request->httpredirect)->with('status', ' Content has been updated successfully');
    }
    public function dropzoneImagesStore(Request $request)
    {

        $imagelists = $request->file('file');
        $size = sizeof($imagelists);
        $params = array();
        foreach ($imagelists as $key => $imagelist) {
            $imageName = $key . '_' . uniqid() . '.' . $imagelist->extension();
            $imagelist->move(public_path('userpics'), $imageName);
            $userDetailsInfo = new Researchinterest();
            $userDetailsInfo->type = $request->type;
            $userDetailsInfo->description = $imageName;
            $userDetailsInfo->userid = Auth()->user()->id;
            $userDetailsInfo->save();
        }
        return response()->json(["status" => "success", "data" => 'Content has been updated successfully']);
    }
    public function savescientistresearchgroup(Request $request)
    {
        if (!empty($request->tokenid)) {
            $findData = Researchgroup::find($request->tokenid);
            if ($findData) {
                $findData->name = $request->name;
                $findData->email = $request->email;
                $findData->isactive = $request->isactive;
                $findData->regno = $request->regno;
                $findData->presentaffiliation = $request->presentaffiliation;
                if (!empty($request->enddate)) {
                    $findData->enddate = convertdate($request->enddate);
                }
                if (!empty($request->workingsince)) {
                    $findData->workingsince = convertdate($request->workingsince);
                }
                $findData->corembrid = $request->corembrid;
                $findData->sectionid = $request->sectionid;
                $interimageName = '';
                if (!empty($request->file('interimage'))) {
                    $interimage = $request->file('interimage');
                    $interimageName = Auth()->user()->id . '_' . time() . '.' . $interimage->extension();
                    $interimage->move(public_path('userpics'), $interimageName);
                }
                if (!empty($interimageName)) {
                    $findData->interimage = $interimageName;
                }
                $findData->save();
                return response()->json(array('status' => true, 'message' => 'Content has been updated successfully'), 200);
            } else {
                return response()->json(array('status' => true, 'message' => 'Issue with update, refresh the page and try again'), 200);
            }
        } else {

            $userDetailsInfo = new Researchgroup();
            $userDetailsInfo->name = $request->name;
            $userDetailsInfo->email = $request->email;
            $userDetailsInfo->isactive = $request->isactive;
            $userDetailsInfo->regno = $request->regno;
            $userDetailsInfo->presentaffiliation = $request->presentaffiliation;
            if (!empty($request->workingsince)) {
                $userDetailsInfo->workingsince = convertdate($request->workingsince);
            }
            if (!empty($request->enddate)) {
                $userDetailsInfo->enddate = convertdate($request->enddate);
            }
            $userDetailsInfo->corembrid = $request->corembrid;
            $userDetailsInfo->sectionid = $request->sectionid;

            $interimageName = '';
            if (!empty($request->file('interimage'))) {
                $interimage = $request->file('interimage');
                $interimageName = Auth()->user()->id . '_' . time() . '.' . $interimage->extension();
                $interimage->move(public_path('userpics'), $interimageName);
            }
            $userDetailsInfo->userid = Auth()->user()->id;
            if (!empty($interimageName)) {
                $userDetailsInfo->interimage = $interimageName;
            }
            $userDetailsInfo->save();
            return response()->json(array('status' => true, 'message' => 'Content has been saved successfully'), 200);
        }
    }

    public function savescientistresearchgroupByAdmin(Request $request)
    {
        if (!empty($request->tokenid)) {
            $findData = Researchgroup::find($request->tokenid);
            if ($findData) {
                $findData->name = $request->name;
                $findData->email = $request->email;
                $findData->regno = $request->regno;
                $findData->userid = $request->userid;
                $findData->isactive = $request->isactive;
                $findData->personalemail = $request->personalemail;
                $findData->presentaffiliation = $request->presentaffiliation;
                // if (!empty($request->enddate)) {
                //     $findData->enddate = convertdate($request->enddate);
                // }
                // dd($request->enddate);
                $findData->enddate = $request->enddate != '' ? convertdate($request->enddate) : NULL;

                // if (!empty($request->workingsince)) {
                    $findData->workingsince = $request->workingsince != '' ? convertdate($request->workingsince) : NULL;
                // }
                $findData->corembrid = $request->corembrid;
                $findData->sectionid = $request->sectionid;
                $interimageName = '';
                if (!empty($request->file('interimage'))) {
                    $interimage = $request->file('interimage');
                    $interimageName = $request->userid . '_' . time() . '.' . $interimage->extension();
                    $interimage->move(public_path('userpics'), $interimageName);
                }
                if (!empty($interimageName)) {
                    $findData->interimage = $interimageName;
                }
                $findData->save();
                return redirect()->route('students')->with('status', ' Content has been saved successfully');
            } else {
                return response()->json(array('status' => true, 'message' => 'Issue with update, refresh the page and try again'), 200);
            }
        } else {
            $userDetailsInfo = new Researchgroup();
            $userDetailsInfo->customname = $request->customname;
            $userDetailsInfo->name = $request->name;
            $userDetailsInfo->email = $request->email;
            $userDetailsInfo->regno = $request->regno;
            $userDetailsInfo->personalemail = $request->personalemail;
            $userDetailsInfo->presentaffiliation = $request->presentaffiliation;
            // if (!empty($request->workingsince)) {
                $userDetailsInfo->workingsince = $request->workingsince != '' ? convertdate($request->workingsince) : NULL;
            // }
            // if (!empty($request->enddate)) {
                $userDetailsInfo->enddate = $request->enddate != '' ? convertdate($request->enddate) : NULL;
            // }
            $userDetailsInfo->corembrid = $request->corembrid;
            $userDetailsInfo->sectionid = $request->sectionid;

            $interimageName = '';
            if (!empty($request->file('interimage'))) {
                $interimage = $request->file('interimage');
                $interimageName = $request->userid . '_' . time() . '.' . $interimage->extension();
                $interimage->move(public_path('userpics'), $interimageName);
            }
            $userDetailsInfo->userid = $request->userid;
            if (!empty($interimageName)) {
                $userDetailsInfo->interimage = $interimageName;
            }
            $userDetailsInfo->save();
            return redirect()->route('students')->with('status', ' Content has been saved successfully');
        }
    }

    public function biodataresearchespawards(Request $request)
    {
        if (Auth()->user()->ispasswordchange == 0) {
            return redirect()->route('sientistchangepassword');
        }
        $sectionid = $request->sectionid;
     
        $lists = Researchinterest::join('sections', 'sections.id', '=', 'researchinterests.sectionid')->where(['userid' => Auth()->user()->id, 'researchinterests.type' => 'researchbiodata', 'researchinterests.sectionid' => $sectionid])->select(['sections.sectionname', 'researchinterests.*']);
        if (request('search')) {
            $lists->where('researchinterests.description', 'LIKE', '%' . request('search') . '%');
        }
        $lists = $lists->orderBy('sortorder', 'ASC')->paginate(50);
        // dd($lists); 
        return view('myadmin.scientists.researchbiodataawardshtml')
            ->with('lists', $lists)
            ->with('sectionid', $sectionid)
            ->with('search', request('search'))
            ->with('heading', ($sectionid == 10 ? 'Research Experience' : 'Awards & Honours'));
    }
    public function createbiodataresearchespawards(Request $request)
    {
        $info = array();
        if (!empty($request->tokenid) && !empty($request->sectionid)) {
            $info  = Researchinterest::where([
                'userid' => Auth()->user()->id,
                'id' => $request->tokenid,
                'sectionid' => $request->sectionid,
            ])->first();
        }
        return view('myadmin.scientists.modals.create_researchbiodataawardshtml')
            ->with('info', $info)
            ->with('sectionid', $request->sectionid)
            ->with('statusArrays', $this->statusArrays)
            ->with('heading', ($request->sectionid == 10 ? 'Research Experience' : 'Awards & Honours'));
    }
    public function biodataresearchegrant(Request $request)
    {
        if (Auth()->user()->ispasswordchange == 0) {
            return redirect()->route('sientistchangepassword');
        }
        $lists = Researchinterest::join('sections', 'sections.id', '=', 'researchinterests.sectionid')->where(['userid' => Auth()->user()->id, 'researchinterests.type' => 'researchbiodata', 'researchinterests.sectionid' => $this->GrantSectionId])->select(['sections.sectionname', 'researchinterests.*']);
        if (request('search')) {
            $lists->where('researchinterests.description', 'LIKE', '%' . request('search') . '%');
        }
        $lists = $lists->orderBy('sortorder', 'ASC')->paginate(50);
        return view('myadmin.scientists.biodataresearchegranthtml')
            ->with('lists', $lists)
            ->with('search', request('search'))
            ->with('heading', 'Research Grants Secured');
    }
    public function createbiodataresearchegrant(Request $request)
    {
        $info = array();
        if (!empty($request->tokenid)) {
            $info  = Researchinterest::where([
                'userid' => Auth()->user()->id,
                'id' => $request->tokenid,
                'sectionid' => $this->GrantSectionId,
            ])->first();
        }
        return view('myadmin.scientists.modals.create_biodataresearchegranthtml')
            ->with('info', $info)
            ->with('statusArrays', $this->statusArrays)
            ->with('sectionid', $this->GrantSectionId)
            ->with('heading', 'Research Grants Secured');
    }

    public function scientistUpdateOrder(Request $request)
	{

		$orders = $request->input('order');
	
		foreach ($orders as $order) {

			$id = $order['id'];
			$position = $order['position'];
			$type = $order['type'];

			$list = Researchinterest::where('id', $id)->where('type', $type)
				->firstOrFail();
                
			$list->sortorder = $position;
			$list->save();
		}
		// die;

		return response()->json(['status' => 'success']);
	}
}
