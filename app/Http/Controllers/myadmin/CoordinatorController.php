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
    /**
     * @var array<string, string>
     */
    public array $statusArrays;

    /**
     * @var array<string, string>
     */
    public array $alumniArrays;

    public function __construct()
    {
        $this->statusArrays = ['' => 'Status', 'inactive' => 'inActive', 'active' => 'Active'];
        $this->alumniArrays = ['' => 'select', 'real' => 'Real Life', 'stories' => 'Alumni Stories'];
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

    public function update(Request $request, int $id): RedirectResponse
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
    public function updatedeans(Request $request, int $id): RedirectResponse
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

    public function indexannualreports(Request $request): View|Factory|RedirectResponse
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
        return view('myadmin.annualreports.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Annual Reports : ' . $infrastructure->total() . ' Records found']);
    }
    public function createannualreports(): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        return view('myadmin.annualreports.createhtml', ['heading' => 'Annual Reports', 'statusArrays' => $this->statusArrays]);
    }
    public function editannualreports(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.annualreports.edithtml', [
                'heading' => 'Annual Reports',
                'statusArrays' => $this->statusArrays,
                'info' => $coordinators
            ]);
        } else {
            return Redirect::route('annual-reports')->with('status', 'Mentioned Id does not exist.');
        }
    }





    
    public function indexannouncements(Request $request): View|Factory|RedirectResponse
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
        return view('myadmin.announcements.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Announcements : ' . $infrastructure->total() . ' Records found']);
    }
    public function createannouncements(): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        return view('myadmin.announcements.createhtml', ['heading' => 'Announcements', 'statusArrays' => $this->statusArrays]);
    }
    public function editannouncements(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.announcements.edithtml', [
                'statusArrays' => $this->statusArrays,
                'heading' => 'Announcements',
                'info' => $coordinators
            ]);
        } else {
            return Redirect::route('announcements')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function indexnewsupdates(Request $request): View|Factory|RedirectResponse
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
        return view('myadmin.newsupdates.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'News & Updates : ' . $infrastructure->total() . ' Records found']);
    }
    public function createnewsupdates(): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        return view('myadmin.newsupdates.createhtml', ['heading' => 'Create News & Updates', 'statusArrays' => $this->statusArrays]);
    }
    public function editnewsupdates(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.newsupdates.edithtml', [
                'statusArrays' => $this->statusArrays,
                'heading' => 'Edit News & Updates',
                'info' => $coordinators
            ]);
        } else {
            return Redirect::route('announcements')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function indexlatestupdates(Request $request): View|Factory|RedirectResponse
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
        return view('myadmin.latestupdates.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Latest Updates : ' . $infrastructure->total() . ' Records found']);
    }
    public function createlatestupdates(): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        return view('myadmin.latestupdates.createhtml', ['heading' => 'Create Latest Updates', 'statusArrays' => $this->statusArrays]);
    }
    public function editlatestupdates(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $coordinators = Coordinator::where('id', $id)->first();

        $featureimg = Albumimage::where('albumid', $id)->orderBy('order', 'ASC')->get();
        if ($coordinators) {
            return view('myadmin.latestupdates.edithtml', [
                'statusArrays' => $this->statusArrays,
                'heading' => 'Edit Latest Updates',
                'featureimg' => $featureimg,
                'info' => $coordinators
            ]);
        } else {
            return Redirect::route('announcements')->with('status', 'Mentioned Id does not exist.');
        }
    }
    public function indexadminstaff(Request $request): View|Factory|RedirectResponse
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
        return view('myadmin.admstaffs.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Adminstrative Staff Information : ' . $infrastructure->total() . ' Records found']);
    }
    public function createadminstaff(): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'adminstaff')->get();
        return view('myadmin.admstaffs.createhtml', ['heading' => 'Add Staff Information', 'sections' => $sections, 'statusArrays' => $this->statusArrays]);
    }
    public function editadminstaff(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'adminstaff')->get();
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.admstaffs.edithtml', [
                'statusArrays' => $this->statusArrays,
                'heading' => 'Edit Staff Information',
                'sections' => $sections,
                'info' => $coordinators
            ]);
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
    public function indexalbums(Request $request):View|Factory|RedirectResponse
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
        return view('myadmin.albums.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Campus Tours : ' . $infrastructure->total() . ' Records found']);
    }
    public function createalbums(): View|Factory
    {
        return view('myadmin.albums.createhtml', ['heading' => 'Campus Tours', 'statusArrays' => $this->statusArrays]);
    }
    public function editalbums(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.albums.edithtml', [
                'statusArrays' => $this->statusArrays,
                'heading' => 'Campus Tours',
                'info' => $coordinators
            ]);
        } else {
            return Redirect::route('albums')->with('status', 'Mentioned Id does not exist.');
        }
    }
    public function indexbogs(Request $request): View|Factory|RedirectResponse
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
        return view('myadmin.bogs.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Board Of Governors : ' . $infrastructure->total() . ' Records found']);
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
    public function createbogs(): View|Factory
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'bogs')->get();
        return view('myadmin.bogs.createhtml', ['heading' => 'Board Of Governors', 'sections' => $sections, 'statusArrays' => $this->statusArrays]);
    }
    public function editbogs(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'bogs')->get();
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.bogs.edithtml', [
                'statusArrays' => $this->statusArrays,
                'heading' => 'Board Of Governors',
                'sections' => $sections,
                'info' => $coordinators
            ]);
        } else {
            return Redirect::route('bogs')->with('status', 'Mentioned Id does not exist.');
        }
    }
    public function indexraac(Request $request): View|Factory
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
        return view('myadmin.raac.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Research and Academic Advisory Council: ' . $infrastructure->total() . ' Records found']);
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
    public function createraac(): View|Factory
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'raac')->get();
        return view('myadmin.raac.createhtml', ['heading' => 'Research and Academic Advisory Council', 'sections' => $sections, 'statusArrays' => $this->statusArrays]);
    }
    public function editraac(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'raac')->get();
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.raac.edithtml', [
                'statusArrays' => $this->statusArrays,
                'heading' => 'Research and Academic Advisory Council',
                'sections' => $sections,
                'info' => $coordinators
            ]);
        } else {
            return Redirect::route('raac')->with('status', 'Mentioned Id does not exist.');
        }
    }
    public function indexphpadmissions(Request $request): View|Factory
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
        return view('myadmin.admissions.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Admissions : ' . $infrastructure->total() . ' Records found']);
    }
    public function createadmissions(): View|Factory
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'admissions')->get();
        $scientists = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.*', 'userdetails.designation'])->where('users.roles', 'scientists')->orderBy('name', 'ASC')->get();
        return view('myadmin.admissions.createhtml', [
            'heading' => 'Admissions',
            'sections' => $sections,
            'scientists' => $scientists,
            'statusArrays' => $this->statusArrays
        ]);
    }
    public function editadmissions(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $scientists = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.*', 'userdetails.designation'])->where('users.roles', 'scientists')->orderBy('name', 'ASC')->get();
        $pdflists = Albumimage::where('albumid', $id)->orderBy('order', 'ASC')->get();
        $sections = Section::where('isactive', 1)->where('type', 'admissions')->get();
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.admissions.edithtml', [
                'heading' => 'Admissions',
                'statusArrays' => $this->statusArrays,
                'sections' => $sections,
                'scientists' => $scientists,
                'pdflists' => $pdflists,
                'info' => $coordinators
            ]);
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
    public function editalbumimage_direct(Request $request, int $id): View|Factory
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

        $userDetailsInfo = Albumimage::find($request->input('albumid'));
        // dd($userDetailsInfo);
        $userDetailsInfo->feature_image = $request->input('attachmentfile');
        $userDetailsInfo->tititle = $request->input('attachmentname');
        // dd($userDetailsInfo->attachmentname);
        $userDetailsInfo->isactive = $request->input('isactive');

        if (!empty($attachmentfileName)) {
            $userDetailsInfo->feature_image = $attachmentfileName;
        }

        $saved = $userDetailsInfo->save();
        return Redirect::route('admissions', ['id' => $request->input('albumid')])->with('status', ' Content has been updated successfully');
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
	public function indexmou(Request $request): View|Factory|RedirectResponse
	{
		if (getcurrentUserRole() != 'users') {
			return Redirect::route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'mou')->paginate(20);
		return view('myadmin.mou.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Memorandum Of Understanding : ' . $infrastructure->total() . ' Records found']);
	}
	public function createmou(): View|Factory|RedirectResponse
	{
		if (getcurrentUserRole() != 'users') {
			return Redirect::route('scientists');
		}
		return view('myadmin.mou.createhtml', ['heading' => 'Memorandum Of Understanding'])->with('statusArrays', $this->statusArrays);
	}
	public function editmou(Request $request, $id): View|Factory|RedirectResponse
	{
		if (getcurrentUserRole() != 'users') {
			return Redirect::route('scientists');
		}
		$coordinators = Coordinator::where('id', $id)->first();
		if ($coordinators) {
			return view('myadmin.mou.edithtml')
				->with('heading', 'Memorandum Of Understanding')
				->with('statusArrays', $this->statusArrays)
				->with('info', $coordinators);
		} else {
			return Redirect::route('mou')->with('status', 'Mentioned Id does not exist.');
		}
	}
	public function indexhonoraryadjunctfaculty(Request $request): View|Factory|RedirectResponse
	{
		if (getcurrentUserRole() != 'users') {
			return Redirect::route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::join('sections', 'sections.id', '=', 'coordinators.catid')->select(['sections.sectionname', 'coordinators.*']);
		if (request('search')) {
			$visionary->where('coordinators.name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('order', 'ASC')->where('coordinators.type', 'honoraryadjunctfaculty')->paginate(20);
		return view('myadmin.honoraryadjunctfaculty.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Honorary and Adjunct Faculty : ' . $infrastructure->total() . ' Records found']);
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
    public function createhonoraryadjunctfaculty(): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'honoraryadjunctfaculty')->get();
        return view('myadmin.honoraryadjunctfaculty.createhtml', [
            'sections' => $sections,
            'heading' => 'Create Honorary and Adjunct Faculty',
            'statusArrays' => $this->statusArrays,
            'alumniArrays' => $this->alumniArrays,
        ]);
    }

    public function edithonoraryadjunctfaculty(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'honoraryadjunctfaculty')->get();
        $coordinators = Coordinator::find($id);
        if ($coordinators) {
            return view('myadmin.honoraryadjunctfaculty.edithtml', [
                'statusArrays' => $this->statusArrays,
                'heading' => 'Edit Honorary and Adjunct Faculty',
                'sections' => $sections,
                'info' => $coordinators,
            ]);
        } else {
            return Redirect::route('honoraryadjunctfaculty')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function tenders(Request $request): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $search = $request->query('search');
        $visionary = Coordinator::query();
        if ($search) {
            $visionary->where('name', 'like', '%' . $search . '%');
        }
        $infrastructure = $visionary->where('type', 'tenders')->orderBy('sortorder', 'ASC')->paginate(20);
        return view('myadmin.tenders.listhtml', [
            'lists' => $infrastructure,
            'search' => $search,
            'totalrecords' => 'Tenders: ' . $infrastructure->total() . ' Records found',
            'statusArrays' => $this->statusArrays,
        ]);
    }

    public function createtenders(): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $sections = Section::where('isactive', 1)->where('type', 'tenders')->get();
        return view('myadmin.tenders.createhtml', [
            'heading' => 'Tenders',
            'sections' => $sections,
            'statusArrays' => $this->statusArrays,
        ]);
    }

    public function edittenders(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $pdflists = Albumimage::where('albumid', $id)->orderBy('order', 'ASC')->get();
        $sections = Section::where('isactive', 1)->where('type', 'tenders')->get();
        $coordinators = Coordinator::find($id);
        if ($coordinators) {
            return view('myadmin.tenders.edithtml', [
                'heading' => 'Tenders',
                'statusArrays' => $this->statusArrays,
                'sections' => $sections,
                'pdflists' => $pdflists,
                'info' => $coordinators,
            ]);
        } else {
            return Redirect::route('tenders')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function tenderUpdateOrders(Request $request): JsonResponse
    {
        $orders = $request->input('order');
        foreach ($orders as $order) {
            if (isset($order['id'], $order['position'], $order['albumid'])) {
                $list = Albumimage::where('albumid', $order['albumid'])->where('id', $order['id'])->first();
                if ($list) {
                    $list->order = $order['position'];
                    $list->save();
                }
            }
        }
        return Response::json(['status' => 'success']);
    }

    public function edittenders_direct(Request $request, int $id): View|Factory|RedirectResponse
    {
        $album = Albumimage::find($id);
        if ($album) {
            return view('myadmin.tenders.modals.edittendershtml', ['album' => $album]);
        }
        return Redirect::route('tenders')->with('status', 'Album not found.');
    }

    public function updatetenders_direct(Request $request): RedirectResponse
    {
        $validator = $request->validate([
            'albumid' => 'required|max:100',
            'attachmentname' => 'required',
            'attachmentfile' => 'max:50000'
        ]);

        $attachmentfileName = '';
        if ($request->hasFile('attachmentfile')) {
            $attachmentfile = $request->file('attachmentfile');
            $attachmentfileName = $request->input('albumid') . '_' . time() . '.' . $attachmentfile->extension();
            $attachmentfile->move(public_path('userpics'), $attachmentfileName);
        }

        $userDetailsInfo = Albumimage::find($request->input('albumid'));
        if ($userDetailsInfo) {
            $userDetailsInfo->feature_image = $attachmentfileName ?: $userDetailsInfo->feature_image;
            $userDetailsInfo->tititle = $request->input('attachmentname');
            $userDetailsInfo->isactive = $request->input('isactive');
            $userDetailsInfo->save();
        }

        return Redirect::route('tenders', ['id' => $request->input('albumid')])->with('status', 'Content has been updated successfully');
    }

    public function deletesinglecordinator(Request $request): JsonResponse
    {
        $ids = $request->input('id');
        $tag = $request->input('tag');
        Coordinator::whereIn('id', $ids)->delete();
        return Response::json(['status' => true]);
    }

    public function indexcif(Request $request): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $search = $request->input('search');
        $visionary = Coordinator::query();
        if ($search) {
            $visionary->where('name', 'Like', '%' . $search . '%');
        }
        $infrastructure = $visionary->orderBy('id', 'DESC')->where('type', 'cif')->paginate(20);
        return view('myadmin.cif.listhtml', [
            'lists' => $infrastructure,
            'search' => $search,
            'totalrecords' => 'Central Instrument Facility : ' . $infrastructure->total() . ' Records found',
            'statusArrays' => $this->statusArrays,
        ]);
    }

    public function createcif(): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        return view('myadmin.cif.createhtml', [
            'heading' => 'Central Instrument Facility',
            'statusArrays' => $this->statusArrays,
        ]);
    }

    public function editcif(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.cif.edithtml', [
                'heading' => 'Central Instrument Facility',
                'statusArrays' => $this->statusArrays,
                'info' => $coordinators,
            ]);
        } else {
            return Redirect::route('cif')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function indextechnology(Request $request): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $search = $request->input('search');
        $visionary = Coordinator::query();
        if ($search) {
            $visionary->where('name', 'Like', '%' . $search . '%');
        }
        $infrastructure = $visionary->orderBy('sortorder', 'ASC')->where('type', 'technology')->paginate(20);
        return view('myadmin.technology.listhtml', [
            'lists' => $infrastructure,
            'search' => $search,
            'totalrecords' => 'Technology : ' . $infrastructure->total() . ' Records found',
            'statusArrays' => $this->statusArrays,
        ]);
    }

    public function createtechnology(): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        return view('myadmin.technology.createhtml', [
            'heading' => 'Technology',
            'statusArrays' => $this->statusArrays,
        ]);
    }

    public function edittechnology(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.technology.edithtml', [
                'heading' => 'Technology',
                'statusArrays' => $this->statusArrays,
                'info' => $coordinators,
            ]);
        } else {
            return Redirect::route('technology')->with('status', 'Mentioned Id does not exist.');
        }
    }













    public function indexdeans(Request $request): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $search = $request->input('search');
        $visionary = Coordinator::query();
        if ($search) {
            $visionary->where('name', 'Like', '%' . $search . '%');
        }
        $infrastructure = $visionary->orderBy('id', 'DESC')->where('type', 'deans')->paginate(20);
        return view('myadmin.deans.listhtml', [
            'lists' => $infrastructure,
            'search' => $search,
            'totalrecords' => 'Deans Information : ' . $infrastructure->total() . ' Records found'
        ]);
    }

    public function createdeans(): View|Factory
    {
        return view('myadmin.deans.createhtml', [
            'heading' => 'Add Dean Information',
            'statusArrays' => $this->statusArrays
        ]);
    }

    public function editdeans(Request $request, int $id): View|Factory|RedirectResponse
    {
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.deans.edithtml', [
                'statusArrays' => $this->statusArrays,
                'heading' => 'Edit Deans Information',
                'info' => $coordinators
            ]);
        } else {
            return Redirect::route('deans')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function index(Request $request): View|Factory
    {
        $search = $request->input('search');
        $visionary = Coordinator::query();
        if ($search) {
            $visionary->where('name', 'Like', '%' . $search . '%');
        }
        $infrastructure = $visionary->orderBy('id', 'DESC')->where('type', 'infrastructure')->paginate(20);
        return view('myadmin.infrastructure.listhtml', [
            'lists' => $infrastructure,
            'search' => $search,
            'totalrecords' => 'Infrastructure : ' . $infrastructure->total() . ' Records found'
        ]);
    }

    public function create(): View|Factory
    {
        return view('myadmin.infrastructure.createhtml', ['statusArrays' => $this->statusArrays]);
    }

    public function edit(Request $request, int $id): View|Factory|RedirectResponse
    {
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.infrastructure.edithtml', [
                'statusArrays' => $this->statusArrays,
                'info' => $coordinators
            ]);
        } else {
            return Redirect::route('infrastructure')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function deleterecords(Request $request): JsonResponse
    {
        $ids = $request->input('post_id');
        Coordinator::whereIn('id', $ids)->delete();
        return Response::json(['status' => true]);
    }

    public function infrastructurestatus(Request $request): JsonResponse
    {
        $pageids = $request->input('post_id');
        $status_type = $request->input('status_type');
        Coordinator::whereIn('id', $pageids)->update(['isactive' => $status_type]);
        return Response::json(['status' => true]);
    }

    public function indexevents(Request $request): View|Factory
    {
        $search = $request->input('search');
        $visionary = Coordinator::query();
        if ($search) {
            $visionary->where('name', 'Like', '%' . $search . '%');
        }
        $infrastructure = $visionary->orderBy('id', 'DESC')->where('type', 'events')->paginate(20);
        return view('myadmin.events_initiatives.listhtml', [
            'lists' => $infrastructure,
            'search' => $search,
            'totalrecords' => 'Events : ' . $infrastructure->total() . ' Records found'
        ]);
    }

    public function createevents(): View|Factory
    {
        return view('myadmin.events_initiatives.createhtml', ['statusArrays' => $this->statusArrays]);
    }

    public function editevents(Request $request, int $id): View|Factory|RedirectResponse
    {
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.events_initiatives.edithtml', [
                'statusArrays' => $this->statusArrays,
                'info' => $coordinators
            ]);
        } else {
            return Redirect::route('events')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function indexalumni(Request $request): View|Factory
    {
        $search = $request->input('search');
        $visionary = Coordinator::query();
        if ($search) {
            $visionary->where('name', 'Like', '%' . $search . '%');
        }
        $infrastructure = $visionary->orderBy('id', 'DESC')->where('type', 'alumni')->paginate(20);
        return view('myadmin.alumni.listhtml', [
            'lists' => $infrastructure,
            'search' => $search,
            'totalrecords' => 'Alumni : ' . $infrastructure->total() . ' Records found'
        ]);
    }

    public function createalumni(): View|Factory
    {
        return view('myadmin.alumni.createhtml', [
            'statusArrays' => $this->statusArrays,
            'alumniArrays' => $this->alumniArrays
        ]);
    }

    public function editalumni(Request $request, int $id): View|Factory|RedirectResponse
    {
        $coordinators = Coordinator::where('id', $id)->first();
        if ($coordinators) {
            return view('myadmin.alumni.edithtml', [
                'statusArrays' => $this->statusArrays,
                'alumniArrays' => $this->alumniArrays,
                'info' => $coordinators
            ]);
        } else {
            return Redirect::route('alumni')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function addmissionUpdateOrder(Request $request): JsonResponse
    {
        $orders = $request->input('order');

        if (is_array($orders)) {
            foreach ($orders as $order) {
                if (isset($order['id'], $order['position'], $order['type'])) {
                    $id = $order['id'];
                    $position = $order['position'];
                    $type = $order['type'];

                    $list = Coordinator::where('id', $id)->where('type', $type)
                        ->firstOrFail();
                    $list->sortorder = $position;
                    $list->save();
                }
            }
        }

        return Response::json(['status' => 'success']);
    }

    public function deletelatestupdatesImg(int $id): JsonResponse
    {
        Albumimage::where('id', $id)->delete();

        return Response::json([
            'success' => true,
            'message' => 'feature Image Deleted Successfully'
        ]);
    }

    public function swapTwoTenders(Request $request): void
    {
        $orders = $request->input('order');
    }

    public function swapLatestupdateImages(Request $request): JsonResponse
    {
        $images = $request->input('imageIds');

        if (is_array($images) && count($images) >= 2) {
            $id1 = $images[0]['id'] ?? null;
            $position1 = $images[0]['position'] ?? null;

            $id2 = $images[1]['id'] ?? null;
            $position2 = $images[1]['position'] ?? null;

            if ($id1 && $id2) {
                Albumimage::where('id', $id1)->update(['order' => $position2]);
                Albumimage::where('id', $id2)->update(['order' => $position1]);
            }
        }

        return Response::json([
            'success' => true,
            'message' => 'feature Images swapped Successfully'
        ]);
    }

    public function medialinks(Request $request): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        $search = $request->input('search');
        $visionary = Coordinator::query();
        if ($search) {
            $visionary->where('name', 'Like', '%' . $search . '%');
        }
        $infrastructure = $visionary->orderBy('id', 'DESC')->where('type', 'medialinks')->paginate(20);
        return view('myadmin.medialinks.listhtml', [
            'lists' => $infrastructure,
            'search' => $search,
            'totalrecords' => 'Media Links : ' . $infrastructure->total() . ' Records found'
        ]);
    }

    public function medialinkscreate(): View|Factory|RedirectResponse
    {
        return view('myadmin.medialinks.createhtml', [
            'heading' => 'Create Media Links',
            'statusArrays' => $this->statusArrays
        ]);
    }
}
