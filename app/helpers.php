<?php

use App\Models\myadmin\Copyright;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

if( !function_exists('getcurrentUserRole') ) :
	function getcurrentUserRole() {
		$user = Auth::user();
		return $user->roles;
	}
endif;
if( !function_exists('getlistsBysections') ) :
	function getlistsBysections($sectionid, $roles = 'scientists') {
		$return = DB::table('userdetails')
			->join('users', 'users.id', '=', 'userdetails.userid')
			->select('userdetails.*','users.name','users.sirname','users.email')
			->where('userdetails.sectionid',$sectionid)
			->where('users.roles',$roles)
			->where('users.isactive',1)
			->orderBy('users.name', 'ASC')
			->get();
		return $return;
	}
endif;
if( !function_exists('getsinglepagename') ) :
	function getsinglepagename($pageid) {
		$return = DB::table('posts')
			->where('id',$pageid)
			->first();
		return $return->pagename_en;
	}
endif;
if( !function_exists('getFooterquicklink') ) :
	function getFooterquicklink() {
		$menulists = DB::table('quicklinks')->where('isactive','active')->orderBy('sortorder', 'ASC')->get();
		$html = '<ul class="quick_links">';
		if( !empty($menulists) ) {
			foreach( $menulists as $menulist ) {
				if( $menulist->type == 'custommenu' ) {
					$html .= '<li><a href="'.$menulist->url.'"> '.$menulist->name.'</a></li>';
				} else {
					$html .= '<li><a href="'.permalink($menulist->pageid).'">'.GetlangPageHeading($menulist->pageid).'</a></li>';
				}
			}
		}
		$html .= '</ul>';
		return $html;
	}
endif;
if( !function_exists('getCordinatorsBysections') ) :
	function getCordinatorsBysections($sectionid, $search = NULL) {
		 if( !empty($search) ) {
			$return = DB::table('coordinators')
				->where('catid',$sectionid)
				->where('name', 'like', "%$search%")
				->orderBy('id', 'DESC')
				->get();
		 } else {
			$return = DB::table('coordinators')
				->where('catid',$sectionid)
				->orderBy('id', 'DESC')
				->get();
		 }
		 return $return;
	}
endif;

// if( !function_exists('getCordinatorsBysections') ) :
	// function getCordinatorsBysections($sectionid, $search = '') {
		// if( !empty($search) ) {
			// $return = DB::table('coordinators')
				// ->where('catid',$sectionid)
				// ->where('name', $search)
				// ->orderBy('id', 'DESC')
				// ->get();
		// } else {
			// $return = DB::table('coordinators')
				// ->where('catid',$sectionid);
				// ->orderBy('id', 'DESC')
				// ->get();
		// }
		// return $return;
	// }
// endif;
if( !function_exists('getAdminMenulists') ) :
	function getAdminMenulists() {
		$return = DB::table('menus')
			->where('isactive',1)
			->orderBy('sortorder', 'ASC')
			->get();
		return $return;
	}
endif;
if( !function_exists('getSectionNameByid') ) :
	function getSectionNameByid($sectionid) {
		if( !empty($sectionid) ) {
			$return = DB::table('sections')
				->where('id',$sectionid)
				->first();
		} else {
			$return = new stdClass;
			$return->sectionname = 'NA'; 
		}
		return $return;
	}
endif;
if( !function_exists('getlistsResearchInterestBysections') ) :
	function getlistsResearchInterestBysections($sectionid, $type, $userid) {
		$return = DB::table('researchinterests')
			->where('sectionid',$sectionid)
			->where('type',$type)
			->where('isactive',1)
			->where('userid',$userid)
			->orderByRaw("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(tenure, ')', 1), '(', -1) AS UNSIGNED) DESC")
			//->whereNotNull('researchinterests.order')->orderBy('order','ASC')
			->get();
			// dd($return);
		return $return;
	}
endif;

if( !function_exists('getlistsResearchInterestBysectionsbiotab') ) :
	function getlistsResearchInterestBysectionsbiotab($sectionid, $type, $userid) {
		$return = DB::table('researchinterests')
			->where('sectionid',$sectionid)
			->where('type',$type)
			->where('isactive',1)
			->where('userid',$userid)
			->orderByRaw("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(tenure, ')', 1), '(', -1) AS UNSIGNED) DESC")
			//->whereNotNull('sortorder')->orderBy('sortorder','ASC')
			->get();
			// dd($return);
		return $return;
	}
endif;



