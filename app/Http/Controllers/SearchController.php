<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\myadmin\Post;
use Illuminate\Support\Facades\Route;

use App\Models\myadmin\category;

use App\Models\myadmin\Coordinator;

use Illuminate\Support\Facades\DB;
use App;

class SearchController extends Controller {
	 public function __construct() {
		$this->banners = get_banners();
		$this->menus_html = get_navbar();
		$this->webinfo = get_webinfo();
		$this->commondetailpage = array('activities','newsevents');
	}
	public function changelangauges(Request $request) {
		$current_url = url()->previous();
        App::setLocale($request->lang);
		session()->put('locale', $request->lang);
        return redirect()->to($current_url);
    }
	public function index( Request $request ) {
		$term = strip_tags($request->post('term'));
		$lists = Post::where('isactive','active')
			->orderBy('postdate','DESC')
			->where('pagename_en','LIKE','%'.$term.'%')
			->paginate(40);
		return view('searchhtml',['webinfo'=>$this->webinfo, 'lists' => $lists, 'banners' => $this->banners, 'menus_html' => $this->menus_html, 'totalrecords' => 'Search Results :  '.ucwords($term)]);
    }
	
	public function coreteammembers( Request $request ) {
		$catid = (!empty($request->query('category')) ? $request->query('category') : '' );
		$catlists = category::where('isactive','active')->where('type','teams')->orderBy('id','ASC')->get();	
		$coordinators = Coordinator::query();
		if( $catid ) {
			$coordinators->where('catid','=',$catid );
		}
		$lists = $coordinators->orderBy('id','DESC')->where('isactive','active')->where('type','teams ')->paginate(40);
		return view('teamshtml',['webinfo'=>$this->webinfo, 'lists' => $lists, 'banners' => $this->banners, 'menus_html' => $this->menus_html, 'catlists' => $catlists, 'catid' => $catid]);
	}

	public function sitemap() {
        $lists = Post::where('isactive','active')
			->orderBy('pagename_en','ASC')->get();
       return view('sitemap',['webinfo'=>$this->webinfo, 'sitemaplists_html' => $this->get_menulists(0), 'banners' => $this->banners, 'menus_html' => $this->menus_html]);
    }
	public function get_menulists( $parentid = 0 ) {
		$lists = DB::table('menulevels AS a')
				->join('posts AS b', 'a.postid', '=', 'b.id')
				->select('a.*','b.slug','b.target_blank','b.external_link')
				->where('a.parentid', $parentid)
				->where('b.isactive', 'active')
				->orderBy('a.id_menu', 'ASC')
				->get();
		$items = '<ul class="sitemap">';
		foreach ( $lists as $row ) {
			$items .= $this->renderMenuListItem_web($row->id_menu,$row->menuname,$row->parentid, $row->slug, $row->target_blank, $row->external_link);
			$items .= $this->get_menulists($row->id_menu);
			$items .= '</li>';
		}
		$items .= '</ul>';
		return $items;
	}
	public function renderMenuListItem_web( $id_menu, $menuname, $parentid, $page_slug, $target_blank = NULL, $external_link = NULL ) {
		$checkif_child_exist = checkif_child_exist_web($id_menu);
		if( $checkif_child_exist > 0 ) {
			$return = '<li class="main-sitemap"><a href="#">' . $menuname;
		} else {
			if( !empty($external_link) && $target_blank == '' ) {
				$return = '<li class="main-sitemap"><a href="'.$external_link.'">' . $menuname .'</a>';
			} else if( !empty($external_link) && $target_blank == 1 ) {
				$return = '<li class="main-sitemap" ><a href="'.$external_link.'" target="_blank">' . $menuname .'</a>';
			} else {
				if( $target_blank == 1) {
					$return = '<li class="main-sitemap"><a href="'.route('page_slug',['page_slug'=>$page_slug]).'" target="_blank">' . $menuname .'</a>';
				} else {
					$return = '<li class="main-sitemap"><a href="'.route('page_slug',['page_slug'=>$page_slug]).'">' . $menuname .'</a>';
				}
			}
		}
		return $return;
	}
}
