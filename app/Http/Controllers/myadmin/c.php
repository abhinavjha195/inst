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
    protected array $statusArrays;

    /**
     * @var array<string, string>
     */
    protected array $alumniArrays;

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



       // Handle 'pdfone' file upload
    $pdfone = $request->file('pdfone');
    if ($pdfone !== null) {
        if ($pdfone instanceof \Illuminate\Http\UploadedFile) {
            $pdfoneName = Auth::id() . '_' . time() . '.' . $pdfone->extension();
            $pdfone->move(public_path('uploads'), $pdfoneName);
        }
    }
       // Handle 'pdftwo' file upload
    $pdftwo = $request->file('pdftwo');
    if ($pdftwo !== null) {
        if ($pdftwo instanceof \Illuminate\Http\UploadedFile) {
            $pdftwoName = Auth::id() . '_' . time() . '.' . $pdftwo->extension();
            $pdftwo->move(public_path('uploads'), $pdftwoName);
        }
    }
        if (!empty($pdfoneName)) {
            $coordinator->pdfone = $pdfoneName;
        }
        if (!empty($pdftwoName)) {
            $coordinator->pdftwo = $pdftwoName;
        }


        // if (!empty($request->file('feature_img'))) {
        //     $feature_img = $request->file('feature_img');

        //     $image_name = Auth()->id() . '_' . time() . '.' . $feature_img->extension();
        //     $feature_img->move(public_path('uploads/images'), $image_name);
        // }

         // Retrieve the single 'feature_img' file from the request
    $feature_img = $request->file('feature_img');

    

    // Check if 'feature_img' is not null and is an instance of UploadedFile
    if ($feature_img instanceof \Illuminate\Http\UploadedFile) {
        $image_name = Auth::id() . '_' . time() . '.' . $feature_img->extension();
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

          // Handle upload for 'pdfone'
    $pdfone = $request->file('pdfone');
    if ($pdfone instanceof \Illuminate\Http\UploadedFile) {
        $pdfoneName = Auth::id() . '_' . time() . '.' . $pdfone->extension();
        $pdfone->move(public_path('uploads'), $pdfoneName);
    }

    // Handle upload for 'pdftwo'
    $pdftwo = $request->file('pdftwo');
    if ($pdftwo instanceof \Illuminate\Http\UploadedFile) {
        $pdftwoName = Auth::id() . '_' . time() . '.' . $pdftwo->extension();
        $pdftwo->move(public_path('uploads'), $pdftwoName);
    }

            
            if (!empty($pdfoneName)) {
                $coordinator->pdfone = $pdfoneName;
            }
            if (!empty($pdftwoName)) {
                $coordinator->pdftwo = $pdftwoName;
            }

            // if (!empty($request->file('feature_img'))) {
            //     $feature_img = $request->file('feature_img');
            //     $image_name = Auth()->id() . '_' . time() . '.' . $feature_img->extension();
            //     $feature_img->move(public_path('uploads'), $image_name);
            // }

            // Handle file upload for feature_image
        $feature_img = $request->file('feature_image');
        if ($feature_img instanceof \Illuminate\Http\UploadedFile) {
            $image_name = Auth::id() . '_' . time() . '.' . $feature_img->extension();
            $feature_img->move(public_path('uploads'), $image_name);
            // $coordinator->feature_image = $image_name;
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
            // if (!empty($request->file('feature_image'))) {
            //     $feature_img = $request->file('feature_image');
            //     $image_name = Auth()->id() . '_' . time() . '.' . $feature_img->extension();
            //     $feature_img->move(public_path('uploads/images'), $image_name);
            //     $coordinator->feature_image = $image_name;
            // }

            // Handle file upload for feature_image
        $feature_img = $request->file('feature_image');
        if ($feature_img instanceof \Illuminate\Http\UploadedFile) {
            $image_name = Auth::id() . '_' . time() . '.' . $feature_img->extension();
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

    public function removeBanner(Request $request): RedirectResponse|JsonResponse
    {
        $image = $request->input('image');
        if (!empty($image)) {
            $result = Coordinator::where('feature_img', $image)
                ->select('feature_img')
                ->first();
            if ($result) {
                   /** @var Coordinator $result */
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
       // $search = $request->query('search');
        $visionary = Coordinator::query();

        // if (request('search')) {
        //     $visionary->where('name', 'Like', '%' . request('search') . '%');
        // }

        $search = $request->input('search', ''); // Default to empty string if not provided
 
        if (is_string($search) && $search !== '') {
            $visionary->where('name', 'like', '%' . $search . '%');
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
       // $search = $request->query('search');
        $visionary = Coordinator::query();

        $search = $request->input('search', ''); // Default to empty string if not provided
 
        if (is_string($search) && $search !== '') {
            $visionary->where('name', 'like', '%' . $search . '%');
        }

        // if (request('search')) {
        //     $visionary->where('name', 'Like', '%' . request('search') . '%');
        // }
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
      //  $search = $request->query('search');
        $visionary = Coordinator::query();
        // if (request('search')) {
        //     $visionary->where('name', 'Like', '%' . request('search') . '%');
        // }

        $search = $request->input('search', ''); // Default to empty string if not provided
 
        if (is_string($search) && $search !== '') {
            $visionary->where('name', 'like', '%' . $search . '%');
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
       // $search = $request->query('search');
        $visionary = Coordinator::query();
        // if (request('search')) {
        //     $visionary->where('name', 'Like', '%' . request('search') . '%');
        // }

        $search = $request->input('search', ''); // Default to empty string if not provided
 
        if (is_string($search) && $search !== '') {
            $visionary->where('name', 'like', '%' . $search . '%');
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
     //   $search = $request->query('search');
        $visionary = Coordinator::query();
        // if (request('search')) {
        //     $visionary->where('name', 'Like', '%' . request('search') . '%');
        // }

        $search = $request->input('search', ''); // Default to empty string if not provided
 
        if (is_string($search) && $search !== '') {
            $visionary->where('name', 'like', '%' . $search . '%');
        }
        $infrastructure =  $visionary->orderBy('order', 'ASC')->where('type', 'adminstaff')->paginate(20);
        return view('myadmin.admstaffs.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Adminstrative Staff Information : ' . $infrastructure->total() . ' Records found']);
    }

}
