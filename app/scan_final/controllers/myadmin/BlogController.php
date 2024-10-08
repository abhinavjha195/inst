<?php

namespace App\Http\Controllers\myadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\myadmin\Post;
use App\Models\myadmin\category;
use App\Models\myadmin\Conferences;
use App\Models\myadmin\Sponsers;
use App\Models\Speakers;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Contracts\View\Factory;

class BlogController extends Controller
{
    /**
     * @var array<int|string, string>
     */
    protected array $statusArrays;


    public function __Construct() {
		$this->statusArrays = array(''=>'Status', 0=>'inactive',1=>'Active');
	}

    public function indexconferencesworkshop(Request $request): View|Factory
    {
       // $search = $request->query('search');
        $catid = $request->query('catid');
        $query = Conferences::where('title_en','!=',''); 
        // ->orderBy('sortorder', 'ASC');	
        // if( request('search') ) {
        //     $query->where( 'title_en','LIKE','%'.request('search').'%');
        // }	

        
		$search = $request->input('search', ''); // Default to empty string if not provided
 
        if (is_string($search) && $search !== '') {
            $query->where('title_en', 'like', '%' . $search . '%');
        }
        $lists = $query->paginate(40);

        return view('myadmin.conferencesworkshop.listhtml',['lists' =>$lists, 'search'=>$search,'totalrecords'=>'Conferences & Workshop : '.$lists->total().' Records found'] );
    }

    public function createconferencesworkshop(): View|Factory
    {
        $scientists = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.*', 'userdetails.designation'])->where('users.roles', 'scientists')->orderBy('name', 'ASC')->get();
        // dd($scientists);
        $allsponsers = Sponsers::where('isactive','active')->get();
        // dd($allsponsers);
        $allSpeakers = Speakers::where('isactive','active')->get();
        // dd($allSpeakers);
        return view('myadmin.conferencesworkshop.createhtml',[
                'statusArrays'=>$this->statusArrays,
                'heading'=>'Conferences & Workshop',
                'scientists' => $scientists,
                'allsponsers' => $allsponsers,
                'allSpeakers' => $allSpeakers,
            ]
        );
    }

    public function editconferencesworkshop(int $id): View|Factory|RedirectResponse
    {
        $posts = Conferences::where('id',$id)->first();
        if($posts) {
            return view('myadmin.conferencesworkshop.edithtml', [
                    'statusArrays'=>$this->statusArrays,
                    'heading'=>'Conferences & Workshop',
                    'info'=>$posts
                ]
            );
        } else {
            return Redirect::route('conferencesworkshop')->with('status', 'Mentioned Id does not exist.');
        }  
    }

    public function workshopUpdateOrder(Request $request): JsonResponse
    {

		 /** 
         * @var array<array{id: int, position: int, type: string}> $orders
         */
        $orders = $request->input('order');
        foreach ($orders as $order) {
            $id = $order['id'];
            $position = $order['position'];
            $pagetype = $order['type'];

			 // Retrieve a single Post model instance
            /** @var \App\Models\myadmin\Post|null $list */
            $list = Post::where('id', $id)->where('pagetype',$pagetype)
                ->firstOrFail();
				if ($list !== null) {
					// Ensure $list is not null before accessing properties
					$list->sortorder = $position;
					$list->save();
				}
        }

        return Response::json(['status' => 'success']);
    }
}
