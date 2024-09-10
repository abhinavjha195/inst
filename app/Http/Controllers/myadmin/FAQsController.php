<?php

namespace App\Http\Controllers\myadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\myadmin\Faqs;
use App\Models\myadmin\category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;

class FAQsController extends Controller
{
     /**
     * @var array<string, string>
     */
    protected array $statusArrays;

    /**
     * @var Collection<int, category>
     */
    protected Collection $catlists;


    public function __Construct() {
		
		$this->statusArrays = array(''=>'Status', 'inactive'=>'inActive','active'=>'Active');
		
		$this->catlists = new Collection(category::query()
            ->select("id", "catname")
            ->where('type', 'activities')
            ->where('isactive', 'active')
            ->orderBy('catname', 'ASC')
            ->get());
	}

    public function index(Request $request): View|Factory
    {

        echo 'abcd';
        $data_query = Faqs::query();
        $search = $request->input('search');
        $catid = $request->input('catid');
        
        $query = Faqs::join('categories', 'categories.id', '=', 'faqs.catid')
                ->where('faqs.isactive','=','active')
                ->orderBy('faqs.id','DESC');    
        if ($request->has('search')) {
            $query->where('question', 'LIKE', '%' . $request->input('search') . '%');
        }    
        if ($request->has('catid')) {
            $query->where('catid', '=', $request->input('catid'));
        }
        $lists = $query->paginate(40,['faqs.*', 'categories.catname']);
        
        return view('myadmin.faqs.listhtml',['lists' =>$lists,'catlists'=>$this->catlists, 'search'=>$search, 'catid'=>$catid,'totalrecords'=>'FAQs : '.$lists->count().' Records found'] );
    }

    public function create(): View|Factory
    {
        return view('myadmin.faqs.createhtml',['catlists' => $this->catlists,'statusArrays'=>$this->statusArrays]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = $request->validate(
            [
                'catid' => 'required',
                'question' => 'required|max:100',
                'answer' => 'required',
                'isactive' => 'required'
            ], 
            [
                'catid.required' => 'The Category name is required',
                'question.required' => 'The Question is required',
                'answer.required' => 'The Answer is required',
                'isactive.required' => 'The Status is required',
            ]
        );
        $faqs = new Faqs();
        $faqs->question = $request->input('question');
        $faqs->answer = $request->input('answer');
        $faqs->isactive = $request->input('isactive');
        $faqs->catid = $request->input('catid');
        $faqs->user_id = Auth()->id();
        $faqs->save();
        return Redirect::route('faqs')->with('status', ' Faqs has been saved successfully');
    }

    public function edit(Request $request, int $id): View|Factory|RedirectResponse
    {
        $faqs = Faqs::where('id',$id)->first();
        
        if($faqs) {
            return view('myadmin.faqs.edithtml')
                ->with('catlists',$this->catlists)
                ->with('statusArrays',$this->statusArrays)
                ->with('info',$faqs);
        } else {
            return Redirect::route('faqs')->with('status', 'Mentioned Id does not exist.');
        }
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request_data = $request->all();       
        $validator = $request->validate(
            [
                'catid' => 'required',
                'question' => 'required|max:100',
                'answer' => 'required',
                'isactive' => 'required'
            ], 
            [
                'catid.required' => 'The Category name is required',
                'question.required' => 'The Question is required',
                'answer.required' => 'The Answer is required',
                'isactive.required' => 'The Status is required',
            ]
        );
        $faqs = Faqs::find($id);
        $faqs->question = $request->input('question');
        $faqs->answer = $request->input('answer');
        $faqs->isactive = $request->input('isactive');
        $faqs->catid = $request->input('catid');
        $faqs->user_id = Auth()->id();
        $faqs->save();
        return Redirect::route('faqs')->with('status', ' faqs has been updated successfully');
    }

    public function deletefaqs(Request $request): JsonResponse
    {
        $ids = $request->input('post_id');
        Faqs::whereIn('id',$ids)->delete();
        return Response::json(['status'=>true]);
    }    

    public function faqstatus(Request $request): JsonResponse
    {
        $ids = $request->input('post_id');
        $status_type = $request->input('status_type');
        Faqs::whereIn('id',$ids)->update(['isactive' => $status_type]);
        return Response::json(['status'=>true]);
    }
}
