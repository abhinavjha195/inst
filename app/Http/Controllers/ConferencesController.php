<?php

namespace App\Http\Controllers;

use App\Models\conferenceFiles;
use App\Models\conferenceLogos;
use App\Models\conferenceSpeakers;
use App\Models\myadmin\Conferences;
use App\Models\myadmin\ConferenceSponsers;
use App\Models\myadmin\Sponsers;
use App\Models\Speakers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

class ConferencesController extends Controller
{
    public function __Construct()
    {

        $this->statusArrays = array('' => 'Choose status', 'inactive' => 'inActive', 'active' => 'Active');
        $this->imageposition = array('' => 'None', 'left' => 'Left', 'right' => 'Right', 'top' => 'Top', 'bottom' => 'Bottom');

        $this->templates = array('default' => 'Default Template', 'annualreports' => 'Annual Reports Template', 'newsupdates' => 'News & Updates Template', 'latestupdates' => 'Latest updates Template', 'adminstaff' => 'Staff photo Template', 'mou' => 'MOU Template', 'albums' => 'Gallery Template', 'bogs' => 'BOG Template', 'raac' => 'RAAC Template', 'conferencesworkshop' => 'conference & Workshops Template', 'student' => 'Student Template', 'scientists' => 'Scientists Template', 'researchunites' => 'Research unit Template', 'honoraryadjunctfaculty' => 'Honorary Adjunct Faculty Template', 'admissions' => 'Admission Template', 'tenders' => 'Tenders Template', 'cif' => 'Central Iinstrument Facility Template', 'technology' => 'Techonology Template', 'deans' => 'Deans Template', 'contactus' => 'Contactus Template', 'downloads' => 'Downloads Template');
    }