if( !function_exists('getlistsResearchgroupsBysections') ) :
	function getlistsResearchgroupsBysections($corembrid, $userid, $checking = NULL) {
		$userArray = array(43,41,40,39,38,5,37,28,19,36,35,33,32,31,29,27,26,25,24,23,22,21,34,18,17,16,15,14,13,12,11,10,9,8,20);
		if( in_array($userid, $userArray) ) {
			$query = DB::table('researchgroups')->where('userid',$userid)->whereNull('enddate');
			$return = $query->orderBy('id', 'DESC')->where('isactive',1)->where('corembrid',$corembrid)->get();
		} else {
			$return = DB::table('researchgroups')
			->where('corembrid',$corembrid)
			->where('userid',$userid)
			->where('isactive',1)
			->orderBy('sortorder', 'DESC')
			->get();
		}
		return $return;
	}
endif;

if( !function_exists('getlistsAlumniBysections') ) :
	function getlistsAlumniBysections($sectionid, $userid, $checking = NULL) {
		$userArray = array(43,41,40,39,38,5,37,28,19,36,35,33,32,31,29,27,26,25,24,23,22,21,34,18,17,16,15,14,13,12,11,10,9,8,20);
		if( in_array($userid, $userArray) ) {
			$TodayDate = date('Y-m-d');
			$query = DB::table('researchgroups')->where('userid',$userid)->whereDate('enddate', '<', $TodayDate);
			$return = $query->orderBy('id', 'DESC')->where('isactive',1)->get();

		} else {
			$return = DB::table('researchgroups')
			->where('sectionid',$sectionid)
			->where('userid',$userid)
			->orderBy('id', 'DESC')
			->get();
		}
		return $return;
	}
endif;


if (!function_exists('getuserimage')) {
    function getuserimage( $file = NULL ){
        if ( !empty($file) ) {
            return asset('userpics/'.$file);
        } else {
            return asset('images/defaultuser.jpg');
        }
    }
}
if (!function_exists('getUploadsimage')) {
    function getUploadsimage( $file = NULL ){
        if ( !empty($file) ) {
            return asset('uploads/'.$file);
        } else {
            return asset('images/defaultuser.jpg');
        }
    }
}

if(!function_exists('permalink')) {
	function permalink( $pageid ) {
		$return = route('/');
		if( !empty($pageid) && is_numeric($pageid) ) {
			$pageinfo = DB::table('posts')->where('id',$pageid)->first();
			if( !empty($pageinfo)) {
				$return = route('page_slug',['page_slug'=>$pageinfo->slug]);
			}
		}
		return $return;
	}
}
if(!function_exists('GetlangPageHeading')) {
	function GetlangPageHeading( $pageid ) {
		$lang = session()->get('locale');
		if( !empty($pageid) && is_numeric($pageid) ) {
			$pageinfo = DB::table('posts')->where('id',$pageid)->first();
			if( !empty($pageinfo)) {
				if ( !empty($lang) && $lang == 'hi' && !empty($pageinfo->pagename_hi) ) {
					$return = $pageinfo->pagename_hi;
				} else {
					$return = $pageinfo->pagename_en;
				}
			}
		}
		return $return;
	}
}
if(!function_exists('GetlangPageContent')) {
	function GetlangPageContent( $pageid, $half = true ) {
		$return = '';

		$lang = session()->get('locale');
		if( !empty($pageid) && is_numeric($pageid) ) {
			$pageinfo = DB::table('posts')->where('id',$pageid)->first();
			if( !empty($pageinfo)) {
				if ( !empty($lang) && $lang == 'hi' && !empty($pageinfo->description_hi) ) {
					$return = !empty($half) ? substr(strip_tags($pageinfo->description_hi),0,800) : $pageinfo->description_hi ;
				} else {
					$return = !empty($half) ? substr(strip_tags($pageinfo->description_en),0,250) : $pageinfo->description_en ;
				}
			}
		}
		return $return.'...';
	}
}
if( !function_exists('get_navbar') ) :
	function get_navbar( $lang='en', $parentid = 0 ) {
	
		$lists = DB::table('menulevels AS a')
				->join('posts AS b', 'a.postid', '=', 'b.id')
				->select('a.*','b.slug','b.pagename_hi','b.target_blank','b.external_link')
				->where('a.parentid', $parentid)
				->where('b.isactive', 'active')
				->orderBy('a.id_menu', 'ASC')
				->get();
		$items = '';
		if( $parentid == 0 ) {
			$items .= '<ul class="navbar-nav ms-auto py-4 py-lg-0">';
		} else {
			$items .= '<ul class="dropdown-menu rounded-0 rounded-bottom m-0">';
		}
		foreach ( $lists as $row ) {
			$TranslatedMenu = ($lang == 'hi' && !empty($row->pagename_hi) ? $row->pagename_hi : $row->menuname );
			$items .= renderMenuItem_web($row->id_menu,$TranslatedMenu,$row->parentid, $row->slug, $row->target_blank, $row->external_link);
			$items .= get_navbar($lang, $row->id_menu);
			$items .= '</li>';
		}
		$items .= '</ul>';
		return $items;
	}
