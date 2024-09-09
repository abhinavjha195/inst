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
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class ScientistController extends Controller
{
    /**
     * @var array<string, string>
     */
    protected array $statusArrays;

    /**
     * @var Collection<int, category>
     */
    
    protected Collection $catlists;


      /**
     * @var string
     */
    protected array $roles;


      /**
     * @var int
     */
    protected int $GrantSectionId;


    public function __construct()
    {
        $this->statusArrays = ['' => 'Status', 'inactive' => 'inActive', 'active' => 'Active'];
        $this->roles = 'scientists';
        $this->GrantSectionId = 12;
    }

    public function index(Request $request): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('singleprofile');
        }
        $search = $request->input('search');
        $userlists = User::join('userdetails', 'users.id', '=', 'userdetails.userid')
            ->select(['users.*', 'userdetails.profilepic', 'userdetails.designation'])
            ->where('users.roles', $this->roles)
            ->orderBy('id', 'DESC');
        if (request('search')) {
            $userlists->where('users.name', 'LIKE', '%' . request('search') . '%');
        }
        $lists = $userlists->paginate(40);
        return view('myadmin.scientists.listhtml', [
            'lists' => $lists,
            'search' => $search,
            'totalrecords' => 'Scientists : ' . $lists->count() . ' Records found'
        ]);
    }

    public function create(): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('singleprofile');
        }
        $sections = Section::where('isactive', 1)->where('type', 'scientists')->get();
        return view('myadmin.scientists.createhtml', [
            'statusArrays' => $this->statusArrays,
            'sections' => $sections,
            'heading' => 'Add new scientist'
        ]);
    }

    public function sientistchangepassword(): View|Factory
    {
        $sections = Section::where('isactive', 1)->where('type', 'scientists')->get();
        return view('myadmin.scientists.changepasswordhtml', [
            'heading' => 'Change Password'
        ]);
    }

    public function updatesientistchangepassword(Request $request): RedirectResponse
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

    public function store(Request $request): RedirectResponse
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
        $user->sirname = $request->input('sirname');
        $user->name = $request->input('name');
        $user->password = Hash::make($request->input('password'));
        $user->roles = $this->roles;
        $user->email = $request->input('email');
        $user->isactive = $request->input('isactive');
        $user->save();
        $profilepicName = '';
        if ($request->hasFile('profilepic')) {
            $profilepic = $request->file('profilepic');
            $profilepicName = $user->id . '_' . time() . '.' . $profilepic->extension();
            $profilepic->move(public_path('userpics'), $profilepicName);
        }
        Userdetail::create([
            'userid' => $user->id,
            'sectionid' => $request->input('sectionid'),
            'designation' => $request->input('designation'),
            'profilepic' => $profilepicName,
            'aboutme' => $request->input('aboutme')
        ]);
        return Redirect::route('scientists')->with('status', ' scientist has been created successfully');
    }

    public function show(int $id): void
    {
    }

    public function edit(int $userid): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('singleprofile');
        }
        $sections = Section::where('isactive', 1)->where('type', 'scientists')->get();
        $userinfo = User::join('userdetails', 'users.id', '=', 'userdetails.userid')
            ->select(['users.*', 'userdetails.profilepic', 'userdetails.aboutme', 'userdetails.sectionid', 'userdetails.designation', 'userdetails.googlelink', 'userdetails.personalgroupinfo'])
            ->where('users.roles', $this->roles)
            ->where('users.id', $userid)
            ->first();
        if ($userinfo) {
            return view('myadmin.scientists.edithtml', [
                'statusArrays' => $this->statusArrays,
                'heading' => 'Edit scientist',
                'sections' => $sections,
                'userinfo' => $userinfo
            ]);
        } else {
            return Redirect::route('scientists')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function update(Request $request, int $userid): RedirectResponse
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
        if ($request->input('password') != '' || $request->input('password_confirmation') != '') {
            if ($request->input('password') == $request->input('password_confirmation')) {
                $updatePassword = true;
            } else {
                return Redirect::back()->with('status', ' Password & confirm password did not matched');
            }
        }

        $userInfo = User::join('userdetails', 'users.id', '=', 'userdetails.userid')
            ->select(['users.*', 'userdetails.profilepic', 'userdetails.aboutme'])
            ->where('users.roles', $this->roles)
            ->where('users.id', $userid)
            ->first();

        if ($userInfo) {
            $userInfo->name = $request->input('name');
            $userInfo->sirname = $request->input('sirname');
            if ($updatePassword) {
                $userInfo->password = Hash::make($request->input('password_confirmation'));
            }
            $userInfo->roles = $this->roles;
            $userInfo->email = $request->input('email');
            $userInfo->isactive = $request->input('isactive');
            $userInfo->save();
            $profilepicName = '';
            if ($request->hasFile('profilepic')) {
                $profilepic = $request->file('profilepic');
                $profilepicName = $userid . '_' . time() . '.' . $profilepic->extension();
                $profilepic->move(public_path('userpics'), $profilepicName);
            }
            if (!empty($profilepicName)) {
                UserDetail::where('userid', $userid)
                    ->update([
                        'profilepic' => $profilepicName,
                        'designation' => $request->input('designation'),
                        'aboutme' => $request->input('aboutme'),
                        'sectionid' => $request->input('sectionid'),
                        'personalgroupinfo' => $request->input('personalgroupinfo'),
                        'googlelink' => $request->input('googlelink')
                    ]);
            } else {
                UserDetail::where('userid', $userid)
                    ->update([
                        'designation' => $request->input('designation'),
                        'aboutme' => $request->input('aboutme'),
                        'sectionid' => $request->input('sectionid'),
                        'personalgroupinfo' => $request->input('personalgroupinfo'),
                        'googlelink' => $request->input('googlelink')
                    ]);
            }
            return Redirect::route('scientists')->with('status', ' Profile has been updated successfully');
        } else {
            return Redirect::route('scientists')->with('status', ' Mentioned Id does not exist');
        }
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $tag = $request->input('tag');

        if ($tag == 'researchgroups') {
            $info = Researchgroup::where('userid', Auth::id())->where('id', $id)->first();
            if ($info && $info->interimage != "") {
                File::delete(public_path('userpics') . '/' . $info->interimage);
                Researchgroup::where('id', $id)->delete();
            }
        } else if ($tag == 'researchinterest') {
            Researchinterest::where('id', $id)->delete();
        } else if ($tag == 'relatedimages') {
            $info = Researchinterest::where('userid', Auth::id())->where('id', $id)->where('type', 'relatedimages')->first();
            if ($info && $info->description != "") {
                File::delete(public_path('userpics') . '/' . $info->description);
                Researchinterest::where('id', $id)->delete();
            }
        }
        return Redirect::back()->with('status', ' Content has been removed successfully');
    }

    public function removescientists(int $pageid): RedirectResponse
    {
        User::where('id', $pageid)->delete();
        return Redirect::route('scientists')->with('status', 'Page Remove Successfully');
    }

    public function scientiststatus(Request $request): JsonResponse
    {
        $userids = $request->input('post_id');
        $status_type = $request->input('status_type');
        User::whereIn('id', $userids)->update(['isactive' => $status_type]);
        return Response::json(['status' => true]);
    }

    public function singleprofile(): View|Factory|RedirectResponse
    {
        if (!Auth::check()) {
            return Redirect::route('login');
        }

        $user = Auth::user();
        if ($user && $user->ispasswordchange == 0) {
            return Redirect::route('sientistchangepassword');
        }

        $userinfo = User::join('userdetails', 'users.id', '=', 'userdetails.userid')
            ->select(['users.id', 'users.name', 'users.email', 'users.sirname', 'userdetails.*'])
            ->where('users.roles', $this->roles)
            ->where('users.id', Auth::id())
            ->first();

        return view('myadmin.scientists.singleprofilehtml', [
            'userinfo' => $userinfo,
            'heading' => 'Profile'
        ]);
    }

    public function scientistimages(): View|Factory|RedirectResponse
    {
        if (!Auth::check()) {
            return Redirect::route('login');
        }

        $user = Auth::user();
        if ($user && $user->ispasswordchange == 0) {
            return Redirect::route('sientistchangepassword');
        }

        $lists = Researchinterest::where('userid', Auth::id())
            ->where('type', 'relatedimages')
            ->orderBy('id', 'DESC')
            ->get();

        return view('myadmin.scientists.scientistimageshtml', [
            'lists' => $lists,
            'heading' => 'Related Images'
        ]);
    }

    public function scientistresearchinterest(): View|Factory|RedirectResponse
    {
        if (!Auth::check()) {
            return Redirect::route('login');
        }

        $user = Auth::user();
        if ($user && $user->ispasswordchange == 0) {
            return Redirect::route('sientistchangepassword');
        }

        $lists = Researchinterest::where('userid', Auth::id())
            ->where('type', 'researchinterest')
            ->orderBy('sortorder', 'ASC')
            ->paginate(50);

        return view('myadmin.scientists.researchinteresthtml', [
            'lists' => $lists,
            'heading' => 'Research Interest'
        ]);
    }

    public function createscientistresearchinterest(Request $request): View|Factory
    {
        $info = [];
        if (!empty($request->input('tokenid'))) {
            $info = Researchinterest::where('userid', Auth::id())
                ->where('id', $request->input('tokenid'))
                ->first();
        }
        return view('myadmin.scientists.modals.create_researchinteresthtml', [
            'info' => $info,
            'statusArrays' => $this->statusArrays,
            'heading' => 'Research Interest'
        ]);
    }

    public function scientistresearchhighlights(): View|Factory|RedirectResponse
    {
        if (!Auth::check()) {
            return Redirect::route('login');
        }

        $user = Auth::user();
        if ($user && $user->ispasswordchange == 0) {
            return Redirect::route('sientistchangepassword');
        }

        $lists = Researchinterest::where('userid', Auth::id())
            ->where('type', 'researchhighlights')
            ->orderBy('sortorder', 'ASC')
            ->paginate(50);

        return view('myadmin.scientists.scientistresearchhighlightshtml', [
            'lists' => $lists,
            'heading' => 'Research Highlights'
        ]);
    }

    public function createscientistresearchhighlights(Request $request): View|Factory
    {
        $info = [];
        if (!empty($request->input('tokenid'))) {
            $info = Researchinterest::where('userid', Auth::id())
                ->where('id', $request->input('tokenid'))
                ->first();
        }
        return view('myadmin.scientists.modals.create_scientistresearchhighlightshtml', [
            'info' => $info,
            'statusArrays' => $this->statusArrays,
            'heading' => 'Research Highlights'
        ]);
    }

    public function scientistresearchgroups(Request $request): View|Factory|RedirectResponse
    {
        if (!Auth::check()) {
            return Redirect::route('login');
        }

        $user = Auth::user();
        if ($user && $user->ispasswordchange == 0) {
            return Redirect::route('sientistchangepassword');
        }

        $sections = Section::where('isactive', 1)->where('type', 'researchgroup')->get();
        $scholorlists = Section::where('isactive', 1)->where('type', 'coregroupmembers')->get();
        $corembrid = $request->input('corembrid');
        $sectionid = $request->input('sectionid');
        $lists = Researchgroup::join('sections', 'sections.id', '=', 'researchgroups.sectionid')
            ->where('researchgroups.userid', Auth::id())
            ->where('sections.type', 'researchgroup')
            ->leftJoin('sections as corembrsection', 'corembrsection.id', '=', 'researchgroups.corembrid')
            ->select(['sections.sectionname', 'researchgroups.*', 'corembrsection.sectionname as scholarname']);

        if (request('sectionid')) {
            $lists->where('researchgroups.sectionid', '=', request('sectionid'));
        }
        if (request('corembrid')) {
            $lists->where('researchgroups.corembrid', '=', request('corembrid'));
        }

        $lists = $lists->orderBy('sortorder', 'ASC')->paginate(50);

        return view('myadmin.scientists.scientistresearchgroupshtml', [
            'lists' => $lists,
            'sections' => $sections,
            'scholorlists' => $scholorlists,
            'sectionid' => $sectionid,
            'corembrid' => $corembrid,
            'heading' => 'Research Groups'
        ]);
    }

    public function createscientistresearchgroups(Request $request): View|Factory
    {
        $info = [];
        if (!empty($request->input('tokenid'))) {
            $info = Researchgroup::where('userid', Auth::id())
                ->where('id', $request->input('tokenid'))
                ->first();
        }
        $sections = Section::where('isactive', 1)->where('type', 'researchgroup')->get();
        $scholorlists = Section::where('isactive', 1)->where('type', 'coregroupmembers')->get();
        return view('myadmin.scientists.modals.create_scientistresearchgroupshtml', [
            'sections' => $sections,
            'info' => $info,
            'statusArrays' => $this->statusArrays,
            'scholorlists' => $scholorlists,
            'heading' => 'Research Groups'
        ]);
    }

    public function scientistpublications(Request $request): View|Factory|RedirectResponse
    {
        if (!Auth::check()) {
            return Redirect::route('login');
        }

        $user = Auth::user();
        if ($user && $user->ispasswordchange == 0) {
            return Redirect::route('sientistchangepassword');
        }

        $sections = Section::where('isactive', 1)->where('type', 'researchpublications')->get();
        $lists = Researchinterest::join('sections', 'sections.id', '=', 'researchinterests.sectionid')
            ->where('userid', Auth::id())
            ->where('researchinterests.type', 'researchpublications')
            ->select(['sections.sectionname', 'researchinterests.*']);
        if (request('search')) {
            $lists->where('researchinterests.title', 'LIKE', '%' . request('search') . '%');
        }
        if (request('sectionid')) {
            $lists->where('researchinterests.sectionid', '=', $request->input('sectionid'));
        }
        $lists = $lists->orderBy('sortorder')
            ->paginate(100);

        return view('myadmin.scientists.scientistpublicationshtml', [
            'lists' => $lists,
            'search' => $request->input('search'),
            'sectionid' => $request->input('sectionid'),
            'sections' => $sections,
            'heading' => 'Publications'
        ]);
    }

    public function researchupdateOrder(Request $request): JsonResponse
    {
        $posts = Researchinterest::all();
     
        $maxOrder = Researchinterest::max('order');

        foreach ($posts as $post) {
            foreach ($request->order as $order) {
                if ($order['id'] == $post->id) {
                    $post->update(['order' => $order['position']]);
                }
            }
        }

        return Response::json('Update Successfully.', 200);
    }

    public function createscientistpublications(Request $request): View|Factory
    {
        $info = [];
        if (!empty($request->input('tokenid'))) {
            $info = Researchinterest::where('userid', Auth::id())
                ->where('id', $request->input('tokenid'))
                ->first();
        }
        $sections = Section::where('isactive', 1)->where('type', 'researchpublications')->get();
        return view('myadmin.scientists.modals.create_scientistpublicationshtml', [
            'sections' => $sections,
            'info' => $info,
            'statusArrays' => $this->statusArrays,
            'heading' => 'Publications'
        ]);
    }

    public function updatescientistsprofile(Request $request, int $userid): RedirectResponse
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
        $userInfo = User::find(Auth::id());
        $httpredirect = $request->input('httpredirect');
        if ($userInfo) {
            $userInfo->sirname = $request->input('sirname');
            $userInfo->name = $request->input('name');
            $userInfo->save();
            $profilepicName = '';
            if ($request->hasFile('profilepic')) {
                $profilepic = $request->file('profilepic');
                $profilepicName = $userid . '_' . time() . '.' . $profilepic->extension();
                $profilepic->move(public_path('userpics'), $profilepicName);
            }
            $userDetailsInfo = UserDetail::find($userid);
            $userDetailsInfo->aboutme = $request->input('aboutme');
            $userDetailsInfo->designation = $request->input('designation');
            $userDetailsInfo->personalgroupinfo = $request->input('personalgroupinfo');
            $userDetailsInfo->googlelink = $request->input('googlelink');
            if (!empty($profilepicName)) {
                $userDetailsInfo->profilepic = $profilepicName;
            }
            $userDetailsInfo->save();

            return Redirect::route($httpredirect)->with('status', ' Basic profile has been updated successfully');
        } else {
            return Redirect::route($httpredirect)->with('status', ' Mentioned Id does not exist');
        }
    }

    public function deleteextrarecords(Request $request): void
    {
    }

    public function saveOthercientistsprofile(Request $request): JsonResponse
    {
        if (!empty($request->input('tokenid'))) {
            $findData = Researchinterest::find($request->input('tokenid'));
            if ($findData) {
                $findData->title = $request->input('title');
                $findData->description = $request->input('description');
                $findData->type = $request->input('type');
                $findData->pi = $request->input('pi');
                $findData->journalname = $request->input('journalname');
                $findData->journalconference = $request->input('journalconference');
                $findData->bookpublisher = $request->input('bookpublisher');
                $findData->copi = $request->input('copi');
                $findData->amount = $request->input('amount');
                $findData->tenure = $request->input('tenure');
                $findData->agency = $request->input('agency');
                $findData->isactive = $request->input('isactive');
                $findData->volumes = $request->input('volumes');
                if (!empty($request->input('enddate'))) {
                    $findData->enddate = convertdate($request->input('enddate'));
                }
                $findData->sectionid = $request->input('sectionid');
                $findData->save();
                if (!empty($request->input('postdate'))) {
                    $findData->postdate = convertdate($request->input('postdate'));
                }
                $findData->save();
                return Response::json(['status' => true, 'message' => 'Profile has been updated successfully'], 200);
            } else {
                return Response::json(['status' => true, 'message' => 'Issue with update, refresh the page and try again'], 200);
            }
        } else {
            $userDetailsInfo = new Researchinterest();
            $userDetailsInfo->title = $request->input('title');
            $userDetailsInfo->description = $request->input('description');
            $userDetailsInfo->type = $request->input('type');
            $userDetailsInfo->pi = $request->input('pi');
            $userDetailsInfo->journalname = $request->input('journalname');
            $userDetailsInfo->journalconference = $request->input('journalconference');
            $userDetailsInfo->bookpublisher = $request->input('bookpublisher');
            $userDetailsInfo->copi = $request->input('copi');
            $userDetailsInfo->amount = $request->input('amount');
            $userDetailsInfo->tenure = $request->input('tenure');
            $userDetailsInfo->agency = $request->input('agency');
            $userDetailsInfo->isactive = $request->input('isactive');
            $userId = Auth::id();
            $getdata = Researchinterest::where('type', $request->input('type'))
                ->where('userid', $userId)
                ->where('sectionid', $request->input('sectionid'))
                ->get();
            
            if(!$getdata->isEmpty()) {
                $last = Researchinterest::where('type', $request->input('type'))
                    ->where('userid', $userId)
                    ->where('sectionid', $request->input('sectionid'))
                    ->orderBy('sortorder', 'DESC')
                    ->first();
                $order = isset($last->sortorder) ? $last->sortorder : 0;
                $userDetailsInfo->sortorder = $order + 1;
            } else {
                $userDetailsInfo->sortorder = 1;
            }
             
            $userDetailsInfo->isactive = $request->input('isactive');
            $userDetailsInfo->volumes = $request->input('volumes');
            if (!empty($request->input('enddate'))) {
                $userDetailsInfo->enddate = convertdate($request->input('enddate'));
            }
            $userDetailsInfo->sectionid = $request->input('sectionid');
            $userDetailsInfo->userid = Auth::id();
            $userDetailsInfo->save();
        }
        return Response::json(['status' => true, 'message' => 'Profile has been saved successfully'], 200);
    }

    public function dropzoneImagesStore(Request $request): JsonResponse
    {
        $imagelists = $request->file('file');
        if (!is_array($imagelists)) {
            $imagelists = [$imagelists];
        }
        
        foreach ($imagelists as $key => $imagelist) {
            $imageName = $key . '_' . uniqid() . '.' . $imagelist->extension();
            $imagelist->move(public_path('userpics'), $imageName);
            $userDetailsInfo = new Researchinterest();
            $userDetailsInfo->type = $request->input('type');
            $userDetailsInfo->description = $imageName;
            $userDetailsInfo->userid = Auth::id();
            $userDetailsInfo->save();
        }
        return Response::json(['status' => 'success', 'data' => 'Content has been updated successfully']);
    }

    public function savescientistresearchgroup(Request $request): JsonResponse
    {
        if (!empty($request->input('tokenid'))) {
            $findData = Researchgroup::find($request->input('tokenid'));
            if ($findData) {
                $findData->name = $request->input('name');
                $findData->email = $request->input('email');
                $findData->isactive = $request->input('isactive');
                $findData->regno = $request->input('regno');
                $findData->presentaffiliation = $request->input('presentaffiliation');
                if (!empty($request->input('enddate'))) {
                    $findData->enddate = convertdate($request->input('enddate'));
                }
                if (!empty($request->input('workingsince'))) {
                    $findData->workingsince = convertdate($request->input('workingsince'));
                }
                $findData->corembrid = $request->input('corembrid');
                $findData->sectionid = $request->input('sectionid');
                $interimageName = '';
                if ($request->hasFile('interimage')) {
                    $interimage = $request->file('interimage');
                    if ($interimage instanceof \Illuminate\Http\UploadedFile) {
                        $interimageName = Auth::id() . '_' . time() . '.' . $interimage->getClientOriginalExtension();
                        $interimage->move(public_path('userpics'), $interimageName);
                    }
                }
                if (!empty($interimageName)) {
                    $findData->interimage = $interimageName;
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
            $userDetailsInfo->isactive = $request->input('isactive');
            $userDetailsInfo->regno = $request->input('regno');
            $userDetailsInfo->presentaffiliation = $request->input('presentaffiliation');
            if (!empty($request->input('workingsince'))) {
                $userDetailsInfo->workingsince = convertdate($request->input('workingsince'));
            }
            if (!empty($request->input('enddate'))) {
                $userDetailsInfo->enddate = convertdate($request->input('enddate'));
            }
            $userDetailsInfo->corembrid = $request->input('corembrid');
            $userDetailsInfo->sectionid = $request->input('sectionid');

            $interimageName = '';
            if ($request->hasFile('interimage')) {
                $interimage = $request->file('interimage');
                if ($interimage instanceof \Illuminate\Http\UploadedFile) {
                    $interimageName = Auth::id() . '_' . time() . '.' . $interimage->getClientOriginalExtension();
                    $interimage->move(public_path('userpics'), $interimageName);
                }
            }
            $userDetailsInfo->userid = Auth::id();
            if (!empty($interimageName)) {
                $userDetailsInfo->interimage = $interimageName;
            }
            $userDetailsInfo->save();
            return Response::json(['status' => true, 'message' => 'Content has been saved successfully'], 200);
        }
    }

    public function savescientistresearchgroupByAdmin(Request $request): RedirectResponse
    {
        if (!empty($request->input('tokenid'))) {
            $findData = Researchgroup::find($request->input('tokenid'));
            if ($findData) {
                $findData->name = $request->input('name');
                $findData->email = $request->input('email');
                $findData->regno = $request->input('regno');
                $findData->userid = $request->input('userid');
                $findData->isactive = $request->input('isactive');
                $findData->personalemail = $request->input('personalemail');
                $findData->presentaffiliation = $request->input('presentaffiliation');
                $findData->enddate = $request->input('enddate') != '' ? convertdate($request->input('enddate')) : NULL;
                $findData->workingsince = $request->input('workingsince') != '' ? convertdate($request->input('workingsince')) : NULL;
                $findData->corembrid = $request->input('corembrid');
                $findData->sectionid = $request->input('sectionid');
                $interimageName = '';
                if ($request->hasFile('interimage')) {
                    $interimage = $request->file('interimage');
                    if ($interimage instanceof \Illuminate\Http\UploadedFile) {
                        $interimageName = $request->input('userid') . '_' . time() . '.' . $interimage->getClientOriginalExtension();
                        $interimage->move(public_path('userpics'), $interimageName);
                    }
                }
                if (!empty($interimageName)) {
                    $findData->interimage = $interimageName;
                }
                $findData->save();
                return Redirect::route('students')->with('status', ' Content has been saved successfully');
            } else {
                return Response::json(['status' => true, 'message' => 'Issue with update, refresh the page and try again'], 200);
            }
        } else {
            $userDetailsInfo = new Researchgroup();
            $userDetailsInfo->customname = $request->input('customname');
            $userDetailsInfo->name = $request->input('name');
            $userDetailsInfo->email = $request->input('email');
            $userDetailsInfo->regno = $request->input('regno');
            $userDetailsInfo->personalemail = $request->input('personalemail');
            $userDetailsInfo->presentaffiliation = $request->input('presentaffiliation');
            $userDetailsInfo->workingsince = $request->input('workingsince') != '' ? convertdate($request->input('workingsince')) : NULL;
            $userDetailsInfo->enddate = $request->input('enddate') != '' ? convertdate($request->input('enddate')) : NULL;
            $userDetailsInfo->corembrid = $request->input('corembrid');
            $userDetailsInfo->sectionid = $request->input('sectionid');

            $interimageName = '';
            if ($request->hasFile('interimage')) {
                $interimage = $request->file('interimage');
                if ($interimage instanceof \Illuminate\Http\UploadedFile) {
                    $interimageName = $request->input('userid') . '_' . time() . '.' . $interimage->getClientOriginalExtension();
                    $interimage->move(public_path('userpics'), $interimageName);
                }
            }
            $userDetailsInfo->userid = $request->input('userid');
            if (!empty($interimageName)) {
                $userDetailsInfo->interimage = $interimageName;
            }
            $userDetailsInfo->save();
            return Redirect::route('students')->with('status', ' Content has been saved successfully');
        }
    }

    public function biodataresearchespawards(Request $request): View|Factory|RedirectResponse
    {
        if (!Auth::check()) {
            return Redirect::route('login');
        }

        $user = Auth::user();
        if ($user && $user->ispasswordchange == 0) {
            return Redirect::route('sientistchangepassword');
        }

        $sectionid = $request->input('sectionid');
     
        $lists = Researchinterest::join('sections', 'sections.id', '=', 'researchinterests.sectionid')
            ->where(['userid' => Auth::id(), 'researchinterests.type' => 'researchbiodata', 'researchinterests.sectionid' => $sectionid])
            ->select(['sections.sectionname', 'researchinterests.*']);
        if (request('search')) {
            $lists->where('researchinterests.description', 'LIKE', '%' . request('search') . '%');
        }
        $lists = $lists->orderBy('sortorder', 'ASC')->paginate(50);
        // dd($lists); 
        return view('myadmin.scientists.researchbiodataawardshtml', [
            'lists' => $lists,
            'sectionid' => $sectionid,
            'search' => request('search'),
            'heading' => ($sectionid == 10 ? 'Research Experience' : 'Awards & Honours')
        ]);
    }

    public function createbiodataresearchespawards(Request $request): View|Factory
    {
        $info = [];
        if (!empty($request->input('tokenid')) && !empty($request->input('sectionid'))) {
            $info = Researchinterest::where([
                'userid' => Auth::id(),
                'id' => $request->input('tokenid'),
                'sectionid' => $request->input('sectionid'),
            ])->first();
        }
        return view('myadmin.scientists.modals.create_researchbiodataawardshtml', [
            'info' => $info,
            'sectionid' => $request->input('sectionid'),
            'statusArrays' => $this->statusArrays,
            'heading' => ($request->input('sectionid') == 10 ? 'Research Experience' : 'Awards & Honours')
        ]);
    }

    public function biodataresearchegrant(Request $request): View|Factory|RedirectResponse
    {
        if (!Auth::check()) {
            return Redirect::route('login');
        }

        $user = Auth::user();
        if ($user && $user->ispasswordchange == 0) {
            return Redirect::route('sientistchangepassword');
        }

        $lists = Researchinterest::join('sections', 'sections.id', '=', 'researchinterests.sectionid')
            ->where(['userid' => Auth::id(), 'researchinterests.type' => 'researchbiodata', 'researchinterests.sectionid' => $this->GrantSectionId])
            ->select(['sections.sectionname', 'researchinterests.*']);
        if (request('search')) {
            $lists->where('researchinterests.description', 'LIKE', '%' . request('search') . '%');
        }
        $lists = $lists->orderBy('sortorder', 'ASC')->paginate(50);
        return view('myadmin.scientists.biodataresearchegranthtml', [
            'lists' => $lists,
            'search' => request('search'),
            'heading' => 'Research Grants Secured'
        ]);
    }

    public function createbiodataresearchegrant(Request $request): View|Factory
    {
        $info = [];
        if (!empty($request->input('tokenid'))) {
            $info = Researchinterest::where([
                'userid' => Auth::id(),
                'id' => $request->input('tokenid'),
                'sectionid' => $this->GrantSectionId,
            ])->first();
        }
        return view('myadmin.scientists.modals.create_biodataresearchegranthtml', [
            'info' => $info,
            'statusArrays' => $this->statusArrays,
            'sectionid' => $this->GrantSectionId,
            'heading' => 'Research Grants Secured'
        ]);
    }

    public function scientistUpdateOrder(Request $request): JsonResponse
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

        return Response::json(['status' => 'success']);
    }
}
