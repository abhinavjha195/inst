<?php

namespace App\Http\Controllers\myadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\myadmin\Coordinator;
use App\Models\myadmin\Albumimage;
use App\Models\Section;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Contracts\View\Factory;

class CoordinatorController extends Controller
{
    public function __construct()
    {
        $this->statusArrays = array('' => 'Status', 'inactive' => 'inActive', 'active' => 'Active');
        $this->alumniArrays = array('' => 'select', 'real' => 'Real Life', 'stories' => 'Alumni Stories');
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = $request->validate(
            [
                'name' => 'required|max:500',
                'isactive' => 'required'
            ],
            [
                'name.required' => 'The name is required',
                'isactive.required' => 'The status is required',
            ]
        );
        $coordinator = new Coordinator();
        $coordinator->name = $request->input('name');
        $coordinator->subtitle = $request->input('subtitle');
        $coordinator->designation = $request->input('designation');
        $coordinator->description = $request->input('description');
        $coordinator->isactive = $request->input('isactive');
        $coordinator->type = $request->input('type');
        $coordinator->extrainfo = $request->input('extrainfo');
        $coordinator->make = $request->input('make');
        $coordinator->model = $request->input('model');
        $coordinator->catid = $request->input('catid');
        $coordinator->user_id = Auth()->id();
        if (!empty($request->input('postenddate'))) {
            $coordinator->postenddate = convertdate($request->input('postenddate'), 'Y-m-d H:i:s');
        }
        if (!empty($request->input('postdate'))) {
            $coordinator->postdate = convertdate($request->input('postdate'), 'Y-m-d H:i:s');
        }
        $featureimageName = '';
        $pdfoneName = '';
        $pdftwoName = '';
        $image_name = '';



        if (!empty($request->file('pdfone'))) {
            $pdfone = $request->file('pdfone');
            $pdfoneName = Auth()->id() . '_' . time() . '.' . $pdfone->extension();
            $pdfone->move(public_path('uploads'), $pdfoneName);
        }
        if (!empty($request->file('pdftwo'))) {
            $pdftwo = $request->file('pdftwo');
            $pdftwoName = Auth()->id() . '_' . time() . '.' . $pdftwo->extension();
            $pdftwo->move(public_path('uploads'), $pdftwoName);
        }
        if (!empty($pdfoneName)) {
            $coordinator->pdfone = $pdfoneName;
        }
        if (!empty($pdftwoName)) {
            $coordinator->pdftwo = $pdftwoName;
        }
        if (!empty($request->file('feature_img'))) {
            $feature_img = $request->file('feature_img');

            $image_name = Auth()->id() . '_' . time() . '.' . $feature_img->extension();
            $feature_img->move(public_path('uploads/images'), $image_name);
        }



        if (!empty($image_name)) {
            $coordinator->feature_image = $image_name;
        }

        $coordinator->save();



        if (!empty($request->file('feature_image'))) {
            $images = $request->file('feature_image');
            $i = 1;
            foreach ($images as $key => $image) {

                $imageName = $key . '_' . uniqid() . '.' . $image->extension();
                $image->move(public_path('uploads/images'), $imageName);
                $addimg = new Albumimage();
                $addimg->tititle = 'title';
                $addimg->feature_image = $imageName;
                $addimg->order = $i;
                $addimg->albumid = $coordinator->id;
                $addimg->save();
                $i++;
            }
        }
        if ($request->input('type') == 'admissions') {
            return Redirect::route('editadmissions', ['id' => $coordinator->id])->with('status', ' Content has been saved successfully');
        } else if ($request->input('type') == 'tenders') {
            return Redirect::route('edittenders', ['id' => $coordinator->id])->with('status', ' Content has been saved successfully');
        } else {
            return Redirect::route($request->input('type'))->with('status', ' Content has been saved successfully');
        }
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $request_data = $request->all();
        $validator = $request->validate(
            [
                'name' => 'required|max:500',
                'isactive' => 'required'
            ],
            [
                'name.required' => 'The name is required',
                'isactive.required' => 'The status is required',
            ]
        );

        $coordinator = Coordinator::find($id);
        if ($coordinator) {
            $coordinator->name = $request->input('name');
            $coordinator->subtitle = $request->input('subtitle');
            $coordinator->designation = $request->input('designation');
            $coordinator->description = $request->input('description');
            $coordinator->descriptionOne = $request->input('descriptionOne');
            $coordinator->isactive = $request->input('isactive');
            $coordinator->type = $request->input('type');
            $coordinator->extrainfo = $request->input('extrainfo');
            $coordinator->make = $request->input('make');
            $coordinator->model = $request->input('model');
            $coordinator->catid = $request->input('catid');
            $coordinator->user_id = Auth()->id();
            if (!empty($request->input('postenddate'))) {
                $coordinator->postenddate = convertdate($request->input('postenddate'), 'Y-m-d H:i:s');
            }
            if (!empty($request->input('postdate'))) {
                $coordinator->postdate = convertdate($request->input('postdate'), 'Y-m-d H:i:s');
            }
            $featureimageName = '';
            $pdfoneName = '';
            $pdftwoName = '';
            $image_name = '';

            if (!empty($request->file('pdfone'))) {
                $pdfone = $request->file('pdfone');
                $pdfoneName = Auth()->id() . '_' . time() . '.' . $pdfone->extension();
                $pdfone->move(public_path('uploads'), $pdfoneName);
            }
            if (!empty($request->file('pdftwo'))) {
                $pdftwo = $request->file('pdftwo');
                $pdftwoName = Auth()->id() . '_' . time() . '.' . $pdftwo->extension();
                $pdftwo->move(public_path('uploads'), $pdftwoName);
            }
            if (!empty($pdfoneName)) {
                $coordinator->pdfone = $pdfoneName;
            }
            if (!empty($pdftwoName)) {
                $coordinator->pdftwo = $pdftwoName;
            }

            if (!empty($request->file('feature_img'))) {
                $feature_img = $request->file('feature_img');
                $image_name = Auth()->id() . '_' . time() . '.' . $feature_img->extension();
                $feature_img->move(public_path('uploads'), $image_name);
            }
            if (!empty($image_name)) {
                $coordinator->feature_img = $image_name;
            }

            $coordinator->save();

            if (!empty($request->file('feature_image'))) {
                $images = $request->file('feature_image');
                $order = Albumimage::where('albumid', $id)->orderBy('id', 'desc')->first();
                $i = 1;
                foreach ($images as $key => $image) {
                    if (!empty($order)) {
                        $sortorder = $order['order'] + $i;
                    } else {
                        $sortorder = $i;
                    }

                    $imageName = $key . '_' . uniqid() . '.' . $image->extension();
                    $image->move(public_path('uploads/images'), $imageName);
                    $addimg = new Albumimage();
                    $addimg->tititle = 'title';
                    $addimg->order = $sortorder;

                    $addimg->feature_image = $imageName;
                    $addimg->albumid = $id;
                    $addimg->save();
                    $i++;
                }
            }

            if ($request->input('type') == 'admissions') {
                return Redirect::route('editadmissions', ['id' => $coordinator->id])->with('status', ' Content has been saved successfully');
            } else if ($request->input('type') == 'tenders') {
                return Redirect::route('edittenders', ['id' => $coordinator->id])->with('status', ' Content has been saved successfully');
            } else {
                return Redirect::route($request->input('type'))->with('status', ' Content has been saved successfully');
            }
        } else {
            return Redirect::route($request->input('type'))->with('status', 'Mentioned Id does not exist.');
        }
    }
    public function updatedeans(Request $request, $id): RedirectResponse
    {
        $request_data = $request->all();
        $validator = $request->validate(
            [
                'name' => 'required|max:500',
                'isactive' => 'required'
            ],
            [
                'name.required' => 'The name is required',
                'isactive.required' => 'The status is required',
            ]
        );

        $coordinator = Coordinator::find($id);
        if ($coordinator) {
            $coordinator->name = $request->input('name');
            $coordinator->designation = $request->input('designation');
            $coordinator->description = $request->input('description');
            $coordinator->descriptionOne = $request->input('descriptionOne');
            $coordinator->isactive = $request->input('isactive');
            $coordinator->type = $request->input('type');
            $coordinator->extrainfo = $request->input('extrainfo');
            $coordinator->make = $request->input('make');
            $coordinator->model = $request->input('model');
            $coordinator->catid = $request->input('catid');
            $coordinator->user_id = Auth()->id();
            if (!empty($request->input('postenddate'))) {
                $coordinator->postenddate = convertdate($request->input('postenddate'), 'Y-m-d H:i:s');
            }
            if (!empty($request->input('postdate'))) {
                $coordinator->postdate = convertdate($request->input('postdate'), 'Y-m-d H:i:s');
            }
            if (!empty($request->file('feature_image'))) {
                $feature_img = $request->file('feature_image');
                $image_name = Auth()->id() . '_' . time() . '.' . $feature_img->extension();
                $feature_img->move(public_path('uploads/images'), $image_name);
                $coordinator->feature_image = $image_name;
            }

            $coordinator->save();

            if ($request->input('type') == 'admissions') {
                return Redirect::route('editadmissions', ['id' => $coordinator->id])->with('status', ' Content has been saved successfully');
            } else if ($request->input('type') == 'tenders') {
                return Redirect::route('edittenders', ['id' => $coordinator->id])->with('status', ' Content has been saved successfully');
            } else {
                return Redirect::route($request->input('type'))->with('status', ' Content has been saved successfully');
            }
        } else {
            return Redirect::route($request->input('type'))->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function removeBanner(Request $request): JsonResponse
    {
        $image = $request->input('image');
        if (!empty($image)) {
            $result = Coordinator::where('feature_img', $image)
                ->select('feature_img')
                ->first();
            if ($result) {
                if ($result->feature_img == $image) {
                    $field = 'feature_img';
                } else {
                    $field = 'feature_img';
                }

                Coordinator::where($field, $image)->update([$field => null]);

                return Redirect::route('editlatestupdates')->with('status', 'Banner remove Successfully.');
            }
        }

        return Response::json(['success' => false]);
    }

    public function indexannualreports(Request $request): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $search = $request->query('search');
        $visionary = Coordinator::query();
        if (request('search')) {
            $visionary->where('name', 'Like', '%' . request('search') . '%');
        }
        $infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'annualreports')->paginate(20);
        return view('myadmin.annualreports.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Annual Reports : ' . $infrastructure->count() . ' Records found']);
    }
    public function createannualreports(): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        return view('myadmin.annualreports.createhtml', ['heading' => 'Annual Reports'])->with('statusArrays', $this->statusArrays);
    }
    public function editannualreports(Request $request, $id): View|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.annualreports.edithtml')
                ->with('heading', 'Annual Reports')
                ->with('statusArrays', $this->statusArrays)
                ->with('info', $coordinators);
        } else {
            return Redirect::route('annual-reports')->with('status', 'Mentioned Id does not exist.');
        }
    }
    public function indexannouncements(Request $request): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $search = $request->query('search');
        $visionary = Coordinator::query();
        if (request('search')) {
            $visionary->where('name', 'Like', '%' . request('search') . '%');
        }
        $infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'announcements')->paginate(20);
        return view('myadmin.announcements.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Announcements : ' . $infrastructure->count() . ' Records found']);
    }
    public function createannouncements(): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        return view('myadmin.announcements.createhtml', ['heading' => 'Announcements'])->with('statusArrays', $this->statusArrays);
    }
    public function editannouncements(Request $request, $id): View|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.announcements.edithtml')
                ->with('statusArrays', $this->statusArrays)
                ->with('heading', 'Announcements')
                ->with('info', $coordinators);
        } else {
            return Redirect::route('announcements')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function indexnewsupdates(Request $request): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $search = $request->query('search');
        $visionary = Coordinator::query();
        if (request('search')) {
            $visionary->where('name', 'Like', '%' . request('search') . '%');
        }
        $infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'newsupdates')->paginate(20);
        return view('myadmin.newsupdates.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'News & Updates : ' . $infrastructure->count() . ' Records found']);
    }
    public function createnewsupdates(): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        return view('myadmin.newsupdates.createhtml', ['heading' => 'Create News & Updates'])->with('statusArrays', $this->statusArrays);
    }
    public function editnewsupdates(Request $request, $id): View|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.newsupdates.edithtml')
                ->with('statusArrays', $this->statusArrays)
                ->with('heading', 'Edit News & Updates')
                ->with('info', $coordinators);
        } else {
            return Redirect::route('announcements')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function indexlatestupdates(Request $request): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $search = $request->query('search');
        $visionary = Coordinator::query();
        if (request('search')) {
            $visionary->where('name', 'Like', '%' . request('search') . '%');
        }
        $infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'latestupdates')->paginate(20);
        return view('myadmin.latestupdates.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Latest Updates : ' . $infrastructure->count() . ' Records found']);
    }
    public function createlatestupdates(): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        return view('myadmin.latestupdates.createhtml', ['heading' => 'Create Latest Updates'])->with('statusArrays', $this->statusArrays);
    }
    public function editlatestupdates(Request $request, $id): View|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $coordinators = Coordinator::where('id', $id)->first();

        $featureimg = Albumimage::where('albumid', $id)->orderBy('order', 'ASC')->get();
        if ($coordinators) {
            return view('myadmin.latestupdates.edithtml')
                ->with('statusArrays', $this->statusArrays)
                ->with('heading', 'Edit Latest Updates')->with('featureimg', $featureimg)
                ->with('info', $coordinators);
        } else {
            return Redirect::route('announcements')->with('status', 'Mentioned Id does not exist.');
        }
    }
    public function indexadminstaff(Request $request): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $search = $request->query('search');
        $visionary = Coordinator::query();
        if (request('search')) {
            $visionary->where('name', 'Like', '%' . request('search') . '%');
        }
        $infrastructure =  $visionary->orderBy('order', 'ASC')->where('type', 'adminstaff')->paginate(20);
        return view('myadmin.admstaffs.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Adminstrative Staff Information : ' . $infrastructure->count() . ' Records found']);
    }
    public function createadminstaff(): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'adminstaff')->get();
        return view('myadmin.admstaffs.createhtml', ['heading' => 'Add Staff Information', 'sections' => $sections])->with('statusArrays', $this->statusArrays);
    }
    public function editadminstaff(Request $request, $id): View|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'adminstaff')->get();
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.admstaffs.edithtml')
                ->with('statusArrays', $this->statusArrays)
                ->with('heading', 'Edit Staff Information')
                ->with('sections', $sections)
                ->with('info', $coordinators);
        } else {
            return Redirect::route('admstaffs')->with('status', 'Mentioned Id does not exist.');
        }
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function adminsataffUpdateOrder(Request $request): JsonResponse
    {
        $orders = $request->input('order');
        foreach ($orders as $order) {
            $id = $order['id'];
            $position = $order['position'];
            $type = $order['type'];

            $list = Coordinator::where('type', $type)
                ->where('id', $id)
                ->firstOrFail();
            $list->order = $position;
            $list->save();
        }


        return Response::json(['status' => 'success']);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function indexalbums(Request $request): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $search = $request->query('search');
        $visionary = Coordinator::query();
        if (request('search')) {
            $visionary->where('name', 'Like', '%' . request('search') . '%');
        }
        $infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'albums')->paginate(20);
        return view('myadmin.albums.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Campus Tours : ' . $infrastructure->count() . ' Records found']);
    }
    public function createalbums(): View
    {
        return view('myadmin.albums.createhtml', ['heading' => 'Campus Tours'])->with('statusArrays', $this->statusArrays);
    }
    public function editalbums(Request $request, $id): View|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.albums.edithtml')
                ->with('statusArrays', $this->statusArrays)
                ->with('heading', 'Campus Tours')
                ->with('info', $coordinators);
        } else {
            return Redirect::route('albums')->with('status', 'Mentioned Id does not exist.');
        }
    }
    public function indexbogs(Request $request): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $search = $request->query('search');
        $visionary = Coordinator::query();
        if (request('search')) {
            $visionary->where('name', 'Like', '%' . request('search') . '%');
        }
        $infrastructure =  $visionary->orderBy('order', 'ASC')->where('type', 'bogs')->paginate(40);
        return view('myadmin.bogs.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Board Of Governors : ' . $infrastructure->count() . ' Records found']);
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////
    public function blogUpdateOrders(Request $request): JsonResponse
    {
        $orders = $request->input('order');
        foreach ($orders as $order) {
            $id = $order['id'];
            $position = $order['position'];

            $list = Coordinator::where('type', 'bogs')
                ->where('id', $id)
                ->firstOrFail();
            $list->order = $position;
            $list->save();
        }


        return Response::json(['status' => 'success']);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////
    public function createbogs(): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'bogs')->get();
        return view('myadmin.bogs.createhtml', ['heading' => 'Board Of Governors', 'sections' => $sections])->with('statusArrays', $this->statusArrays);
    }
    public function editbogs(Request $request, $id): View|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'bogs')->get();
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.bogs.edithtml')
                ->with('statusArrays', $this->statusArrays)
                ->with('heading', 'Board Of Governors')
                ->with('sections', $sections)
                ->with('info', $coordinators);
        } else {
            return Redirect::route('bogs')->with('status', 'Mentioned Id does not exist.');
        }
    }
    public function indexraac(Request $request): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $search = $request->query('search');
        $visionary = Coordinator::query();
        if (request('search')) {
            $visionary->where('name', 'Like', '%' . request('search') . '%');
        }
        $infrastructure =  $visionary->orderBy('order', 'ASC')->where('type', 'raac')->paginate(40);
        return view('myadmin.raac.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Research and Academic Advisory Council: ' . $infrastructure->count() . ' Records found']);
    }
    ////////////////////////////////////////////////////////////////////////////////////
    public function raacUpdateOrders(Request $request): JsonResponse
    {
        $orders = $request->input('order');
        foreach ($orders as $order) {
            $id = $order['id'];
            $position = $order['position'];

            $list = Coordinator::where('type', 'raac')
                ->where('id', $id)
                ->firstOrFail();
            $list->order = $position;
            $list->save();
        }


        return Response::json(['status' => 'success']);
    }
    ////////////////////////////////////////////////////////////////////////////////////
    public function createraac(): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'raac')->get();
        return view('myadmin.raac.createhtml', ['heading' => 'Research and Academic Advisory Council', 'sections' => $sections])->with('statusArrays', $this->statusArrays);
    }
    public function editraac(Request $request, $id): View|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'raac')->get();
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.raac.edithtml')
                ->with('statusArrays', $this->statusArrays)
                ->with('heading', 'Research and Academic Advisory Council')
                ->with('sections', $sections)
                ->with('info', $coordinators);
        } else {
            return Redirect::route('raac')->with('status', 'Mentioned Id does not exist.');
        }
    }
    public function indexphpadmissions(Request $request): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $search = $request->query('search');
        $visionary = Coordinator::query();
        if (request('search')) {
            $visionary->where('name', 'Like', '%' . request('search') . '%');
        }
        $infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'admissions')->paginate(20);
        return view('myadmin.admissions.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Admissions : ' . $infrastructure->count() . ' Records found']);
    }
    public function createadmissions(): View
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'admissions')->get();
        $scientists = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.*', 'userdetails.designation'])->where('users.roles', 'scientists')->orderBy('name', 'ASC')->get();
        return view('myadmin.admissions.createhtml', [
            'heading' => 'Admissions',
            'sections' => $sections,
            'scientists' => $scientists
        ])->with('statusArrays', $this->statusArrays);
    }
    public function editadmissions(Request $request, $id): View|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $scientists = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.*', 'userdetails.designation'])->where('users.roles', 'scientists')->orderBy('name', 'ASC')->get();
        $pdflists = Albumimage::where('albumid', $id)->orderBy('order', 'ASC')->get();
        $sections = Section::where('isactive', 1)->where('type', 'admissions')->get();
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.admissions.edithtml')
                ->with('heading', 'Admissions')
                ->with('statusArrays', $this->statusArrays)
                ->with('sections', $sections)
                ->with('scientists', $scientists)
                ->with('pdflists', $pdflists)
                ->with('info', $coordinators);
        } else {
            return Redirect::route('admissions')->with('status', 'Mentioned Id does not exist.');
        }
    }
    //////////////////////////////////////////////////////////////////
    public function admissionsUpdateOrders(Request $request): JsonResponse
    {
        $orders = $request->input('order');
        foreach ($orders as $order) {
            $id = $order['id'];
            $position = $order['position'];
            $albumid = $order['albumid'];

            $list = Albumimage::where('albumid', $albumid)
                ->where('id', $id)
                ->firstOrFail();
            $list->order = $position;
            $list->save();
        }


        return Response::json(['status' => 'success']);
    }
    ///////////////////////////////////////////////////////////////
    public function editalbumimage_direct(Request $request, $id): View
    {
        $album = Albumimage::find($id);
        return view('myadmin.admissions.modals.editadmissionhtml', ['album' => $album]);
    }
    public function updatealbumimage_direct(Request $request): RedirectResponse
    {
        $validator = $request->validate([
            'albumid' => 'required|max:100',
            'attachmentname' => 'required',
            'attachmentfile' => 'mimes:pdf|max:50000'
        ], [
            'albumid.required' => 'The album name is required',
            'attachmentname.required' => 'The PDF is required',
            'attachmentfile.required' => 'The PDF is required & size should be less than 5MB',
        ]);

        $attachmentfileName = '';
        if (!empty($request->file('attachmentfile'))) {
            $attachmentfile = $request->file('attachmentfile');
            $attachmentfileName = $request->albumid . '_' . time() . '.' . $attachmentfile->extension();
            $attachmentfile->move(public_path('userpics'), $attachmentfileName);
        }

        $userDetailsInfo = Albumimage::find($request->albumid);
        // dd($userDetailsInfo);
        $userDetailsInfo->feature_image = $request->attachmentfile;
        $userDetailsInfo->tititle = $request->attachmentname;
        // dd($userDetailsInfo->attachmentname);
        $userDetailsInfo->isactive = $request->isactive;

        if (!empty($attachmentfileName)) {
            $userDetailsInfo->feature_image = $attachmentfileName;
        }

        $saved = $userDetailsInfo->save();
        return Redirect::route('admissions', ['id' => $request->albumid])->with('status', ' Content has been updated successfully');
    }

    ////////////////////////////////////////////////////////////////////
    public function filemanager(Request $request): JsonResponse
    {
        // Get the uploaded file from the request
        $file = $request->file('file');

        $filename = time() . '_' . $file->getClientOriginalName();

        $file->move(public_path('userpics'), $filename);

        $url = asset('userpics/' . $filename);
// dd($url);
		// Return the URL of the saved image
		return Response::json(['location' => $url]);
	}

	////////////////////////////////////////////////////////////////////
	public function indexmou(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'mou')->paginate(20);
		return view('myadmin.mou.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Memorandum Of Understanding : ' . $infrastructure->count() . ' Records found']);
	}
	public function createmou()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		return view('myadmin.mou.createhtml', ['heading' => 'Memorandum Of Understanding'])->with('statusArrays', $this->statusArrays);
	}
	public function editmou(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$coordinators = Coordinator::where('id', $id)->first();
		if ($coordinators) {
			return view('myadmin.mou.edithtml')
				->with('heading', 'Memorandum Of Understanding')
				->with('statusArrays', $this->statusArrays)
				->with('info', $coordinators);
		} else {
			return redirect()->route('mou')->with('status', 'Mentioned Id does not exist.');
		}
	}
	public function indexhonoraryadjunctfaculty(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::join('sections', 'sections.id', '=', 'coordinators.catid')->select(['sections.sectionname', 'coordinators.*']);
		if (request('search')) {
			$visionary->where('coordinators.name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('order', 'ASC')->where('coordinators.type', 'honoraryadjunctfaculty')->paginate(20);
		return view('myadmin.honoraryadjunctfaculty.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Honorary and Adjunct Faculty : ' . $infrastructure->count() . ' Records found']);
	}
	/////////////////////////////////////////////////////////////////////////
	public function honorarysortorder(Request $request)
	{
		$orders = $request->input('order');
		foreach ($orders as $order) {
			$id = $order['id'];
			$position = $order['position'];

			$list = Coordinator::where('type', 'honoraryadjunctfaculty')
				->where('id', $id)
				->firstOrFail();
			$list->order = $position;
			$list->save();
		}


		return Response::json(['status' => 'success']);
	}
	/////////////////////////////////////////////////////////////////////////
	public function createhonoraryadjunctfaculty()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$sections = Section::where('isactive', 1)->where('type', 'honoraryadjunctfaculty')->get();
		return view('myadmin.honoraryadjunctfaculty.createhtml', [
			'sections' => $sections,
			'heading' => 'Create Honorary and Adjunct Faculty',
		])->with('statusArrays', $this->statusArrays);
	}
	public function edithonoraryadjunctfaculty(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$sections = Section::where('isactive', 1)->where('type', 'honoraryadjunctfaculty')->get();
		$coordinators = Coordinator::where('id', $id)->first();
		if ($coordinators) {
			return view('myadmin.honoraryadjunctfaculty.edithtml')
				->with('statusArrays', $this->statusArrays)
				->with('heading', 'Edit Honorary and Adjunct Faculty')
				->with('sections', $sections)
				->with('info', $coordinators);
		} else {
			return redirect()->route('honoraryadjunctfaculty')->with('status', 'Mentioned Id does not exist.');
		}
	}
	public function tenders(Request $request)
	{

		$infrastructure =  Coordinator::where('type', 'tenders')->get();
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}


		$infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'tenders')->paginate(20);
		return view('myadmin.tenders.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Tenders : ' . $infrastructure->count() . ' Records found']);
	}
	public function createtenders()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$sections = Section::where('isactive', 1)->where('type', 'tenders')->get();
		return view('myadmin.tenders.createhtml', [
			'heading' => 'Tenders',
			'sections' => $sections
		])->with('statusArrays', $this->statusArrays);
	}
	public function edittenders(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$pdflists = Albumimage::where('albumid', $id)->orderBy('order', 'ASC')->get();
		$sections = Section::where('isactive', 1)->where('type', 'tenders')->get();
		$coordinators = Coordinator::where('id', $id)->first();
		if ($coordinators) {
			return view('myadmin.tenders.edithtml')
				->with('heading', 'Tenders')
				->with('statusArrays', $this->statusArrays)
				->with('sections', $sections)
				->with('pdflists', $pdflists)
				->with('info', $coordinators);
		} else {
			return redirect()->route('tenders')->with('status', 'Mentioned Id does not exist.');
		}
	}
	/////////////////////////////////////////////////////////////
	public function tenderUpdateOrders(Request $request)
	{
		$orders = $request->input('order');
		foreach ($orders as $order) {
			$id = $order['id'];
			$position = $order['position'];
			$albumid = $order['albumid'];

			$list = Albumimage::where('albumid', $albumid)
				->where('id', $id)
				->firstOrFail();
			$list->order = $position;
			$list->save();
		}


		return Response::json(['status' => 'success']);
	}
	/////////////////////////////////////////////////////////////
	public function edittenders_direct(Request $request, $id)
	{
		// Retrieve the album with the given ID
		$album = Albumimage::find($id);
		// dd($album);

		// Return the view for the edit page, passing the album object as data
		return view('myadmin.tenders.modals.edittendershtml', ['album' => $album]);
	}

	public function updatetenders_direct(Request $request)
	{
		$validator = $request->validate([
			'albumid' => 'required|max:100',
			'attachmentname' => 'required',
			'attachmentfile' => 'max:50000'
		], [
			'albumid.required' => 'The album name is required',
			'attachmentname.required' => 'The PDF is required',
			'attachmentfile.required' => 'The PDF is required & size should be less than 5MB',
		]);

		$attachmentfileName = '';
		if (!empty($request->file('attachmentfile'))) {
			$attachmentfile = $request->file('attachmentfile');
			$attachmentfileName = $request->albumid . '_' . time() . '.' . $attachmentfile->extension();
			$attachmentfile->move(public_path('userpics'), $attachmentfileName);
		}

		$userDetailsInfo = Albumimage::find($request->albumid);
		// dd($userDetailsInfo);
		$userDetailsInfo->feature_image = $request->attachmentfile;
		$userDetailsInfo->tititle = $request->attachmentname;
		// dd($userDetailsInfo->attachmentname);
		$userDetailsInfo->isactive = $request->isactive;

		if (!empty($attachmentfileName)) {
			$userDetailsInfo->feature_image = $attachmentfileName;
		}

		$saved = $userDetailsInfo->save();
		return redirect()->route('tenders', ['id' => $request->albumid])->with('status', ' Content has been updated successfully');
	}
	////////////////////////////////////////////////////////////
	public function deletesinglecordinator(Request $request)
	{
		$ids = $request->id;
		$tag = $request->tag;
		Coordinator::whereIn('id', $ids)->delete();
		return Response::json(['status' => true]);
	}
	public function indexcif(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('id', 'DESC')->where('type', 'cif')->paginate(20);
		return view('myadmin.cif.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Central Instrument Facility : ' . $infrastructure->count() . ' Records found']);
	}
	public function createcif()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		return view('myadmin.cif.createhtml', ['heading' => 'Central Instrument Facility'])->with('statusArrays', $this->statusArrays);
	}
	public function editcif(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$coordinators = Coordinator::where('id', $id)->first();
		if ($coordinators) {
			return view('myadmin.cif.edithtml')
				->with('heading', 'Central Instrument Facility')
				->with('statusArrays', $this->statusArrays)
				->with('info', $coordinators);
		} else {
			return redirect()->route('cif')->with('status', 'Mentioned Id does not exist.');
		}
	}
	public function indextechnology(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'technology')->paginate(20);
		return view('myadmin.technology.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Technology : ' . $infrastructure->count() . ' Records found']);
	}
	public function createtechnology()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		return view('myadmin.technology.createhtml', ['heading' => 'Technology'])->with('statusArrays', $this->statusArrays);
	}
	public function edittechnology(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$coordinators = Coordinator::where('id', $id)->first();
		if ($coordinators) {
			return view('myadmin.technology.edithtml')
				->with('heading', 'Technology')
				->with('statusArrays', $this->statusArrays)
				->with('info', $coordinators);
		} else {
			return redirect()->route('technology')->with('status', 'Mentioned Id does not exist.');
		}
	}
	public function indexdeans(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('id', 'DESC')->where('type', 'deans')->paginate(20);
		return view('myadmin.deans.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Deans Information : ' . $infrastructure->count() . ' Records found']);
	}
	public function createdeans()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		return view('myadmin.deans.createhtml', ['heading' => 'Add Dean Information'])->with('statusArrays', $this->statusArrays);
	}
	public function editdeans(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$coordinators = Coordinator::where('id', $id)->first();
		if ($coordinators) {
			return view('myadmin.deans.edithtml')
				->with('statusArrays', $this->statusArrays)
				->with('heading', 'Edit Deans Information')
				->with('info', $coordinators);
		} else {
			return redirect()->route('deans')->with('status', 'Mentioned Id does not exist.');
		}
	}





































	public function index(Request $request)
	{
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('id', 'DESC')->where('type', 'infrastructure')->paginate(20);
		return view('myadmin.infrastructure.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Infrastructure : ' . $infrastructure->count() . ' Records found']);
	}
	public function create()
	{
		return view('myadmin.infrastructure.createhtml')->with('statusArrays', $this->statusArrays);
	}
	public function edit(Request $request, $id)
	{
		$coordinators = Coordinator::where('id', $id)->first();
		if ($coordinators) {
			return view('myadmin.infrastructure.edithtml')
				->with('statusArrays', $this->statusArrays)
				->with('info', $coordinators);
		} else {
			return redirect()->route('infrastructure')->with('status', 'Mentioned Id does not exist.');
		}
	}

	public function deleterecords(Request $request)
	{
		$ids = $request->post_id;
		Coordinator::whereIn('id', $ids)->delete();
		return Response::json(['status' => true]);
	}
	public function infrastructurestatus(Request $request)
	{
		$pageids = $request->post_id;
		$status_type = $request->status_type;
		Coordinator::whereIn('id', $pageids)->update(['isactive' => $status_type]);
		return Response::json(['status' => true]);
	}
	/**********infrastructure**************/


	public function indexevents(Request $request)
	{
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('id', 'DESC')->where('type', 'events')->paginate(20);
		return view('myadmin.events_initiatives.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Events : ' . $infrastructure->count() . ' Records found']);
	}
	public function createevents()
	{
		return view('myadmin.events_initiatives.createhtml')->with('statusArrays', $this->statusArrays)->with('statusArrays', $this->statusArrays);
	}
	public function editevents(Request $request, $id)
	{
		$coordinators = Coordinator::where('id', $id)->first();
		if ($coordinators) {
			return view('myadmin.events_initiatives.edithtml')
				->with('statusArrays', $this->statusArrays)
				->with('info', $coordinators);
		} else {
			return redirect()->route('events')->with('status', 'Mentioned Id does not exist.');
		}
	}


	/**********Events & Initiatives**************/





	/**********Achievements**************/




	/**********awards**************/


	public function indexalumni(Request $request)
	{
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('id', 'DESC')->where('type', 'alumni')->paginate(20);
		return view('myadmin.alumni.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Alumni : ' . $infrastructure->count() . ' Records found']);
	}
	public function createalumni()
	{
		return view('myadmin.alumni.createhtml')->with('statusArrays', $this->statusArrays)->with('alumniArrays', $this->alumniArrays);
	}
	public function editalumni(Request $request, $id)
	{
		$coordinators = Coordinator::where('id', $id)->first();
		if ($coordinators) {
			return view('myadmin.alumni.edithtml')
				->with('statusArrays', $this->statusArrays)
				->with('alumniArrays', $this->alumniArrays)
				->with('info', $coordinators);
		} else {
			return redirect()->route('alumni')->with('status', 'Mentioned Id does not exist.');
			return redirect()->route('alumni')->with('status', 'Mentioned Id does not exist.');
		}
	}

	public function addmissionUpdateOrder(Request $request)
	{

		$orders = $request->input('order');

		foreach ($orders as $order) {

			$id = $order['id'];
			$position = $order['position'];
			$type = $order['type'];

			$list = Coordinator::where('id', $id)->where('type', $type)
				->firstOrFail();
			$list->sortorder = $position;
			$list->save();
		}
		// die;

		return Response::json(['status' => 'success']);
	}

	public function deletelatestupdatesImg($id)
	{
		Albumimage::where('id', $id)->delete();

		return Response::json([
			'success' => true,
			'message' => 'feature Image Deleted Successfully'
		]);
	}

	public function swapTwoTenders(Request $request)
	{
		$orders = $request->input('order');
	}

	public function swapLatestupdateImages(Request $request)
	{
		$images = $request->input('imageIds');
		$id1 = $images[0]['id'];
		$position1 = $images[0]['position'];

		$id2 = $images[1]['id'];
		$position2 = $images[1]['position'];

		Albumimage::where('id', $id1)->update(['order' => $position2]);
		Albumimage::where('id', $id2)->update(['order' => $position1]);

		return Response::json([
			'success' => true,
			'message' => 'feature Images swaped Successfully'
		]);
	}


	public function medialinks(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('id', 'DESC')->where('type', 'medialinks')->paginate(20);
		return view('myadmin.medialinks.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Media Links : ' . $infrastructure->count() . ' Records found']);
	}
	public function medialinkscreate()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		return view('myadmin.medialinks.createhtml', ['heading' => 'Create Media Links'])->with('statusArrays', $this->statusArrays);
	}
}
