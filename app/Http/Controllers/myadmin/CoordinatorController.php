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

class CoordinatorController extends Controller
{
	public function __Construct()
	{
		$this->statusArrays = array('' => 'Status', 'inactive' => 'inActive', 'active' => 'Active');
		$this->alumniArrays = array('' => 'select', 'real' => 'Real Life', 'stories' => 'Alumni Stories');
	}
	public function store(Request $request)
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
		$coordinator->name = $request->name;
		$coordinator->subtitle = $request->subtitle;
		$coordinator->designation = $request->designation;
		$coordinator->description = $request->description;
		//$coordinator->feature_image = $request->feature_image;
		$coordinator->isactive = $request->isactive;
		$coordinator->type = $request->type;
		//$coordinator->pdfone = $request->pdfone;
		//$coordinator->pdftwo = $request->pdftwo;
		$coordinator->extrainfo = $request->extrainfo;
		$coordinator->make = $request->make;
		$coordinator->model = $request->model;
		$coordinator->catid = $request->catid;
		$coordinator->user_id = Auth()->id();
		if (!empty($request->postenddate)) {
			$coordinator->postenddate = convertdate($request->postenddate, 'Y-m-d H:i:s');
		}
		if (!empty($request->postdate)) {
			$coordinator->postdate = convertdate($request->postdate, 'Y-m-d H:i:s');
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
		// if (!empty($featureimages)) {
		// 	$coordinator->feature_image = json_encode($saveimages);
		// }

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

		// dd($coordinator);
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
		if ($request->type == 'admissions') {
			return redirect()->route('editadmissions', ['id' => $coordinator->id])->with('status', ' Content has been saved successfully');
		} else if ($request->type == 'tenders') {
			return redirect()->route('edittenders', ['id' => $coordinator->id])->with('status', ' Content has been saved successfully');
		} else {
			return redirect()->route($request->type)->with('status', ' Content has been saved successfully');
		}
	}

	public function update(Request $request, $id)
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
			$coordinator->name = $request->name;
			$coordinator->subtitle = $request->subtitle;
			$coordinator->designation = $request->designation;
			$coordinator->description = $request->description;
			$coordinator->descriptionOne = $request->descriptionOne;
			//$coordinator->feature_image = $request->feature_image;
			$coordinator->isactive = $request->isactive;
			$coordinator->type = $request->type;
			//$coordinator->pdfone = $request->pdfone;
			//$coordinator->pdftwo = $request->pdftwo;
			$coordinator->extrainfo = $request->extrainfo;
			$coordinator->make = $request->make;
			$coordinator->model = $request->model;
			$coordinator->catid = $request->catid;
			$coordinator->user_id = Auth()->id();
			if (!empty($request->postenddate)) {
				$coordinator->postenddate = convertdate($request->postenddate, 'Y-m-d H:i:s');
			}
			if (!empty($request->postdate)) {
				$coordinator->postdate = convertdate($request->postdate, 'Y-m-d H:i:s');
			}
			$featureimageName = '';
			$pdfoneName = '';
			$pdftwoName = '';
			$image_name = '';

			// if (!empty($request->file('feature_image'))) {

			// 	$images = $request->file('feature_image');
			// 	foreach ($images as $image) {
			// 	$featureimage = $image;
			// 	$featureimages = str_replace(' ', '', $image->getClientOriginalName());
			// 	$featureimage->move(public_path('uploads/images'), $featureimages);
			// 	$saveimages[] = $featureimages;
			// 	}
			// }


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
			// if (!empty($featureimages)) {
			// 	$coordinator->feature_image = json_encode($saveimages);
			// }
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