endif;
if( !function_exists('get_parentcatname') ) :
	function get_parentcatname( $parentid, $type ) {
		if( $type == 'blogs' ) {
			if( $parentid > 0 ) {
				$return = DB::table('categories')->where('id',$parentid)->first();
				return '<small style="font-style:italic;color:red;"> ( '.$return->catname.' )</small>';
			} else {
				return '<small style="font-style: italic;color: red;"> ( Parent ) </small>';
			}
		}
	}
endif;

if( !function_exists('renderMenuItem_web') ) :
	function renderMenuItem_web( $id_menu, $menuname, $parentid, $page_slug, $target_blank = NULL, $external_link = NULL ) {
		$checkif_child_exist = checkif_child_exist_web($id_menu);
		if( $checkif_child_exist > 0 ) {
			$return = '<li class="nav-item dropdown"><a href="javascrip:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">' . $menuname.'</a>';
		} else {
			if( !empty($external_link) && $target_blank == '' ) {
				$return = '<li><a class="nav-link dropdown-item" href="'.$external_link.'">' . $menuname .' </a>';
			} else if( !empty($external_link) && $target_blank == 1 ) {
				$return = '<li><a class="nav-link dropdown-item" href="'.$external_link.'" target="_blank">' . $menuname .'</a>';
			} else {
				if( $target_blank == 1) {
					$return = '<li><a class="nav-link dropdown-item" href="'.route('page_slug',['page_slug'=>$page_slug]).'" target="_blank">' . $menuname .' </a>';
				} else {
					$return = '<li><a class="nav-link dropdown-item" href="'.route('page_slug',['page_slug'=>$page_slug]).'">' . $menuname .' </a>';
				}
			}
		}
		return $return;
	}
endif;
if( !function_exists('url_slugify') ) :
	function url_slugify( $str, $delimiter = '-' ) {
		$slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
		return $slug;
	}
endif;
if( !function_exists('checkif_child_exist_web') ) :
	function checkif_child_exist_web( $parentid ) {
		$count = DB::table('menulevels AS a')
				->join('posts AS b', 'a.postid', '=', 'b.id')
				->select('a.*','b.slug','b.target_blank','b.external_link')
				->where('a.parentid', $parentid)
				->where('b.isactive', 'active')

				->count();
		//$count = DB::table('menulevels')->where('parentid', $parentid)->count();
		return $count;
	}
endif;
if( !function_exists('AlbumsubimageCount') ) :
	function AlbumsubimageCount($albumid) {
		$ReturnCount = DB::table('albumimages')->where('albumid',$albumid)->count();
		return $ReturnCount;
	}
endif;
if( !function_exists('get_webinfo') ) :
	function get_webinfo() {
		$options = DB::table('options')->where('id',1)->first();
		return $options;
	}
endif;
if( !function_exists('getpagesvideo') ) :
	function getpagesvideo($page_slug) {
		$lists = DB::table('posts')->where('slug',$page_slug)->select('you_tube_link','description_en')->first();
		return $lists;
	}
endif;

if( !function_exists('getsubimagesHtml') ) :
	function getsubimagesHtml($albumid) {
		$lists = DB::table('albumimages')->where('albumid',$albumid)->where('isactive',1)->orderBy('order','ASC')->get();
		return $lists;
	}
endif;


if( !function_exists('get_banners') ) :
	function get_banners() {
		$banners = DB::table('banners')->where('isactive',1)->get();
		return $banners;
	}
endif;
if(!function_exists('split_string')) {
    function split_string( $string, $words = 120 ) {
		$line = substr($string, 0,$words);
		return strip_tags($line);
	}
}
if( !function_exists('convertdate') ) :
	function convertdate( $postdate, $format = 'Y-m-d' ) {
		return date($format,strtotime($postdate));
	}
endif;