    public function store_conferences(Request $request)
    {

        // dd($request->input('sponsers'));
        $this->validate($request, [
            'bannerimage' => 'mimes:png,jpeg,jpg,gif|max:3000',
            'date' => 'required',
            'expire_date' => 'required',
            'title_en' => 'required',
            'abbreviation' => 'required',
            'status' => 'required'
        ]);

        $page_slug = $this->generateUniqueSlug($request->title_en);

        $store = new Conferences();
        $store->title_en = $request->title_en;
        $store->title_hi = $request->title_hi;
        $store->slug = $page_slug;
        $store->tab_name = $request->abbreviation;
        $store->description_en = $request->description_en;
        $store->description_hi = $request->description_hi;
        $store->date = $request->date;
        $store->expire_date = $request->expire_date;
        $store->registration_status = $request->registration_status;
        // photo
        $banner_img = "";
        if (!empty($request->file('bannerimage'))) {
            $feature_image = $request->file('bannerimage');
            $image = time() . '.' . $feature_image->getClientOriginalExtension();
            $feature_image->move(public_path('/uploads/images'), $image);
            $banner_img = $image;
        }
        $store->bannerimage = $banner_img;

        $programme = "";
        if (!empty($request->file('programme_pdf'))) {
            $feature_image = $request->file('programme_pdf');
            $image = time() . '.' . $feature_image->getClientOriginalExtension();
            $feature_image->move(public_path('/uploads/images'), $image);
            $programme = $image;
        }
        $store->programme_pdf = $programme;

        $image_path = "";
        if (!empty($request->file('image_file'))) {
            $feature_image = $request->file('image_file');
            $image = time() . '.' . $feature_image->getClientOriginalExtension();
            $feature_image->move(public_path('/uploads/conference'), $image);
            $image_path = '/uploads/conference/' . $image;
        }
        $store->image_file = $image_path;
        $store->registration_details = $request->registration_details;
        $store->programme_link = $request->programme_link;
        $store->registration_details_for_ind = $request->registration_details_for_ind;
        $store->registration_details_for_other = $request->registration_details_for_other;
        $store->last_of_registration = $request->last_of_registration;
        $store->last_of_absract_submission = $request->last_of_absract_submission;
        $store->absract_submission_guideline = $request->absract_submission_guideline;
        // broucher_pdf
        $broucher = '';
        if (!empty($request->file('broucher_pdf'))) {
            $broucher_pdf = $request->file('broucher_pdf');
            $broucher = time() . '.' . $broucher_pdf->getClientOriginalExtension();
            $broucher_pdf->move(public_path('/uploads/conference'), $broucher);
            $broucher = '/uploads/conference/' . $broucher;
        }

        $store->broucher_pdf = $broucher;

        // e_book_of_absraction
        $e_book = '';
        if (!empty($request->file('e_book_of_absraction'))) {
            $e_book_of_absraction = $request->file('e_book_of_absraction');
            $e_book_file = time() . '.' . $e_book_of_absraction->getClientOriginalExtension();
            $e_book_of_absraction->move(public_path('/uploads/conference'), $e_book_file);
            $e_book = '/uploads/conference/' . $e_book_file;
        }


        $store->e_book_of_absraction = $e_book;
        //  scientific_program_of_TCS_file 
        $scientificProgram = '';
        if (!empty($request->file('scientific_program_of_TCS_file'))) {
            $scientific_program_of_TCS_file = $request->file('scientific_program_of_TCS_file');
            $scientific_program = time() . '.' . $scientific_program_of_TCS_file->getClientOriginalExtension();
            $scientific_program_of_TCS_file->move(public_path('/uploads/conference'), $scientific_program);
            $scientificProgram = '/uploads/conference/' . $scientific_program;
        }


        $store->scientific_program_of_TCS_file = $scientificProgram;
        // $store->abriviation = $request->abriviation;
        $store->orgenising_comamitee = $request->orgenising_comamitee;
        $store->programme = $request->programme;
        $store->contact_details = $request->contact_details;
        $store->venue = $request->venue;
        $store->accommodation = $request->accommodation;
        $store->sponsorship_plan = $request->sponsorship_plan;
        $store->status = $request->status;

        $store->save();

        // if ($request->input('speakers')) {
        //     // $sponsers = $request->input('speakers');
        //     $sponsers = $request->input('selected_items');
        //     print_r( $sponsers);die();
        //     foreach ($sponsers as $sponser) {
        //         print_r($sponser);die();
        //         $storeSponser = new conferenceSpeakers();
        //         $storeSponser->conference_id = $store->id;
        //         $storeSponser->sponser_id  = $sponser;
        //         $storeSponser->save();
        //     }
        // }
        // dd($store->id);
        if ($request->input('speakers')) {
            $sponsersString = $request->input('selected_items');
            $sponsersArray = explode(',', $sponsersString); // Split the string into an array
        // print_r($sponsersArray);die();
            foreach ($sponsersArray as $sponser) {
                $storeSponser = new conferenceSpeakers();
                $storeSponser->conference_id = $store->id;
                $storeSponser->sponser_id = $sponser;
                $storeSponser->save();
            }
        }
        
        if ($request->input('sponsers')) {
            $sponsers = $request->input('sponsers');
            foreach ($sponsers as $sponser) {
                $storeSponser = new ConferenceSponsers();
                $storeSponser->conference_id = $store->id;
                $storeSponser->sponser_id  = $sponser;
                $storeSponser->save();
            }
        }


        return redirect()->route('photoes', [$store->id])->with('success', 'Data Added Successfully!');
    }


    public function photoes($id)
    {

        $data = Conferences::where('id', $id)->first();
        $files = conferenceFiles::where('conference_id', $id)->where('type', 'photos')->get();
        $statusarrays = $this->statusArrays;
        return view('myadmin.conferencesworkshop.photo', ['info' => $data, 'files' => $files, 'id' => $id, 'statusArrays ' => $statusarrays]);
    }
    public function edit($id)
    {

        $data = Conferences::where('id', $id)->first();
        $attachfiles = conferenceFiles::where('conference_id', $id)->where('type', 'attachedfiles')->get();
        $photofiles = conferenceFiles::where('conference_id', $id)->where('type', 'photos')->get();
        // dd($Allscientists);

        $allsponsers = Sponsers::where('isactive', 'active')->get();
        $sponsers = ConferenceSponsers::where('conference_id', $id)->get()->toArray();
        // dd($sponsers);
        $speakers = conferenceSpeakers::where('conference_id', $id)->get()->toArray();
        // dd($speakers);
        $logo = Conferences::leftJoin('conference_logos', 'conferences.id', '=', 'conference_logos.conf_id')
            ->where('conf_id', $id)
            ->get();
        // dd($logo);
        $allSpeakers = Speakers::where('isactive', 'active')->get();
        // dd($allSpeakers);
        $registrationstatus = Conferences::where('registration_status', 'active')->get();

        $arrayfilterofspeakers = array_column($speakers, 'sponser_id');
        $arrayfilterofsponsers = array_column($sponsers, 'sponser_id');

        // dd($arrayfilter);
        $statusarrays = $this->statusArrays;
        return view('myadmin.conferencesworkshop.edithtml', ['info' => $data, 'allSpeakers' => $allSpeakers, 'sponsers' => $arrayfilterofsponsers, 'allsponsers' => $allsponsers, 'speakers' => $arrayfilterofspeakers, 'statusarrays' => $statusarrays, 'attachfiles' => $attachfiles, 'logo' => $logo, 'registrationstatus' => $registrationstatus, 'photofiles' => $photofiles, 'id' => $id]);
    }