			// print_r($request->file('feature_image'));die();
			if (!empty($request->file('feature_image'))) {
				$images = $request->file('feature_image');
				// dd($images);
				$order = Albumimage::where('albumid', $id)->orderBy('id', 'desc')->first();
// dd($order);
				$i = 1;
				foreach ($images as $key => $image) {
					// echo"hi";die();
					if (!empty($order)) {
						$sortorder = $order['order'] + $i;
					} else {
						$sortorder = $i;
					}

					$imageName = $key . '_' . uniqid() . '.' . $image->extension();
					// dd($imageName);
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

			if ($request->type == 'admissions') {
				return redirect()->route('editadmissions', ['id' => $coordinator->id])->with('status', ' Content has been saved successfully');
			} else if ($request->type == 'tenders') {
				return redirect()->route('edittenders', ['id' => $coordinator->id])->with('status', ' Content has been saved successfully');
			} else {
				return redirect()->route($request->type)->with('status', ' Content has been saved successfully');
			}
		} else {
			return redirect()->route($request->type)->with('status', 'Mentioned Id does not exist.');
		}
	}
	public function updatedeans(Request $request, $id)
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
			$coordinator->name = $request->name;
			$coordinator->designation = $request->designation;
			$coordinator->description = $request->description;
			$coordinator->descriptionOne = $request->descriptionOne;
			$coordinator->isactive = $request->isactive;
			$coordinator->type = $request->type;
			$coordinator->extrainfo = $request->extrainfo;
			$coordinator->make = $request->make;
			$coordinator->model = $request->model;
			$coordinator->catid = $request->catid;
			$coordinator->user_id = Auth()->id();
			if (!empty($request->postenddate)) {
				$coordinator->postenddate = convertdate($request->postenddate, 'Y-m-d H:i:s');
			}
			if (!empty($request->postdate)) {
				$coordinator->postdate = convertdate($request->postdate, 'Y-m-d H:i:s');
			}
			if (!empty($request->file('feature_image'))) {
				$feature_img = $request->file('feature_image');
				$image_name = Auth()->id() . '_' . time() . '.' . $feature_img->extension();
				$feature_img->move(public_path('uploads/images'), $image_name);
				$coordinator->feature_image = $image_name;
			}
	
