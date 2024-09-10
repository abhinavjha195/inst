<?php

namespace App\Http\Controllers\myadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\myadmin\Option;
use App\Models\Researchgroup;
use App\Models\Researchinterest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Contracts\View\Factory;

class OptionController extends Controller {
    public function index(): View|Factory
    {
        $id = 1;
        $option = Option::find($id);
        return view('myadmin.settings.settingshtml', ['info' => $option, 'totalrecords' => 'Website Settings']);
    }

    public function removeImage(Request $request): JsonResponse
    {
        $image = $request->input('image');
        
        if (!empty($image)) {
            $result = Option::where('featureimage', $image)
                             ->orWhere('featureimagetwo', $image)
                             ->select('featureimage', 'featureimagetwo')
                             ->first();
                             
            if ($result) {
                $field = $result->featureimage == $image ? 'featureimage' : 'featureimagetwo';
                Option::where($field, $image)->update([$field => null]);
                return Response::json(['success' => true]);
            }
        }
        
        return Response::json(['success' => false]);
    }
    
    public function update(Request $request, int $id): RedirectResponse
    {
        $validator = $request->validate(
            [
                'site_title' => 'required|max:200',
            ], 
            [
                'site_title.required' => 'The site title is required',
            ]
        );

        $option = Option::find($id);
        if ($option) {
            $option->site_title = $request->input('site_title');
            $option->email = $request->input('email');
            $option->mobile = $request->input('mobile');
            $option->aboutsexcerts = $request->input('aboutsexcerts');
            $option->aboutsexcerts_link = $request->input('aboutsexcerts_link');
            $option->meta_title = $request->input('meta_title');
            $option->meta_description = $request->input('meta_description');
            $option->videourl = $request->input('videourl');
            $option->address = $request->input('address');
            $option->meta_keywords = $request->input('meta_keywords');
            $option->coremembercontent = $request->input('coremembercontent');
            $option->maplink = $request->input('maplink');
            $option->aboutheading = $request->input('aboutheading');
            $option->facebook = $request->input('facebook');
            $option->youtube = $request->input('youtube');
            $option->twitter = $request->input('twitter');
            $option->linkdin = $request->input('linkdin');
            $option->user_id = Auth::id();

            if ($request->hasFile('featureimage')) {
                $featureimage = $request->file('featureimage');
                if ($featureimage instanceof \Illuminate\Http\UploadedFile) {
                    $featureimageName = Auth::id() . '_' . time() . '.' . $featureimage->getClientOriginalExtension();
                    $featureimage->move(public_path('uploads/images'), $featureimageName);
                    $option->featureimage = $featureimageName;
                }
            }

            if ($request->hasFile('featureimagetwo')) {
                $featureimagetwo = $request->file('featureimagetwo');
                if ($featureimagetwo instanceof \Illuminate\Http\UploadedFile) {
                    $featureimagetwoName = Auth::id() . '_' . time() . '.' . $featureimagetwo->getClientOriginalExtension();
                    $featureimagetwo->move(public_path('uploads/images'), $featureimagetwoName);
                    $option->featureimagetwo = $featureimagetwoName;
                }
            }

            $option->save();
            return Redirect::route('options')->with('status', 'Settings have been updated successfully');
        } else {
            return Redirect::route('options')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function sientistCommonchangeorder(Request $request): JsonResponse
    {
        $itemOrder = $request->input('item_order', []);
        $table = $request->input('Table', '');
        $type = $request->input('type', '');
        $sectionId = $request->input('sectionid');


         // Ensure $itemOrder is an array
    if (!is_array($itemOrder)) {
        $itemOrder = [];
    }

        foreach ($itemOrder as $sortorder => $autoid) {
            $sortorder = $sortorder + 1;
            
            if ($table === 'researchgroups') {
                Researchgroup::where([
                    'id' => $autoid, 
                    'userid' => Auth::id()
                ])->update(['sortorder' => $sortorder]);
            } elseif ($table === 'researchinterests') {
                $parameters = [
                    'id' => $autoid,
                    'userid' => Auth::id(),
                    'type' => $type
                ];
                
                if ($sectionId !== null) {
                    $parameters['sectionid'] = $sectionId;
                }
                
                Researchinterest::where($parameters)->update(['sortorder' => $sortorder]);
            }
        }

        return Response::json([
            'status' => true, 
            'message' => 'Order has been updated successfully'
        ]);
    }
}
