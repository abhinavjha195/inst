<?php

namespace App\Http\Controllers\myadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\myadmin\Coordinator;
use App\Models\Section;
use App\Models\Researchgroup;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\User;
use Validator;
use App\Models\myadmin\Albumimage;
// use File;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class DownloadController extends Controller
{
   /**
     * @var array<string, string>
     */
    protected array $statusArrays;

   

    public function __construct()
    {
        $this->statusArrays = ['' => 'Status', 'inactive' => 'inActive', 'active' => 'Active'];
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = $request->validate(
            [
                'name' => 'required|max:100',
                'isactive' => 'required'
            ],
            [
                'name.required' => 'The name is required',
                'isactive.required' => 'The status is required',
            ]
        );

        $coordinator = new Coordinator();
        $coordinator->name = $request->input('name');
        $coordinator->designation = $request->input('designation');
        $coordinator->description = $request->input('description');
        $coordinator->feature_image = $request->input('feature_image');
        $coordinator->isactive = $request->input('isactive');
        $coordinator->type = $request->input('type');
        $coordinator->extrainfo = $request->input('extrainfo');
        $coordinator->make = $request->input('make');
        $coordinator->model = $request->input('model');
        $coordinator->catid = $request->input('catid');
        $coordinator->user_id = Auth::id();

        if (!empty($request->postenddate)) {
            $coordinator->postenddate = convertdate($request->postenddate);
        }

        if (!empty($request->postdate)) {
            $coordinator->postdate = convertdate($request->postdate);
        }

        $pdfoneName = '';
        $pdftwoName = '';

        if ($request->hasFile('pdfone')) {
            $pdfone = $request->file('pdfone');
            if ($pdfone instanceof \Illuminate\Http\UploadedFile) {
                $pdfoneName = Auth::id() . '_' . time() . '.' . $pdfone->getClientOriginalExtension();
                $pdfone->move(public_path('userpics/downloads'), $pdfoneName);
            }
        }

        if ($request->hasFile('pdftwo')) {
            $pdftwo = $request->file('pdftwo');
            if ($pdftwo instanceof \Illuminate\Http\UploadedFile) {
                $pdftwoName = Auth::id() . '_' . time() . '.' . $pdftwo->getClientOriginalExtension();
                $pdftwo->move(public_path('userpics/downloads'), $pdftwoName);
            }
        }

        if (!empty($pdfoneName)) {
            $coordinator->pdfone = $pdfoneName;
        }

        if (!empty($pdftwoName)) {
            $coordinator->pdftwo = $pdftwoName;
        }

        if (!empty($request->pdfthree)) {
            $coordinator->pdfthree = json_encode($request->pdfthree);
        }

        $coordinator->save();

        // Retrieve 'pagetype' from request
    $pagetype = $request->input('type', 'pages');

    // Ensure $pagetype is a string
    if (!is_string($pagetype)) {
        $pagetype = 'pages'; // Fallback to a default route if the type is not correct
    }


        return Redirect::route($pagetype)->with('status', ' Content has been saved successfully');
    }

    public function internalfileupload(Request $request): RedirectResponse
    {
        $validator = $request->validate(
            [
                'albumid' => 'required|max:100',
                'attachmentname' => 'required',
                'attachmentfile' => 'mimes:pdf|max:50000'
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

        return Redirect::route('formsdownloads', ['id' => $request->input('albumid')])->with('status', ' Content has been updated successfully');
    }

    public function editinternal_form(Request $request, int $id): View|Factory
    {
        $album = Albumimage::find($id);
        return view('myadmin.downloads.modals.editadmissionhtml', ['album' => $album]);
    }

    public function updateainternal_form(Request $request): RedirectResponse
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

        if ($request->hasFile('attachmentfile')) {
            $attachmentfile = $request->file('attachmentfile');
            if ($attachmentfile instanceof \Illuminate\Http\UploadedFile) {
                $attachmentfileName = $request->input('albumid') . '_' . time() . '.' . $attachmentfile->getClientOriginalExtension();
                $attachmentfile->move(public_path('userpics'), $attachmentfileName);
            }
        }

        $userDetailsInfo = Albumimage::find($request->input('albumid'));

        if($userDetailsInfo){
        $userDetailsInfo->feature_image = $request->input('attachmentfile');
        $userDetailsInfo->tititle = $request->input('attachmentname');
        $userDetailsInfo->isactive = $request->input('isactive');
        

        if (!empty($attachmentfileName)) {
            $userDetailsInfo->feature_image = $attachmentfileName;
        }
    

        $saved = $userDetailsInfo->save();
    }

        return Redirect::route('formsdownloads', ['id' => $request->input('albumid')])->with('status', ' Content has been updated successfully');
    }

    public function deleteinternal_form(Request $request): RedirectResponse
    {
        $itemid = $request->input('itemid');
        Albumimage::where('id', $itemid)->delete();
        return Redirect::back()->with('status', ' Content has been deleted successfully');
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
                'name.required' => 'The name is required',
                'isactive.required' => 'The status is required',
            ]
        );

        $coordinator = Coordinator::find($id);

        if ($coordinator) {
            $coordinator->name = $request->input('name');
            $coordinator->designation = $request->input('designation');
            $coordinator->description = $request->input('description');
            $coordinator->feature_image = $request->input('feature_image');
            $coordinator->isactive = $request->input('isactive');
            $coordinator->type = $request->input('type');
            $coordinator->extrainfo = $request->input('extrainfo');
            $coordinator->make = $request->input('make');
            $coordinator->model = $request->input('model');
            $coordinator->catid = $request->input('catid');
            $coordinator->user_id = Auth::id();

            if (!empty($request->postenddate)) {
                $coordinator->postenddate = convertdate($request->postenddate);
            }

            if (!empty($request->postdate)) {
                $coordinator->postdate = convertdate($request->postdate);
            }

            $pdfoneName = '';
            $pdftwoName = '';

            if ($request->hasFile('pdfone')) {
                $pdfone = $request->file('pdfone');
                if ($pdfone instanceof \Illuminate\Http\UploadedFile) {
                    $pdfoneName = Auth::id() . '_' . time() . '.' . $pdfone->getClientOriginalExtension();
                    $pdfone->move(public_path('userpics/downloads'), $pdfoneName);
                }
            }

            if ($request->hasFile('pdftwo')) {
                $pdftwo = $request->file('pdftwo');
                if ($pdftwo instanceof \Illuminate\Http\UploadedFile) {
                    $pdftwoName = Auth::id() . '_' . time() . '.' . $pdftwo->getClientOriginalExtension();
                    $pdftwo->move(public_path('userpics/downloads'), $pdftwoName);
                }
            }

            if (!empty($pdfoneName)) {
                $coordinator->pdfone = $pdfoneName;
            }

            if (!empty($pdftwoName)) {
                $coordinator->pdftwo = $pdftwoName;
            }

            $coordinator->save();

            return Redirect::route($request->input('type'))->with('status', ' Content has been saved successfully');
        } else {
            return Redirect::route($request->input('type'))->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function index(Request $request): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }

        $query = Coordinator::join('sections', 'sections.id', '=', 'coordinators.catid')
            ->where('coordinators.type', 'formsdownloads')
            ->where('sections.type', 'downloads')
            ->orderBy('coordinators.order', 'ASC');

        // if (request('search')) {
        //     $query->where('coordinators.name', 'LIKE', '%' . request('search') . '%');
        // }

        $search = $request->input('search'); // Safely retrieve the search value

if (is_string($search) && !empty($search)) {
    $query->where('coordinators.name', 'LIKE', '%' . $search . '%');
}

        $search = $request->query('search');
        $catid = $request->query('catid');

        if (request('catid')) {
            $query->where('coordinators.catid', '=', request('catid'));
        }

        $sections = Section::where('isactive', 1)->where('type', 'downloads')->orderBy('id', 'ASC')->get();
        $lists = $query->paginate(60, ['coordinators.*', 'sections.sectionname']);

        return view('myadmin.downloads.listhtml', ['lists' => $lists, 'catid' => $catid, 'sections' => $sections, 'search' => $search, 'totalrecords' => 'Internal forms / Downloads : ' . $lists->total() . ' Records found']);
    }

    public function infrastructureinternal_forms_downloads_status(Request $request): JsonResponse
    {
        $pageids = $request->input('post_id');
        $status_type = $request->input('status_type');
        Coordinator::whereIn('id', $pageids)->update(['isactive' => $status_type]);
        return Response::json(['status' => true]);
    }

    public function updateOrders(Request $request): JsonResponse
    {
          /** 
         * @var array<array{id: int, position: int, catid: int}> $orders
         */
        $orders = $request->input('order');

        foreach ($orders as $order) {
            $id = $order['id'];
            $position = $order['position'];
            $catid = $order['catid'];


            // Retrieve a single Post model instance
            /** @var \App\Models\myadmin\Coordinator|null $list */
            $list = Coordinator::where('type', 'formsdownloads')
                ->where('catid', $catid)
                ->where('id', $id)
                ->firstOrFail();

                if ($list !== null) {
                    $list->order = $position;
                    $list->save();
				}
            // $list->order = $position;
            // $list->save();
        }

        return Response::json(['status' => 'success']);
    }

    public function create(): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }

        $sections = Section::where('isactive', 1)->where('type', 'downloads')->orderBy('id', 'ASC')->get();

        return view('myadmin.downloads.createhtml', [
            'heading' => 'Internal forms / Downloads',
            'sections' => $sections,
            'statusArrays' => $this->statusArrays
        ]);
    }

    public function edit(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }

        $sections = Section::where('isactive', 1)->where('type', 'downloads')->orderBy('id', 'ASC')->get();
        $coordinators = Coordinator::where('id', $id)->first();
        $pdflists = Albumimage::where('albumid', $id)->orderBy('order', 'ASC')->get();

        if ($coordinators) {
            return view('myadmin.downloads.edithtml', [
                'heading' => 'Internal forms / Downloads',
                'statusArrays' => $this->statusArrays,
                'sections' => $sections,
                'pdflists' => $pdflists,
                'info' => $coordinators
            ]);
        } else {
            return Redirect::route('formsdownloads')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        $info = Coordinator::where('id', $id)->first();

       
        // Check if $info is not null
    if ($info !== null) {
        // Check if the pdfone property is not empty
        if (!empty($info->pdfone)) {
            $filePath = public_path('userpics/downloads') . '/' . $info->pdfone;

            // Ensure the file exists before attempting to delete it
            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            // Clear the pdfone property
            $info->pdfone = '';
           
        }

        // Delete the Coordinator record
        Coordinator::where('id', $id)->delete();
    }

      // Check if $info is not null
      /** @var Coordinator $info */
    if ($info !== null) {
        // Check if the pdftwo property is not empty
        if ($info->pdftwo != "") { 
            $filePath = public_path('userpics/downloads') . '/' . $info->pdftwo;

            // Ensure the file exists before attempting to delete it
            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            // Clear the pdftwo property
            $info->pdftwo = '';
           
        }

        // Delete the Coordinator record
        Coordinator::where('id', $id)->delete();
    } 

    if ($info) {
        $info->delete();
    }

        return Redirect::route('formsdownloads')->with('status', ' Content has been updated successfully');
    }

    public function removesingledownload(Request $request): RedirectResponse
    {
        $itemid = $request->input('token');
        $key = $request->input('key');
        $info = Coordinator::find($itemid);

        // if ($info->pdfone != ""  && $key == "pdfone") {
        //     File::delete(public_path('userpics/downloads') . '/' . $info->pdfone);
        //     $info->pdfone = '';
        // }

        if($info){
        if ($info->pdfone != "" && $key == "pdfone") {
            // Ensure the file exists before attempting to delete
            $filePath = public_path('userpics/downloads') . '/' . $info->pdfone;
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            $info->pdfone = '';
        }
    }

        // if ($info->pdftwo != ""  && $key == "pdftwo") {
        //     File::delete(public_path('userpics/downloads') . '/' . $info->pdftwo);
        //     $info->pdftwo = '';
        // }


        if($info){
        if ($info->pdftwo != "" && $key == "pdftwo") {
            // Ensure the file exists before attempting to delete
            $filePath = public_path('userpics/downloads') . '/' . $info->pdftwo;
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            $info->pdftwo = '';
        }
    }
    if($info){
        $info->save();
    }

        return Redirect::route($request->input('type'))->with('status', ' Content has been saved successfully');
    }

    public function indexstudents(Request $request): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }

        $results = Researchgroup::join('sections', 'sections.id', '=', 'researchgroups.corembrid')->where('sections.type', 'coregroupmembers')->join('users', 'users.id', '=', 'researchgroups.userid')->select(['researchgroups.*', 'users.name as professorname', 'sections.sectionname']);

        if (request('sectionid')) {
            $results->where('researchgroups.corembrid', request('sectionid'));
        }

        if (request('professorid')) {
            $results->where('researchgroups.userid', request('professorid'));
        }

        // if (request('search')) {
        //     $results->where('researchgroups.name', 'Like', '%' . request('search') . '%');
        // }

        $search = $request->input('search'); // Safely retrieve the search value