if(!function_exists('defaultdate')) {
    function defaultdate( $date, $time = true ) {
        $return = '';
		if(strlen($date) > 0) {
			if( $time ) {
				$return = date('d-M-Y h:i A',strtotime($date));
			} else {
				$return = date('d-M-Y',strtotime($date));
			}
        }
        return $return;
    }
}
if (!function_exists('get_thumbnail')) {
    function get_thumbnail( $url ){
        if ( !empty($url) ) {
            return $url;
        } else {
            return asset('images/default-inner-banner.jpg');
        }
    }
}
if (!function_exists('get_default_banner')) {
    function get_default_banner( $url ){
        if ( !empty($url) ) {
            return asset('uploads/images/'.$url);
        } else {
            return asset('images/default-inner-banner.jpg');
        }
    }
}
if (!function_exists('custom_link_url')) {
	function custom_link_url ($id, $title = NULL ){
		$slug = Str::slug($title, '-');
		return ($id.'-'.$slug);
	}
}
if(!function_exists('post_date')) {
    function post_date( $date, $blog = true ) {
        $return = '';
		if(strlen($date) > 0) {
			$return = date('d M Y',strtotime($date));
        }
        return $return; 
    }
}
if(!function_exists('BreadcrumbComponent')) {
    function BreadcrumbComponent($title, $pagebanner = NULL, $parentTitle = NULL) {
		$imgaePath = get_default_banner($pagebanner);
		$returnHtml = '
		<section class="container-fluid page-header" style="background: linear-gradient(rgba(7, 11, 59, 70%), rgba(7, 11, 59, 70%)), url('.$imgaePath.')">
			<div class="container py-5">
			<div class="row text-center">
				<h1 class="display-3 text-white mb-2 animated slideInDown">'.( !empty($parentTitle) ? $parentTitle : $title).'</h1>
				
			</div>
			</div>
	  	</section>';
		return $returnHtml;
    }
} 
if (!function_exists('Breadcrumblogo')) {
    function Breadcrumblogo($title, $pagebanner = NULL, $parentTitle = NULL) {
        $imgaePath = get_default_banner($pagebanner);

        // Assuming you have the necessary imports for the DB facade.
        $lists = DB::table('conferences')->select('date', 'expire_date')->where('title_en', $title)->get();


// Decode the JSON data into an array
$events = json_decode($lists, true);
foreach ($events as $event) {
    $startDate = new DateTime($event['date']);
    $endDate = new DateTime($event['expire_date']);

    $formattedStartDate = $startDate->format('j<\s\up>S</\s\up>');
	// dd($formattedStartDate);
    $formattedEndDate = $endDate->format('j<\s\up>S</\s\up> F Y');

    $formattedDateRange = $formattedStartDate . ' - ' . $formattedEndDate;

    
}
$returnHtml = '
<section class="container-fluid page-header" style="background: linear-gradient(rgba(7, 11, 59, 70%), rgba(7, 11, 59, 70%)), url(' . $imgaePath . ')">
    <div class="container py-5">
        <div class="row text-center">
            <h1 class="display-3 text-white mb-2 animated slideInDown">' . (!empty($parentTitle) ? $parentTitle : $title) .'<br><br>'.$formattedDateRange. '</h1>
       
        </div>
    </div>
</section>';

echo $returnHtml;
	}}
if(!function_exists('coreteam_home')) {
    function coreteam_home($catid) {
		$corelists = DB::table('coordinators')
				->select('id','name','designation','description','feature_image')
				->where('catid', $catid)
				->where('isactive', 'active')
				->where('type', 'teams')
				->orderBy('id', 'DESC')
				->take(3)
				->get();
		$ReturnHTML = '';
		if( !empty($corelists) ) {
			foreach ( $corelists as $corelist ) :
			$ReturnHTML .= '
				<div class="witr_all_mb_30 col-lg-4 col-md-4">
					<div class="team-part all_color_team ">
						<div class="witr_team_section member_pic">
							<img src="'.get_thumbnail($corelist->feature_image).'" alt="'.$corelist->name.'">
						</div>
						<div class="witr_team_content all_content_bg_color text-center">
							<p>'.split_string($corelist->description,260).'...</p>
							<h5><a href="'.permalink(65).'">'.$corelist->name.'<br>'.$corelist->designation.'</a></h5>
						</div>
					</div>
				</div>
				';
			endforeach;
		} else {
			$ReturnHTML .= 'No data found';
		}
		return $ReturnHTML;
	}
}

