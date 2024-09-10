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
        $coordinator->user_id = auth()->user()->id;
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
            if (is_array($pdfone)) { // Check if it's an array (multiple files)
                foreach ($pdfone as $file) { // Iterate over each file
                    $pdfoneName = auth()->user()->id . '_' . time() . '.' . $file->extension(); // Get the extension
                    $file->move(public_path('uploads'), $pdfoneName); // Move the file
                    // You may want to save $pdfoneName to the database if needed
                }
            } else { // Handle single file upload case
                $pdfoneName = auth()->user()->id . '_' . time() . '.' . $pdfone->extension(); // Get the extension
                $pdfone->move(public_path('uploads'), $pdfoneName); // Move the file
                // Save $pdfoneName to the database if needed
            }
        }
        if (!empty($request->file('pdftwo'))) {
            $pdftwo = $request->file('pdftwo');
            if (is_array($pdftwo)) { // Check if it's an array (multiple files)
                foreach ($pdftwo as $file) { // Iterate over each file
                    $pdftwoName = auth()->user()->id . '_' . time() . '.' . $file->extension(); // Get the extension
                    $file->move(public_path('uploads'), $pdftwoName); // Move the file
                    // You may want to save $pdftwoName to the database if needed
                }
            } else { // Handle single file upload case
                $pdftwoName = auth()->user()->id . '_' . time() . '.' . $pdftwo->extension(); // Get the extension
                $pdftwo->move(public_path('uploads'), $pdftwoName); // Move the file
                // Save $pdftwoName to the database if needed
            }
        }
        if (!empty($pdfoneName)) {
            $coordinator->pdfone = $pdfoneName;
        }
        if (!empty($pdftwoName)) {
            $coordinator->pdftwo = $pdftwoName;
        }
        if (!empty($request->file('feature_img'))) {
            $feature_img = $request->file('feature_img');

            $image_name = auth()->user()->id . '_' . time() . '.' . $feature_img->extension();
            $feature_img->move(public_path('uploads/images'), $image_name);
        }

        if (!empty($image_name)) {
            $coordinator->feature_image = $image_name;
        }

        $coordinator->save();

        if (!empty($request->file('feature_image'))) {
            $images = $request->file('feature_image');
            if (is_array($images)) {
                $i = 1;
                foreach ($images as $image) {
                    $imageName = uniqid() . '.' . $image->extension();
                    $image->move(public_path('uploads/images'), $imageName);
                    $addimg = new Albumimage();
                    $addimg->tititle = 'title';
                    $addimg->feature_image = $imageName;
                    $addimg->order = $i;
                    $addimg->albumid = $coordinator->id;
                    $addimg->save();
                    $i++;
                }
            } else {
                $imageName = uniqid() . '.' . $images->extension();
                $images->move(public_path('uploads/images'), $imageName);
                $addimg = new Albumimage();
                $addimg->tititle = 'title';
                $addimg->feature_image = $imageName;
                $addimg->order = 1;
                $addimg->albumid = $coordinator->id;
                $addimg->save();
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
            $coordinator->user_id = auth()->user()->id;
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
                if (is_array($pdfone)) { // Check if it's an array (multiple files)
                    foreach ($pdfone as $file) { // Iterate over each file
                        $pdfoneName = auth()->user()->id . '_' . time() . '.' . $file->extension(); // Get the extension
                        $file->move(public_path('uploads'), $pdfoneName); // Move the file
                        // You may want to save $pdfoneName to the database if needed
                    }
                } else { // Handle single file upload case
                    $pdfoneName = auth()->user()->id . '_' . time() . '.' . $pdfone->extension(); // Get the extension
                    $pdfone->move(public_path('uploads'), $pdfoneName); // Move the file
                    // Save $pdfoneName to the database if needed
                }
            }
            if (!empty($request->file('pdftwo'))) {
                $pdftwo = $request->file('pdftwo');
                if (is_array($pdftwo)) { // Check if it's an array (multiple files)
                    foreach ($pdftwo as $file) { // Iterate over each file
                        $pdftwoName = auth()->user()->id . '_' . time() . '.' . $file->extension(); // Get the extension
                        $file->move(public_path('uploads'), $pdftwoName); // Move the file
                        // You may want to save $pdftwoName to the database if needed
                    }
                } else { // Handle single file upload case
                    $pdftwoName = auth()->user()->id . '_' . time() . '.' . $pdftwo->extension(); // Get the extension
                    $pdftwo->move(public_path('uploads'), $pdftwoName); // Move the file
                    // Save $pdftwoName to the database if needed
                }
            }
            if (!empty($pdfoneName)) {
                $coordinator->pdfone = $pdfoneName;
            }
            if (!empty($pdftwoName)) {
                $coordinator->pdftwo = $pdftwoName;
            }

            if (!empty($request->file('feature_img'))) {
                $feature_img = $request->file('feature_img');
                $image_name = auth()->user()->id . '_' . time() . '.' . $feature_img->extension();
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
            $coordinator->user_id = auth()->user()->id;
            if (!empty($request->input('postenddate'))) {
                $coordinator->postenddate = convertdate($request->input('postenddate'), 'Y-m-d H:i:s');
            }
            if (!empty($request->input('postdate'))) {
                $coordinator->postdate = convertdate($request->input('postdate'), 'Y-m-d H:i:s');
            }
            if (!empty($request->file('feature_image'))) {
                $feature_img = $request->file('feature_image');
                $image_name = auth()->user()->id . '_' . time() . '.' . $feature_img->extension();
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
        $coordinator->user_id = auth()->user()->id;
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
            $pdfoneName = auth()->user()->id . '_' . time() . '.' . $pdfone->extension();
            $pdfone->move(public_path('uploads'), $pdfoneName);
        }
        if (!empty($request->file('pdftwo'))) {
            $pdftwo = $request->file('pdftwo');
            $pdftwoName = auth()->user()->id . '_' . time() . '.' . $pdftwo->extension();
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

            $image_name = auth()->user()->id . '_' . time() . '.' . $feature_img->extension();
            $feature_img->move(public_path('uploads/images'), $image_name);
        }



        if (!empty($image_name)) {
            $coordinator->feature_image = $image_name;
        }

        $coordinator->save();



        if (!empty($request->file('feature_image'))) {
            $images = $request->file('feature_image');
            if (is_array($images)) {
                $i = 1;
                foreach ($images as $image) {
                    $imageName = uniqid() . '.' . $image->extension();
                    $image->move(public_path('uploads/images'), $imageName);
                    $addimg = new Albumimage();
                    $addimg->tititle = 'title';
                    $addimg->feature_image = $imageName;
                    $addimg->order = $i;
                    $addimg->albumid = $coordinator->id;
                    $addimg->save();
                    $i++;
                }
            } else {
                $imageName = uniqid() . '.' . $images->extension();
                $images->move(public_path('uploads/images'), $imageName);
                $addimg = new Albumimage();
                $addimg->tititle = 'title';
                $addimg->feature_image = $imageName;
                $addimg->order = 1;
                $addimg->albumid = $coordinator->id;
                $addimg->save();
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
            $coordinator->user_id = auth()->user()->id;
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
                $pdfoneName = auth()->user()->id . '_' . time() . '.' . $pdfone->extension();
                $pdfone->move(public_path('uploads'), $pdfoneName);
            }
            if (!empty($request->file('pdftwo'))) {
                $pdftwo = $request->file('pdftwo');
                $pdftwoName = auth()->user()->id . '_' . time() . '.' . $pdftwo->extension();
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
                $image_name = auth()->user()->id . '_' . time() . '.' . $feature_img->extension();
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
            $coordinator->user_id = auth()->user()->id;
            if (!empty($request->input('postenddate'))) {
                $coordinator->postenddate = convertdate($request->input('postenddate'), 'Y-m-d H:i:s');
            }
            if (!empty($request->input('postdate'))) {
                $coordinator->postdate = convertdate($request->input('postdate'), 'Y-m-d H:i:s');
            }
            if (!empty($request->file('feature_image'))) {
                $feature_img = $request->file('feature_image');
                $image_name = auth()->user()->id . '_' . time() . '.' . $feature_img->extension();
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

    public function indexannualreports(Request $request): View|Factory
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
    public function createannualreports(): View|Factory
    {
        if (getcurrentUserRole() != 'users') {
            return Redirect::route('scientists');
        }
        return view('myadmin.annualreports.createhtml', ['heading' => 'Annual Reports', 'statusArrays' => $this->statusArrays]);
    }
    public function editannualreports(Request $request, $id): View|Factory|RedirectResponse
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

}