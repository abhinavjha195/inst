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

    public function createbogs(): View|Factory|RedirectResponse
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
    public function indexraac(Request $request): View|Factory|RedirectResponse
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
        return view('myadmin.admissions.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Admissions : ' . $infrastructure->count() . ' Records found']);
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
	public function indexmou(Request $request):View|Factory|RedirectResponse
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
		return view('myadmin.mou.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Memorandum Of Understanding : ' . $infrastructure->count() . ' Records found']);
	}
	public function createmou():View|Factory
	{
		if (getcurrentUserRole() != 'users') {
			return Redirect::route('scientists');
		}
		return view('myadmin.mou.createhtml', ['heading' => 'Memorandum Of Understanding'])->with('statusArrays', $this->statusArrays);
	}
	public function editmou(Request $request, int $id):View|Factory|RedirectResponse
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
	public function indexhonoraryadjunctfaculty(Request $request):View|Factory
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
		return view('myadmin.honoraryadjunctfaculty.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Honorary and Adjunct Faculty : ' . $infrastructure->count() . ' Records found']);
	}
	/////////////////////////////////////////////////////////////////////////
	public function honorarysortorder(Request $request):JsonResponse
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
}
