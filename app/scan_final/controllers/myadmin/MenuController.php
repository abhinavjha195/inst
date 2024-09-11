<?php

namespace App\Http\Controllers\myadmin;

use App\Http\Controllers\Controller;

use App\Models\myadmin\Post;

use App\Models\myadmin\Menulevel;

use App\Models\myadmin\Quicklink;
use App\Models\myadmin\Copyright;

use Illuminate\Http\Request;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller {

     /**
     * @var array<string, string>
     */
    protected array $statusArrays;
	
    public function index(): View|Factory {
		//$this->statusArrays = array(''=>'Status', 'inactive'=>'inActive','active'=>'Active');

        $this->statusArrays = ['' => 'Status', 'inactive' => 'inActive', 'active' => 'Active'];
		$lists = Post::orderBy('pagename_en')->where('pagetype','pages')->get();
		
		$html_menu = $this->get_menuTree();
		
		return view('myadmin.menushtml',['lists' =>$lists, 'html_menu' => $html_menu ] );
		
    }

    public function store(Request $request): RedirectResponse {
		
		// $array_menu = json_decode($request->input('menu'), true);

		// Retrieve 'menu' from the request and ensure it's a string
		$menuJson = $request->input('menu', '');

		// Ensure $menuJson is a string
		if (!is_string($menuJson)) {
			$menuJson = ''; // Fallback or handle accordingly
		}
	
		// Decode the JSON string into an associative array
		$array_menu = json_decode($menuJson, true);
		
		// Menulevel::query()->truncate();
		
		// $this->updateMenu($array_menu);






		
    if (is_array($array_menu)) {
        // Proceed with updating the menu
        Menulevel::query()->truncate();
        $this->updateMenu($array_menu);
    } else {
        // Handle invalid JSON or non-array result gracefully
        // For example, you might want to log this or use a default value
        $array_menu = []; // Default to an empty array
      
        // Proceed with an empty menu or other default logic
        Menulevel::query()->truncate();
        $this->updateMenu($array_menu);
    }
		
		return Redirect::route('menus')->with('status', ' Menu has been saved successfully');
		
    }

	/**
 * @param array<mixed, array<string, mixed>> $menu
 * @param int $parentid
 */

 protected function updateMenu(array $menu, int $parentid = 0): void {
    if (!empty($menu)) {
        foreach ($menu as $value) {
            $menuname = $value['label'];
            $postid = empty($value['url']) ? '0' : $value['url'];

            $Menulevel = new Menulevel();
            $Menulevel->menuname = $menuname;
            $Menulevel->postid = $postid;
            $Menulevel->parentid = $parentid;

            $Menulevel->user_id = Auth::id(); // Correct usage
            $Menulevel->save();

            if (array_key_exists('children', $value)) {
				//@phpstan-ignore-next-line
                $this->updateMenu($value['children'], $Menulevel->id);
            }
        }
    }
}
    // protected function updateMenu(array $menu, int $parentid = 0): void {
	// 	if (!empty($menu)) {
			
	// 		foreach ($menu as $value) {
				
	// 			$menuname = $value['label'];
				
	// 			$postid = (empty($value['url'])) ? '0' : $value['url'];
				
	// 			$Menulevel = new Menulevel();
				
	// 			$Menulevel->menuname = $menuname;
				
	// 			$Menulevel->postid = $postid;
				
	// 			$Menulevel->parentid = $parentid;
				
	// 			// Replace this line:
	// 			// $Menulevel->user_id = Auth()->id();
	// 			// With this:
	// 			$Menulevel->user_id = Auth::id();
				
	// 			$Menulevel->save();
				
	// 			if (array_key_exists('children', $value))
					
	// 				$this->updateMenu($value['children'],$Menulevel->id);
	// 		}
	// 	}
	// }
	protected function get_menuTree(int $parentid = 0): string {
		$items = '';
		// @phpstan-ignore-next-line 
		$lists = Menulevel::select("*")->where('parentid',$parentid)->orderBy('id_menu','ASC')->get();
		if( !$lists->isEmpty() ){
			$items .= '<ol class="dd-list">';
			foreach ($lists as $row) {
				$items .= $this->renderMenuItem($row['id_menu'], $row['menuname'], $row['postid']);
				$items .= $this->get_menuTree($row['id_menu']);
				$items .= '</li>';
			}
			$items .= '</ol>';
		}
		return $items;
	}
	public function renderMenuItem(int $id, string $label, string $url): string {
		$return  = '
		<li class="dd-item dd3-item" data-id="' . $id . '" data-label="' . $label . '" data-url="' . $url . '">' .
        '<div class="dd-handle dd3-handle" > Drag</div>' .
        '<div class="dd3-content"><span>' . $label . '</span>' .
        '<div class="item-edit">Edit</div>' .
        '</div>' .
        '<div class="item-settings d-none">' .
        '<p><label for="">Menu Name<br><input type="text" name="navigation_label" value="' . $label . '"></label></p>' .
        '<input type="hidden" name="navigation_url" value="' . $url . '">' .
        '<p><a class="item-delete" href="javascript:;">Remove</a> |' .
        '<a class="item-close" href="javascript:;">Close</a></p>' .
        '</div>';
		return $return;
	}
	
	public function quicklinks(Request $request): View|Factory {
		
		//$search = $request->query('search');
		$menulist = Quicklink::query();
        // if (request('search')) {
        //     $menulist->where('name', 'Like', '%' . request('search') . '%');
        // }

		$search = $request->input('search', ''); // Default to empty string if not provided
 
		if (is_string($search) && $search !== '') {
			$menulist->where('name', 'like', '%' . $search . '%');
		}
		
        $menulists =  $menulist->orderBy('sortorder', 'ASC')->paginate(40);
		$lists = Post::orderBy('pagename_en')->where('pagetype','pages')->get();
		return view('myadmin.quicklinkshtml',['menulists' =>$menulists,'lists' =>$lists,'heading'=> 'Quick Links' ] );
		
    }
	
	public function createquicklinks(Request $request): View|Factory
	{
		$info = array();
		if (!empty($request->input('tokenid'))) {
			$info = Quicklink::where('id', $request->input('tokenid'))->first();
		}
		$this->statusArrays = array(''=>'Status', 'inactive'=>'inActive','active'=>'Active');
		$lists = Post::orderBy('pagename_en')->where('pagetype','pages')->get();
		
		return view('myadmin.modals.createquicklinkshtml', [
			'lists' => $lists,
			'info' => $info,
			'statusArrays' => $this->statusArrays,
			'heading' => 'Quick Links'
		]);
	}
	public function savequicklinks(Request $request): JsonResponse
{
    // Check if 'tokenid' is provided in the request
    if (!empty($request->input('tokenid'))) {
        // Update the existing Quicklink
        $quicklink = Quicklink::find($request->input('tokenid'));
        if ($quicklink) {
            $quicklink->name = $request->input('name');
            $quicklink->isactive = $request->input('isactive');
            $quicklink->url = $request->input('url');
            $quicklink->type = $request->input('type');
            $quicklink->type = ($request->input('pageid') == 'custommenu' ? 'custommenu' : $request->input('type'));
            $quicklink->pageid = ($request->input('pageid') == 'custommenu' ? '0' : $request->input('pageid'));
            $quicklink->user_id = Auth::id();
            $quicklink->save();

            return Response::json(['status' => true, 'message' => 'Content has been updated successfully'], 200);
        }
    } else {
        $validator = $request->validate(
            [
                'pageid' => 'required|max:500',
                'isactive' => 'required'
            ], 
            [
                'name.required' => 'The name is required',
                'isactive.required' => 'The status is required',
            ]
        );
        $list = new Quicklink();
        $list->name = $request->input('name');
        $list->isactive = $request->input('isactive');
        $list->url = $request->input('url');
        $list->type = $request->input('type');
        $list->type = ($request->input('pageid') == 'custommenu' ? 'custommenu' : $request->input('type'));
        $list->pageid = ($request->input('pageid') == 'custommenu' ? '0' : $request->input('pageid'));
        $list->user_id = Auth::id();
        $list->save();
    }

    return Response::json(['status' => true, 'message' => 'Content has been created successfully'], 200);
}

	
	public function destroyquicklinks(Request $request, int $id): RedirectResponse {
        Quicklink::where('id', $id)->delete();
        return Redirect::back()->with('status', ' Content has been removed successfully');
    }
	////////////////////////////////////////////////////////
	public function sortorderquicklinks(Request $request): JsonResponse
	{
        /** 
         * @var array<array{id: int, position: int, userid: int}> $orders
         */
		$orders = $request->input('sortorder');
		foreach ($orders as $order) {
			$id = $order['id'];
			$position = $order['position'];
			$userid = $order['userid'];
			// dd($albumid);
		
			// $list = Quicklink::where('user_id', $userid)
			// 				   ->where('id', $id)
			// 				   ->firstOrFail();
			// $list->sortorder = $position;
			// $list->save(); 


             // Retrieve a single Post model instance
            /** @var \App\Models\myadmin\Quicklink|null $list */
            $list = Quicklink::where('user_id', $userid)
            ->where('id', $id)
            ->firstOrFail();
				if ($list !== null) {
					// Ensure $list is not null before accessing properties
					$list->sortorder = $position;
					$list->save();
				}
		}
		
	
		return Response::json(['status' => 'success']);
	}
	/////////////////////////////////////////////////////////
	public function copyright(Request $request): View|Factory {
		$lists = Copyright::where('id', 1)->get();

		return view('myadmin.copyrighthtml',['lists'=>$lists,'heading'=> 'Copyright Text' ] );
		
    }

	public function googlesheet(Request $request): View|Factory {
		// $lists = Copyright::where('id', 1)->get();

		return view('myadmin.googlesheethtml',['heading'=> 'User Manual' ] );
		
    }
// 	public function updateCopyright(Request $request)
// {
//     $id = $request->input('id');
// 	dd($id);
//     $copyright = $request->input('copyright');

//     $list = Copyright::where('id', $id)->firstOrFail();
//     $list->copyright = $copyright;
//     $list->save();

//     return Response::json(['status' => 'success']);
// }

public function updateCopyright(Request $request): RedirectResponse
{
    $id = $request->input('id');
    $copyright = $request->input('copyright');
    
    $list = Copyright::find($id);
    
    if ($list) {
        $list->copyright = $copyright;
        $list->save();
        return Redirect::route('copyright')->with('status', ' Text Update successfully');
    }
    
    return Redirect::route('copyright')->with('status', 'Not Updated');
}


}