    public function uploadphotos(Request $request)
    {



        $files =  $request->files;
        // print_r($files);die();
        foreach ($request->file('files') as $file) {
            $store =  new conferenceFiles();
            $store->type = $request->type;
            $store->conference_id = $request->postid;
            $e_book_file = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('/uploads/conference'), $e_book_file);
            $e_book = '/uploads/conference/' . $e_book_file;
            $store->file = $e_book;
            $store->save();
        }
        return response()->json([
            'success' => true,
            'message' => 'files uploaded successfully!'
        ]);
    }

    public function attachfiles($id)
    {
        $data = Conferences::where('id', $id)->first();
        $files = conferenceFiles::where('conference_id', $id)->where('type', 'attachedfiles')->get();
        $statusarrays = $this->statusArrays;
        return view('myadmin.conferencesworkshop.attachFiles', ['info' => $data, 'files' => $files, 'id' => $id, 'statusArrays ' => $statusarrays]);
    }

    public function storeconferencefile(Request $request)
    {
        $this->validate($request, [
            'attachmentfile' => 'mimes:png,jpeg,jpg,gif|max:1000',
        ]);

        $store =  new conferenceFiles();
        $store->type = $request->type;
        $store->conference_id = $request->postid;
        if (!empty($request->file('attachmentfile'))) {
            $file = $request->file('attachmentfile');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('/uploads/conference'), $filename);
            $path = '/uploads/conference/' . $filename;
            $store->file = $path;
        }
        $store->save();


        // return redirect()->back()->with('success', 'File Saved Successfully!');
        return redirect()->route('conferencesworkshop')->with('status', 'File Saved Successfully!');
    }


    public function storeconferencelogo(Request $request)
    {
        $this->validate($request, [
            'conference_logo' => 'mimes:png,jpeg,jpg,gif|max:1000',
        ]);

        $store =  new conferenceLogos();
        $store->conference_link = $request->conference_link;
        $store->conf_id = $request->postid;
        if (!empty($request->file('conference_logo'))) {
            $file = $request->file('conference_logo');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('/uploads/conference'), $filename);
            $path = '/uploads/conference/' . $filename;
            $store->conference_logo = $path;
        }
        $store->save();


        // return redirect()->back()->with('success', 'File Saved Successfully!');
        return redirect()->route('conferencesworkshop')->with('status', 'File Saved Successfully!');
    }

    public function updateconferences(Request $request)
    {
        
   
        $this->validate($request, [
            'bannerimage' => 'mimes:png,jpeg,jpg,gif|max:3000',
            'image_file' => 'mimes:png,jpeg,jpg,gif|max:3000',
            'date' => 'required',
            'expire_date' => 'required',
            'title_en' => 'required',
            'abbreviation' => 'required',
            'status' => 'required',

        ]);

        // $page_slug = $this->generateUniqueSlug($request->title_en);
        // $page_slug = $this->generateUniqueSlug(substr($request->title_en, 0, -1)); // Remove "end" from the end
        // dd($this->generateUniqueSlug);
        $title = $request->title_en;
        if (str_ends_with($title, '-1')) {
            $title = substr($title, 0, -2); // Remove the "-1"
        }

        $base_slug = $this->generateUniqueSlug($title);
        $page_slug = str_ends_with($base_slug, '-1') ? substr($base_slug, 0, -2) : $base_slug;


        $update = Conferences::where('id', $request->id)->first();
        // dd($update);
        $update->title_en = $request->title_en;
        $update->registration_status = $request->registration_status;
        $update->slug = $page_slug;

        $update->title_hi = $request->title_hi;
        $update->tab_name = $request->abbreviation;
        $update->status = $request->status;
        $update->description_en = $request->description_en;
        $update->description_hi = $request->description_hi;
        $update->date = $request->date;
        $update->expire_date = $request->expire_date;

        // dd($update);

        // photo
        $banner_img = "";
        if (!empty($request->file('bannerimage'))) {
            $feature_image = $request->file('bannerimage');
            $image = time() . '.' . $feature_image->getClientOriginalExtension();
            $feature_image->move(public_path('/uploads/images'), $image);
            $banner_img =  $image;
        } else {
            $banner_img = $update->bannerimage;
        }
        $update->bannerimage = $banner_img;


        $image_path = "";
        if (!empty($request->file('image_file'))) {
            $feature_image = $request->file('image_file');
            $image = time() . '.' . $feature_image->getClientOriginalExtension();
            $feature_image->move(public_path('/uploads/conference'), $image);
            $image_path = '/uploads/conference/' . $image;
        } else {
            $image_path = $update->image_file;
        }
        $update->image_file = $image_path;

        $image_registration_path = "";
        if (!empty($request->file('registration_background_image'))) {
            $feature_image = $request->file('registration_background_image');
            $image = time() . '.' . $feature_image->getClientOriginalExtension();
            $feature_image->move(public_path('/uploads/conference'), $image);
            $image_registration_path = '/uploads/conference/' . $image;
        } else {
            $image_registration_path = $update->registration_background_image;
        }
        $update->registration_background_image = $image_registration_path;
        $update->registration_details = $request->registration_details;
        $update->registration_details_for_ind = $request->registration_details_for_ind;
        $update->registration_details_for_other = $request->registration_details_for_other;
        $update->last_of_registration = $request->last_of_registration;
        $update->last_of_absract_submission = $request->last_of_absract_submission;
        $update->absract_submission_guideline = $request->absract_submission_guideline;
        // broucher_pdf
        $broucher = '';
        if (!empty($request->file('broucher_pdf'))) {
            $broucher_pdf = $request->file('broucher_pdf');
            $broucher = time() . '.' . $broucher_pdf->getClientOriginalExtension();
            $broucher_pdf->move(public_path('/uploads/conference'), $broucher);
            $broucher = '/uploads/conference/' . $broucher;
        } else {
            $broucher = $update->broucher_pdf;
        }

        // dd($broucher);
        $update->broucher_pdf = $broucher;

        // e_book_of_absraction
        $e_book = '';
        if (!empty($request->file('e_book_of_absraction'))) {
            $e_book_of_absraction = $request->file('e_book_of_absraction');
            $e_book_file = time() . '.' . $e_book_of_absraction->getClientOriginalExtension();
            $e_book_of_absraction->move(public_path('/uploads/conference'), $e_book_file);
            $e_book = '/uploads/conference/' . $e_book_file;
        } else {
            $e_book = $update->e_book_of_absraction;
        }


        $update->e_book_of_absraction = $e_book;

        $programme = '';
        if (!empty($request->file('programme_pdf'))) {
            $programme_pdf = $request->file('programme_pdf');
            $programme_file = time() . '.' . $programme_pdf->getClientOriginalExtension();
            $programme_pdf->move(public_path('/uploads/conference'), $programme_file);
            $programme = '/uploads/conference/' . $programme_file;
        } else {
            $programme = $update->programme_pdf;
        }


        $update->programme_pdf = $programme;
        //  scientific_program_of_TCS_file 
        $scientificProgram = '';
        if (!empty($request->file('scientific_program_of_TCS_file'))) {
            $scientific_program_of_TCS_file = $request->file('scientific_program_of_TCS_file');
            $scientific_program = time() . '.' . $scientific_program_of_TCS_file->getClientOriginalExtension();
            $scientific_program_of_TCS_file->move(public_path('/uploads/conference'), $scientific_program);
            $scientificProgram = '/uploads/conference/' . $scientific_program;
        } else {
            $scientificProgram = $update->scientific_program_of_TCS_file;
        }


        if ($request->hasFile('conference_logo')) {
            $uploadedLogos = [];

            foreach ($request->file('conference_logo') as $logo) {
                $filename = time() . '_' . $logo->getClientOriginalName();
                $logo->move(public_path('/uploads/conference'), $filename); // Adjust the storage path as needed
                $uploadedLogos[] = '/uploads/conference/' . $filename; // Store the file path without URL encoding
            }

            $update->conference_logo = json_encode($uploadedLogos); // Convert the array to a JSON string before saving
        }


        // $update->conference_logo = $uploadedLogos;
        // dd( $update->conference_logo);
        $update->scientific_program_of_TCS_file = $scientificProgram;
        $update->abriviation = $request->abriviation;
        $update->programme_link = $request->programme_link;
        $update->orgenising_comamitee = $request->orgenising_comamitee;
        $update->programme = $request->programme;
        $update->contact_details = $request->contact_details;
        $update->venue = $request->venue;
        $update->accommodation = $request->accommodation;
        $update->sponsorship_plan = $request->sponsorship_plan;

        $update->save();
        
        if ($request->sponsers ) {
            DB::table('conferences_sponser')->where('conference_id', $update->id)->delete();
            foreach ($request->sponsers as $sponserinfo) {
                $storeSponser = new ConferenceSponsers();
                $storeSponser->conference_id = $update->id;
                $storeSponser->sponser_id  = $sponserinfo;
                $storeSponser->save();
            }
        }

        if ($request->speakers ) {
            DB::table('conferences_speakers')->where('conference_id', $update->id)->delete();
            foreach ($request->speakers as $speaker) {
                $storeSponser = new conferenceSpeakers();
                $storeSponser->conference_id = $update->id;
                $storeSponser->sponser_id  = $speaker;
                $storeSponser->save();
            }
        }
        return redirect()->route('conferencesworkshop')->with('success', 'Data Added Successfully!');
    }
    ////////////////////////////////////////////////////////////////////
    public function removephoto(Request $request)
    {

        $imageId = $request->input('id');
        $type = $request->input('type');

        // dd($imageId);
        // $type = $request->input('imagetype');

        // Retrieve the image from the database
        $image = Conferences::where('id', $imageId)->first();

        // dd($image);
        if ($image) {
            $image->$type = null;
            $image->save();

            // Return a success response
            return response()->json(['status' => true]);
        }



        // Return an error response if the image is not found
        return response()->json(['status' => false]);
    }

    /////////////////////////////////////////////////////////////////////
    public function deleteconferencephoto(Request $request)
    {
        $id = $request->id;
        conferenceFiles::where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'feature Image Deleted Successfully'
        ]);
    }
    private function generateUniqueSlug($title)
    {
        $temp = str_slug($title, '-');
        if (!Conferences::all()->where('slug', $temp)->isEmpty()) {
            $i = 1;
            $newslug = $temp . '-' . $i;
            while (!Conferences::all()->where('slug', $newslug)->isEmpty()) {
                $i++;
                $newslug = $temp . '-' . $i;
            }
            $temp =  $newslug;
        }
        return $temp;
    }
    public function deleteconferenceAttachment(Request $request)
    {
        $id = $request->id;
        conferenceFiles::where('id', $id)->delete();

        return redirect()->back()->with('success', 'File deleted Successfully!');
    }

    public function deleteconferencelogo(Request $request)
    {
        $id = $request->id;
        conferenceLogos::where('id', $id)->delete();

        return redirect()->back()->with('success', 'File deleted Successfully!');
    }

    public function deleteconferences(Request $request)
    {

        $ids = $request->post_id;
        $func =  Conferences::where('id', $ids)->delete();
        if (!$func) {
            return response()->json(['status' => false, 'message' => 'record not found.']);
        }
        return response()->json(['status' => true]);
    }

    public function conferencestatus(Request $request)
    {

        // dd($request->all());
        $pageids = $request->post_id;
        $status_type = $request->status_type;
        Conferences::whereIn('id', $pageids)->update(['status' => $status_type]);
        return response()->json(['status' => true]);
    }
}