if (is_string($search) && !empty($search)) {
    $results->where('researchgroups.name', 'Like', '%' . $search . '%');
}

        $others =  Researchgroup::join('sections', 'sections.id', '=', 'researchgroups.corembrid')->where('sections.type', 'coregroupmembers')->where('userid',777)->select(['researchgroups.*','sections.sectionname'])->get();

        $lists = $results->orderBy('researchgroups.id', 'DESC')->get();
        $combinedResults = $results->get()->concat($others);
        $perPage = 50;
        $page = request('page', 1);
        $total = $combinedResults->count();

        $paginator = new LengthAwarePaginator(
            $combinedResults->forPage($page, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        $professors = User::where('isactive', 1)->where('roles', 'scientists')->orderBy('sortorder', 'ASC')->get();

        $search = $request->query('search');
        $sectionid = $request->query('sectionid');
        $professorid = $request->query('professorid');

        $sections = Section::where('isactive', 1)->where('type', 'coregroupmembers')->orderBy('id', 'ASC')->get();

        return view('myadmin.students.listhtml', ['professorid' => $professorid, 'professors' => $professors, 'lists' => $paginator,'others'=>$others , 'sections' => $sections, 'sectionid' => $sectionid, 'search' => $search, 'totalrecords' => 'Students : ' . $total . ' Records found']);
    }

    public function createstudents(): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }

        $professors = User::where('isactive', 1)->where('roles', 'scientists')->get();
        $scholorlists = Section::where('isactive', 1)->where('type', 'coregroupmembers')->get();

        return view('myadmin.students.createhtml', [
            'heading' => 'Students',
            'scholorlists' => $scholorlists,
            'professors' => $professors,
            'statusArrays' => $this->statusArrays
        ]);
    }

    public function editstudents(Request $request, int $id): View|Factory|RedirectResponse
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }

        $scholorlists = Section::where('isactive', 1)->where('type', 'coregroupmembers')->get();
        $info  = Researchgroup::where('id', $id)->first();
        $professors = User::where('isactive', 1)->where('roles', 'scientists')->get();

        if ($info) {
            return view('myadmin.students.edithtml', [
                'heading' => 'Students',
                'statusArrays' => $this->statusArrays,
                'professors' => $professors,
                'scholorlists' => $scholorlists,
                'info' => $info
            ]);
        } else {
            return Redirect::route('Students')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function removestudent(Request $request): RedirectResponse
    {
        $ids = $request->input('pageid');
        Researchgroup::where('id', $ids)->delete();
        return Redirect::route('students')->with('status', 'Student Remove Successfully');
    }

    public function sorttenders(Request $request): JsonResponse
    {
        $orders = $request->input('order');

        // Check if $orders is an array and has the required structure
        if (is_array($orders) && isset($orders[0]['fromid'], $orders[0]['toposition'])) {
            $fromId = $orders[0]['fromid'];
            $toposition = $orders[0]['toposition'];

            $gettoId = Coordinator::where('sortorder', $toposition)->first();

            $updateto = Coordinator::where('id', $fromId)->update(['sortorder' => $toposition]);
            $updateto = Coordinator::where('id', $fromId)->first();

            $getrestdata = Coordinator::where('id', '!=', $fromId)->where('sortorder', '>=', $toposition)
                ->where('type', 'tenders')->orderBy('sortorder', 'ASC')->get();

            $i = $toposition + 1;

            foreach ($getrestdata as $value) {
                Coordinator::where('id', $value['id'])->update(['sortorder' => $i]);
                $i++;
            }

            return Response::json("success");
        }

        return Response::json(['status' => 'error', 'message' => 'Invalid order data'], 400);
    }

    public function studentstatus(Request $request): JsonResponse
    {
        $userids = $request->input('post_id');
        $status_type = $request->input('status_type');
        Researchgroup::whereIn('id', $userids)->update(['isactive' => $status_type]);
        return Response::json(['status' => true]);
    }
}