if(!function_exists('coreteam_homepage')) {
    function coreteam_homepage() {
		$corelists = DB::table('coordinators')
				->select('id','name','designation','description','feature_image')
				->where('isactive', 'active')
				->where('type', 'teams')
				->orderBy('id', 'DESC')
				->get();
		$ReturnHTML = '';
		if( !empty($corelists) ) {
			foreach ( $corelists as $corelist ) :
				// $ReturnHTML .= '
				// <div class="witr_all_mb_30  ">
				// 	<div class="team-part all_color_team ">
				// 		<div class="witr_team_section member_pic">
				// 			<img src="'.get_thumbnail($corelist->feature_image).'" alt="'.$corelist->name.'">
				// 		</div>
				// 		<div class="witr_team_content all_content_bg_color text-center">
				// 			<p>'.split_string($corelist->description,260).'...</p>
				// 			<h5><a href="'.permalink(65).'">'.$corelist->name.'<br>'.$corelist->designation.'</a></h5>
				// 		</div>
				// 	</div>
				// </div>
				// ';
				$ReturnHTML .= '<div class="item_pos col-lg-12 text-center"><div class="witr_single_pslide"><div class="witr_all_mb_30 col-lg-12 col-md-12"><div class="team-part all_color_team "><div class="witr_team_section member_pic"><img src="'.get_thumbnail($corelist->feature_image).'" alt="'.$corelist->name.'"></div><div class="witr_team_content all_content_bg_color text-center"><p>'.split_string($corelist->description,260).'...</p><h5><a href="'.permalink(65).'">'.$corelist->name.'<br>'.$corelist->designation.'</a></h5></div></div></div></div></div>';
			endforeach;
		} else {
			$ReturnHTML .= 'No data found';
		}
		return $ReturnHTML;
	}
}

if(!function_exists('newoldes_filters')) {
    function newoldes_filters( $select_opt = NULL ) {
		$returnHtml = '
			<select name="select_opt" class="form-control" onchange="this.form.submit();">
				<option value="new" '.(!empty($select_opt) && $select_opt == 'new' ? 'selected="selected"' : '').'>Newest</option>
				<option value="old" '.(!empty($select_opt) && $select_opt == 'old' ? 'selected="selected"' : '').'>Oldest</option>
			</select>
		';
        return $returnHtml;
    }
}

if(!function_exists('sessionYears_filter')) {
    function sessionYears_filter( $pagetype , $sessionyear = NULL ) {
		$lists = DB::table('posts')
				->distinct()
				->where('isactive', 'active')
				->where('pagetype', $pagetype)
				->orderBy('sessions', 'DESC')
				->select('sessions')
				->get();
		$returnHtml = '<select name="sessions" class="form-control" onchange="this.form.submit();"><option value="">Session year</option>';
			foreach( $lists as $list ) {
				$returnHtml .= '<option value="'.$list->sessions.'" '.($sessionyear == $list->sessions ? 'selected="selected"' : '').'>'.$list->sessions.'-'.($list->sessions+1).'</option>';
			}
		$returnHtml .= '</select>';
        return $returnHtml;
    }
}

if(!function_exists('related_posts')) {
    function related_posts( $pagetype , $id ) {
		$lists = DB::table('posts')
			->where('isactive', 'active')
			->where('pagetype', $pagetype)
			->whereNotIn('id',array($id))
			->take(9)
			->get();
        $ReturnHTML = '
		<div class="pad-t-b bg-grey">
			<div class="container">
				<div class="row">
					<div class="  witr_section_title_inner">
						<div class="col-lg-12 ">
							<h3>More '.$pagetype.'</h3>	
						</div>
						<br>
						<br>
					</div>
					<div class="witr_blog_area11 witr_blog_area16 col-md-12">
						<div class="blog_wrap row">';
							if( !empty($lists) ) {
								foreach ( $lists as $list ) :
									$ReturnHTML .= '
									<div class="witr_all_mb_30 col-md-4">
										<div class="busi_singleBlog">
											<div class="witr_sb_thumb">
												<a href="'.route('page_slug',['page_slug'=>$list->slug]).'">
													<img src="'.get_thumbnail($list->feature_image).'" alt="">
												</a>
											</div>
											<div class="all_blog_color">
												<div class="witr_blog_con bs5">
													<h2><span>'.post_date( $list->postdate).'</span><a href="'.route('page_slug',['page_slug'=>$list->slug]).'">'.split_string($list->pagename,40).'...</a></h2>
													<p>'.split_string($list->description,100).'...</p>
													<div class="em-blog-content-area_adn ">
														<div class="learn_more_adn"> <a class="learn_btn adnbtn2" href="'.route('page_slug',['page_slug'=>$list->slug]).'">Read More<i class="icofont-arrow-right"></i> </a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>';
								endforeach;
							}
							$ReturnHTML .= '
						</div>
					</div>
				</div>
			</div>
		</div>';
		return $ReturnHTML;
    }
}