			$coordinator->save();
	
	
			if ($request->type == 'admissions') {
				return redirect()->route('editadmissions', ['id' => $coordinator->id])->with('status', ' Content has been saved successfully');
			} else if ($request->type == 'tenders') {
				return redirect()->route('edittenders', ['id' => $coordinator->id])->with('status', ' Content has been saved successfully');
			} else {
				return redirect()->route($request->type)->with('status', ' Content has been saved successfully');
			}
		} else {
			return redirect()->route($request->type)->with('status', 'Mentioned Id does not exist.');
		}
	}
	
	public function removeBanner(Request $request)
	{
		$image = $request->input('image');
		if (!empty($image)) {
			$result = Coordinator::where('feature_img', $image)
				->select('feature_img')
				->first();
			// dd($result);			 
			if ($result) {
				// Determine which field to delete based on the value returned from the query
				if ($result->feature_img == $image) {
					$field = 'feature_img';
				} else {
					$field = 'feature_img';
				}

				// Delete the field from the database
				Coordinator::where($field, $image)->update([$field => null]);

				// If the field was successfully deleted from the database,
				// return a success response.
				return redirect()->route('editlatestupdates')->with('status', 'Banner remove Successfully.');
			}
		}

		// If there was an error deleting the image from the database,
		// return an error response.
		return response()->json(['success' => false]);
	}

	public function indexannualreports(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'annualreports')->paginate(20);
		return view('myadmin.annualreports.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Annual Reports : ' . $infrastructure->count() . ' Records found']);
	}
	public function createannualreports()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		return view('myadmin.annualreports.createhtml', ['heading' => 'Annual Reports'])->with('statusArrays', $this->statusArrays);
	}
	public function editannualreports(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$coordinators = Coordinator::where('id', $id)->first();
		if ($coordinators) {
			return view('myadmin.annualreports.edithtml')
				->with('heading', 'Annual Reports')
				->with('statusArrays', $this->statusArrays)
				->with('info', $coordinators);
		} else {
			return redirect()->route('annual-reports')->with('status', 'Mentioned Id does not exist.');
		}
	}
	public function indexannouncements(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'announcements')->paginate(20);
		// dd($infrastructure);
		return view('myadmin.announcements.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Announcements : ' . $infrastructure->count() . ' Records found']);
	}
	public function createannouncements()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		return view('myadmin.announcements.createhtml', ['heading' => 'Announcements'])->with('statusArrays', $this->statusArrays);
	}
	public function editannouncements(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$coordinators = Coordinator::where('id', $id)->first();
		if ($coordinators) {
			return view('myadmin.announcements.edithtml')
				->with('statusArrays', $this->statusArrays)
				->with('heading', 'Announcements')
				->with('info', $coordinators);
		} else {
			return redirect()->route('announcements')->with('status', 'Mentioned Id does not exist.');
		}
	}

	public function indexnewsupdates(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'newsupdates')->paginate(20);
		return view('myadmin.newsupdates.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'News & Updates : ' . $infrastructure->count() . ' Records found']);
	}
	public function createnewsupdates()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		return view('myadmin.newsupdates.createhtml', ['heading' => 'Create News & Updates'])->with('statusArrays', $this->statusArrays);
	}
	public function editnewsupdates(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$coordinators = Coordinator::where('id', $id)->first();
		if ($coordinators) {
			return view('myadmin.newsupdates.edithtml')
				->with('statusArrays', $this->statusArrays)
				->with('heading', 'Edit News & Updates')
				->with('info', $coordinators);
		} else {
			return redirect()->route('announcements')->with('status', 'Mentioned Id does not exist.');
		}
	}

	public function indexlatestupdates(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'latestupdates')->paginate(20);
		return view('myadmin.latestupdates.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Latest Updates : ' . $infrastructure->count() . ' Records found']);
	}
	public function createlatestupdates()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		return view('myadmin.latestupdates.createhtml', ['heading' => 'Create Latest Updates'])->with('statusArrays', $this->statusArrays);
	}
	public function editlatestupdates(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$coordinators = Coordinator::where('id', $id)->first();

		$featureimg = Albumimage::where('albumid', $id)->orderBy('order', 'ASC')->get();
		//  dd($featureimg); 

		if ($coordinators) {
			return view('myadmin.latestupdates.edithtml')
				->with('statusArrays', $this->statusArrays)
				->with('heading', 'Edit Latest Updates')->with('featureimg', $featureimg)
				->with('info', $coordinators);
		} else {
			return redirect()->route('announcements')->with('status', 'Mentioned Id does not exist.');
		}
	}
	public function indexadminstaff(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('order', 'ASC')->where('type', 'adminstaff')->paginate(20);
		return view('myadmin.admstaffs.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Adminstrative Staff Information : ' . $infrastructure->count() . ' Records found']);
	}
	public function createadminstaff()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$sections = Section::where('isactive', 1)->where('type', 'adminstaff')->get();
		return view('myadmin.admstaffs.createhtml', ['heading' => 'Add Staff Information', 'sections' => $sections])->with('statusArrays', $this->statusArrays);
	}
	public function editadminstaff(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
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
			return redirect()->route('admstaffs')->with('status', 'Mentioned Id does not exist.');
		}
	}
	///////////////////////////////////////////////////////////////////////////////
	public function adminsataffUpdateOrder(Request $request)
	{
		$orders = $request->input('order');
		foreach ($orders as $order) {
			$id = $order['id'];
			$position = $order['position'];
			$type = $order['type'];
			// dd($catid);

			$list = Coordinator::where('type', $type)
				->where('id', $id)
				->firstOrFail();
			$list->order = $position;
			$list->save();
		}


		return response()->json(['status' => 'success']);
	}
	////////////////////////////////////////////////////////////////////////////////
	public function indexalbums(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'albums')->paginate(20);
		return view('myadmin.albums.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Campus Tours : ' . $infrastructure->count() . ' Records found']);
	}
	public function createalbums()
	{
		return view('myadmin.albums.createhtml', ['heading' => 'Campus Tours'])->with('statusArrays', $this->statusArrays);
	}
	public function editalbums(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$coordinators = Coordinator::where('id', $id)->first();
		if ($coordinators) {
			return view('myadmin.albums.edithtml')
				->with('statusArrays', $this->statusArrays)
				->with('heading', 'Campus Tours')
				->with('info', $coordinators);
		} else {
			return redirect()->route('albums')->with('status', 'Mentioned Id does not exist.');
		}
	}
	public function indexbogs(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
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
	public function blogUpdateOrders(Request $request)
	{
		$orders = $request->input('order');
		foreach ($orders as $order) {
			$id = $order['id'];
			$position = $order['position'];
			// dd($catid);

			$list = Coordinator::where('type', 'bogs')
				->where('id', $id)
				->firstOrFail();
			$list->order = $position;
			$list->save();
		}


		return response()->json(['status' => 'success']);
	}

	//////////////////////////////////////////////////////////////////////////////////////////////
	public function createbogs()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$sections = Section::where('isactive', 1)->where('type', 'bogs')->get();
		return view('myadmin.bogs.createhtml', ['heading' => 'Board Of Governors', 'sections' => $sections])->with('statusArrays', $this->statusArrays);
	}
	public function editbogs(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
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
			return redirect()->route('bogs')->with('status', 'Mentioned Id does not exist.');
		}
	}
	public function indexraac(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
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
	public function raacUpdateOrders(Request $request)
	{
		$orders = $request->input('order');
		foreach ($orders as $order) {
			$id = $order['id'];
			$position = $order['position'];
			// dd($catid);

			$list = Coordinator::where('type', 'raac')
				->where('id', $id)
				->firstOrFail();
			$list->order = $position;
			$list->save();
		}


		return response()->json(['status' => 'success']);
	}
	////////////////////////////////////////////////////////////////////////////////////
	public function createraac()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$sections = Section::where('isactive', 1)->where('type', 'raac')->get();
		return view('myadmin.raac.createhtml', ['heading' => 'Research and Academic Advisory Council', 'sections' => $sections])->with('statusArrays', $this->statusArrays);
	}
	public function editraac(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
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
			return redirect()->route('raac')->with('status', 'Mentioned Id does not exist.');
		}
	}
	public function indexphpadmissions(Request $request)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$search = $request->query('search');
		$visionary = Coordinator::query();
		if (request('search')) {
			$visionary->where('name', 'Like', '%' . request('search') . '%');
		}
		$infrastructure =  $visionary->orderBy('sortorder', 'ASC')->where('type', 'admissions')->paginate(20);
		// $infrastructure =  $visionary->orderBy('order', 'ASC')->where('type','admissions')->paginate(20);
		return view('myadmin.admissions.listhtml', ['lists' => $infrastructure, 'search' => $search, 'totalrecords' => 'Admissions : ' . $infrastructure->count() . ' Records found']);
	}
	///////////////////////////////////////////////////////////////////////////////
	// public function sortOrder(Request $request)
	// {
	//     $posts = Coordinator::all();
	//     // dd($posts);
	//     $maxOrder = Coordinator::max('order');

	//     // Create the new record with the next order value
	//     $attributes['order'] = $maxOrder + 1;
	//     foreach ($posts as $post) {
	//         foreach ($request->order as $order) {
	//             if ($order['id'] == $post->id) {
	//                 $post->update(['order' => $order['position']]);
	//             }
	//         }
	//     }

	//     return response('Update Successfully.', 200);
	// }
	//////////////////////////////////////////////////////////////////////////////////////
	public function createadmissions()
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		$sections = Section::where('isactive', 1)->where('type', 'admissions')->get();
		$scientists = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.*', 'userdetails.designation'])->where('users.roles', 'scientists')->orderBy('name', 'ASC')->get();
		return view('myadmin.admissions.createhtml', [
			'heading' => 'Admissions',
			'sections' => $sections,
			'scientists' => $scientists
		])->with('statusArrays', $this->statusArrays);
	}
	public function editadmissions(Request $request, $id)
	{
		if (getcurrentUserRole() != 'users') {
			return redirect()->route('scientists');
		}
		//$scientists = User::where('roles','scientists')->where('isactive',1)->orderBy('name','ASC')->get();
		$scientists = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.*', 'userdetails.designation'])->where('users.roles', 'scientists')->orderBy('name', 'ASC')->get();
		$pdflists = Albumimage::where('albumid', $id)->orderBy('order', 'ASC')->get();
		// dd($pdflists);
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
			return redirect()->route('admissions')->with('status', 'Mentioned Id does not exist.');
		}
	}
	////////////////////////////////////////////////////////////////
	public function admissionsUpdateOrders(Request $request)
	{
		$orders = $request->input('order');
		foreach ($orders as $order) {
			$id = $order['id'];
			$position = $order['position'];
			$albumid = $order['albumid'];
			// dd($albumid);

			$list = Albumimage::where('albumid', $albumid)
				->where('id', $id)
				->firstOrFail();
			$list->order = $position;
			$list->save();
		}


		return response()->json(['status' => 'success']);
	}
	///////////////////////////////////////////////////////////////
	public function editalbumimage_direct(Request $request, $id)
	{
		// Retrieve the album with the given ID
		$album = Albumimage::find($id);
		// dd($album);

		// Return the view for the edit page, passing the album object as data
		return view('myadmin.admissions.modals.editadmissionhtml', ['album' => $album]);
	}
	public function updatealbumimage_direct(Request $request)
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
		return redirect()->route('admissions', ['id' => $request->albumid])->with('status', ' Content has been updated successfully');
	}

	////////////////////////////////////////////////////////////////////
	public function filemanager(Request $request)
	{
		// Get the uploaded file from the request
		$file = $request->file('file');

		$filename = time() . '_' . $file->getClientOriginalName();

		$file->move(public_path('userpics'), $filename);

		$url = asset('userpics/' . $filename);
// dd($url);
		// Return the URL of the saved image
		return response()->json(['location' => $url]);
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


		return response()->json(['status' => 'success']);
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


		return response()->json(['status' => 'success']);
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
		return response()->json(['status' => true]);
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
		return response()->json(['status' => true]);
	}
	public function infrastructurestatus(Request $request)
	{
		$pageids = $request->post_id;
		$status_type = $request->status_type;
		Coordinator::whereIn('id', $pageids)->update(['isactive' => $status_type]);
		return response()->json(['status' => true]);
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

		return response()->json(['status' => 'success']);
	}

	public function deletelatestupdatesImg($id)
	{
		Albumimage::where('id', $id)->delete();

		return response()->json([
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

		return response()->json([
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