if(!function_exists('blog_category')) {
	function blog_category($activetab = NULL, $parentactivetab = NULL ) {
		$return = '';
		$catlists = DB::table('categories')->where('isactive','active')->where('type','blogs')->where('parentid',0)->orderBy('id','ASC')->get();
		if( !empty($catlists)) {
			foreach( $catlists as $list ) {
				$maincatid = "parentcategories_".$list->id;
				$return .= '
				<div class="card card-2" id="parentcategories_'.$list->id.'" style="display:none;">
					<div class="witr_ac_card">';
						if( !empty($list->parentid)) {
							$return .= '<a href="'.URL('blogs?category='.custom_link_url($list->id,$list->catname)).'&p='.$list->id.'" class="card-link witr_ac_style collapsed" data-toggle="collapse" data-target="#categoires'.$list->id.'" aria-expanded="false">'.$list->catname.'</a>';
						} else {
							$return .= '<a href="'.URL('blogs?category='.custom_link_url($list->id,$list->catname)).'&p='.$list->id.'" class="card-link witr_ac_style collapsed '.($list->id == $parentactivetab ? 'active' : '').'" >'.$list->catname.'</a>';
						}
					$return .= '</div>
					<div id="categoires'.$list->id.'" class="collapse '.($list->id == $parentactivetab ? 'show' : '').'" data-parent="#accordion" style="">
						<ul>';
						$subcats = DB::table('categories')->where('isactive','active')->where('type','blogs')->where('parentid',$list->id)->orderBy('id','ASC')->get();
						if( !empty($subcats) ) {
							$return .= "<script>document.getElementById('$maincatid').style.display = 'block';</script>";
							foreach( $subcats as $subcat ) {
								$return .= '<li '.($subcat->id == (int)$activetab ? 'class="active"' : '').'><a href="'.URL('blogs?category='.custom_link_url($subcat->id,$subcat->catname)).'&p='.$list->id.'">'.$subcat->catname.'</a></li>';
							}
						}
						$return .= '</ul>
					</div>
				</div>
				';
			}
		}
		$return .= '';
		return $return;
	}
}
if(!function_exists('archive_blogs')) {
	function archive_blogs($activetab = NULL ) {
		$return = '<ul>';
		$results = DB::table('posts')->selectRaw("DATE_FORMAT(postdate, '%Y-%m') as postdate")->where('pagetype','blogs')->where('isactive','active')->orderBy('postdate','DESC')->get();
		if( !empty($results)) {
			foreach( $results as $list ) {
				$return .= '<li '.(date('Y-m',strtotime($list->postdate)) == $activetab ? 'class="active"' : '').'><a href="'.URL('blogs?month='.$list->postdate).'">'.date('M, Y',strtotime($list->postdate)).'</a></li>';
			}
		}
		$return .= '</ul>';
		return $return;
	}
}
if(!function_exists('admin_blogs_cats')) {
    function admin_blogs_cats( $selected = NULL ) {
		$returnHtml = '
			<select name="catid" class="form-control" required="required">
				<option value="">Category</option>';
			$results = DB::table('categories')->where('parentid',0)->where('type','blogs')->where('isactive','active')->orderBy('id','Asc')->get();
			if( !empty($results)) {
				foreach( $results as $catlist ) {
					$returnHtml .= '<optgroup label="'.$catlist->catname.'">';
					$subresults = DB::table('categories')->where('type','blogs')->where('isactive','active')->where('parentid',$catlist->id)->orderBy('id','Asc')->get();
					if( !empty($subresults)) {
						foreach( $subresults as $subcatlist ) {
							$returnHtml .= '<option value="'.$subcatlist->id.'" '.(!empty($selected) && $selected == $subcatlist->id ? 'selected="selected"' : '').'>'.$subcatlist->catname.'</option>';
						}
					}
					$returnHtml .= '</optgroup>';
				}
			}
			$returnHtml .= '</select>';
        return $returnHtml;
    }

	 function copyright() {
		$copy = Copyright::first();
		$copy=$copy['copyright'];
		return $copy;
		
    }
}
?>