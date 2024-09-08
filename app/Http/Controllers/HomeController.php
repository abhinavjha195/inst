<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\myadmin\Coordinator;
use App\Models\myadmin\Albumimage;
use App\Models\myadmin\Conferences;
use App\Models\myadmin\conferenceSponser;
use App\Models\SpeakerAssignedCategories;
use App\Models\myadmin\Post;
use App\Models\myadmin\Faqs;
use App\Models\myadmin\category;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Section;
use App\Models\User;
use App\Models\Researchinterest;
use App\Models\Researchgroup;
use App\Models\conference_files;
use App;
use App\Models\conferenceSpeakers;
use PDO;

class HomeController extends Controller
{
	public function __construct()
	{
		$this->banners = get_banners();
		$this->menus_html = get_navbar(session()->get('locale'));
		$this->webinfo = get_webinfo();
		$this->commondetailpage = array('conferencesworkshop', 'announcements', 'newsupdates', 'latestupdates');
	}

	public function index()
	{
		if (!\Session::has('locale')) {
			\Session::put('locale', 'en');
		}

		$announcements = Coordinator::take(10)->where('type', 'announcements')->where('isactive', 'active')->orderBy('id', 'DESC')->get();
		$newsupdates = Coordinator::take(8)->where('type', 'newsupdates')->where('isactive', 'active')->orderBy('id', 'DESC')->get();
		$latestupdates = Coordinator::take(10)->where('type', 'latestupdates')->where('isactive', 'active')->orderBy('sortorder', 'ASC')->get();
		// dd($latestupdates);


		$latestupdatesarray = array();
		foreach ($latestupdates as $latestupdate) {

			$latestupdatesimg = Albumimage::where('albumid', $latestupdate->id)->orderBy('id', 'DESC')->first();
			// print_r($latestupdatesimg);


			if ($latestupdatesimg) {
				$latestupdatesarray[] = array(
					'id' => $latestupdate['id'],
					'name' => $latestupdate->name,
					'postdate' => $latestupdate->postdate,
					'imgid' => $latestupdatesimg['id'],
					'freature_img' => $latestupdatesimg['feature_image'],
					'feature_i' => $latestupdate['feature_img']
				);
			}else{
				$latestupdatesarray[] = array(
					'id' => $latestupdate['id'],
					'name' => $latestupdate->name,
					'postdate' => $latestupdate->postdate,
					// 'imgid' => $latestupdatesimg['id'],
					// 'freature_img' => $latestupdatesimg['feature_image'],
					'feature_i' => $latestupdate['feature_img']
				);
			}
		}

		// dd($latestupdatesarray);
		return view('homehtml', ['webinfo' => $this->webinfo, 'announcements' => $announcements, 'newsupdates' => $newsupdates, 'latestupdates' => $latestupdatesarray, 'banners' => $this->banners, 'menus_html' => get_navbar(session()->get('locale'))]);
	}
	public function single(Request $request, $page_slug)
	{
		$TodayDate = date('Y-m-d H:i:s');
		$lists = array();
		$topsectionlist = array();
		$sectionlists = array();
		$token = $request->token;
		$category = $request->category;
		$defaultview = 'default';
		$counterpage = '';
		$info = Post::where('slug', $page_slug)->where('isactive', 'active')->first();

		if (empty($info->pagename_en)) {

			$info = Coordinator::where('id', $page_slug)->where('isactive', 'active')->orderBy('id', 'DESC')->first();
			if (!empty($info->name)  && in_array($info->type, $this->commondetailpage)) {
				// dd($info);

				$this->partials = 'single-detail';
			} else {
				return redirect('/');
			}
			$infoimages = Albumimage::where('albumid', $info['id'])->orderBy('order', 'ASC')->get();
			$defaultview = (!empty($info->template) ? $info->template : $this->partials) . 'html';

			return view($defaultview, ['webinfo' => $this->webinfo, 'banners' => $this->banners, 'menus_html' =>  get_navbar(session()->get('locale')), 'info' => $info, 'sectionlists' => $sectionlists, 'infoimages' => $infoimages]);
		} else {
			if ($info->template == 'downloads') {
				// $topsectionlist = Coordinator::join('sections', 'sections.id', '=', 'coordinators.catid')->where('coordinators.type', 'formsdownloads')->where('sections.type', 'downloads')->where('coordinators.isactive', 'active')->distinct()->select(['sections.id', 'sections.sectionname', 'sections.sortorder'])->get();
				// dd($topsectionlist);
				$sectiondatas = Coordinator::join('sections', 'sections.id', '=', 'coordinators.catid')
				->where('coordinators.type', 'formsdownloads')
				->where('sections.type', 'downloads')
				->where('coordinators.isactive', 'active')
				->distinct()
				->select(['sections.id', 'sections.sectionname', 'sections.sortorder'])
				->get();
				$topsectionlist = $sectiondatas->sortBy('sortorder');
				
				$sectionlists = Coordinator::join('sections', 'sections.id', '=', 'coordinators.catid')->where('coordinators.type', 'formsdownloads')->where('sections.type', 'downloads')->where('coordinators.isactive', 'active')->distinct()->select(['sections.id', 'sections.sectionname']);



				if (!empty($category)) {
					$sectionlists->where('sections.id', '=', $category);
				}
				$lists = Coordinator::join('sections', 'sections.id', '=', 'coordinators.catid')
					->join('albumimages', 'albumimages.albumid', '=', 'coordinators.id')
					->where('coordinators.catid', '=', $category)
					->where('coordinators.isactive', 'active')
					->distinct()
					->select(['albumimages.*', 'coordinators.id', 'coordinators.catid'])
					->get();
				// dd($lists);
				$sectionlists = $sectionlists->orderBy('sections.sectionname', 'ASC')->get();


				// $results = $sectionlists->merge($results);
			} else 	if ($info->template == 'adminstaff') {
				$sectionlists = Section::where('isactive', 1)->where('type', 'adminstaff')->get();
				$lists = Coordinator::where('type', 'adminstaff')->where('isactive', 'active')->orderBy('id', 'DESC')->get();
			} else 	if ($info->template == 'deans') {


				$lists = Coordinator::where('type', 'deans')->where('isactive', 'active')->orderBy('id', 'DESC')->get();
			} else 	if ($info->template == 'annualreports') {


				$lists = Coordinator::where('type', 'annualreports')->where('isactive', 'active')->orderBy('id', 'DESC')->get();
			} else if ($info->template == 'albums') {


				$lists = Coordinator::where('type', 'albums')->where('isactive', 'active')->orderBy('sortorder', 'ASC')->get();
			} else if ($info->template == 'bogs') {

				$sectionlists = Section::where('isactive', 1)->where('type', 'bogs')->get();
				$lists = Coordinator::where('type', 'bogs')->where('isactive', 'active')->orderBy('id', 'DESC')->get();
			} else if ($info->template == 'raac') {

				$sectionlists = Section::where('isactive', 1)->where('type', 'raac')->get();
				$lists = Coordinator::where('type', 'raac')->where('isactive', 'active')->orderBy('id', 'DESC')->get();
			
			} else if ($info->template == 'technology') {


				$lists = Coordinator::where('type', 'technology')->where('isactive', 'active')->orderBy('id', 'DESC')->get();
			} else if ($info->template == 'mou') {


				$lists = Coordinator::where('type', 'mou')->where('isactive', 'active')->orderBy('id', 'DESC')->get();
			} else if ($info->template == 'conferencesworkshop') {

				// $lists = Conferences::where('status', 'active')->where('last_of_registration', '>', date('Y-m-d'))->orderBy('id', 'DESC')->get();
				$lists = Conferences::where('status', 'active')->where('expire_date', '>=', date('Y-m-d'))->orderBy('id', 'DESC')->get();
			} else if ($info->template == 'cif') {


				$lists = Coordinator::where('type', 'cif')->where('isactive', 'active')->orderBy('id', 'DESC')->get();
				// dd($lists);
			} else if ($info->template == 'tenders') {

				if ($token == 'archives') {
					$lists = Coordinator::where('type', 'tenders')->where('postenddate', '<=', $TodayDate)->where('isactive', 'active')->orderBy('postenddate', 'DESC')->paginate(50);
				} else {
					$lists = Coordinator::where('type', 'tenders')->where('postenddate', '>=', $TodayDate)->where('isactive', 'active')->orderBy('postenddate', 'DESC')->paginate(50);
				}
				// $counterpage = $lists->total();
				// if (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) {
				// 	$counterpage = ($counterpage - (($_REQUEST['page'] - 1) * 50));
				// }
				$totalItems = $lists->total();

				// Initialize $counterpage with the total number of items
				$counterpage = $totalItems;

				// Check if the 'page' parameter is set and not empty
				if (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) {
					// Calculate the starting counter value for the current page
					$currentPage = intval($_REQUEST['page']);
					$itemsPerPage = 50; // Change this to your actual items per page value
					$counterpage = $totalItems - (($currentPage - 1) * $itemsPerPage);
				}
			}
			// else if( $info->template == 'admissions' ) {
			// 	$sectionlists = Section::where('isactive',1)->where('type','admissions')->get();
			// 	$lists=Coordinator::where('type','admissions')->where('isactive','active')->orderBy('id','DESC')->get();
			// } 
			else if ($info->template == 'admissions') {
				$sectionlists = Section::where('isactive', 1)->where('type', 'admissions')->get();
				// dd($sectionlists);
				if ($token == 'archives') {
					$lists = Coordinator::where('type', 'admissions')->whereDate('postdate', '<=', $TodayDate)->orderBy('id', 'DESC')->get();
					// dd($lists);
				} else {

					$lists = Coordinator::where('type', 'admissions')->whereDate('postdate', '>=', $TodayDate)->where('isactive', 'active')->orderBy('id', 'DESC')->get();
					// dd($lists);
				}


				// dd($lists);
			} else if ($info->template == 'honoraryadjunctfaculty') {


				$sectionlists = Section::where('isactive', 1)->where('type', 'honoraryadjunctfaculty')->get();
				$lists = Coordinator::where('type', 'honoraryadjunctfaculty')->where('isactive', 'active')->orderBy('id', 'DESC')->get();
			} else if ($info->template == 'scientists') {


				$sectionlists = Section::whereNotIn('id', [1])->where('isactive', 1)->where('type', 'scientists')->get();
				$topsectionlist = Section::where('isactive', 1)->where('id', 1)->where('type', 'scientists')->first();
				$lists = Post::where('pagetype', 'student')->where('isactive', 'active')->orderBy('id', 'DESC')->get();
			} else if ($info->template == 'student') {


				$lists = Section::where('isactive', 1)->where('type', 'coregroupmembers')->get();
			} else if (in_array($info->pagetype, $this->commondetailpage)) {


				$this->partials = 'single-detail';
			}

			$defaultview = (!empty($info->template) ? $info->template : $this->partials) . 'html';


			return view($defaultview, ['webinfo' => $this->webinfo, 'banners' => $this->banners, 'page_slug' => $page_slug, 'lists' => $lists, 'menus_html' =>  get_navbar(session()->get('locale')), 'info' => $info, 'sectionlists' => $sectionlists, 'topsectionlist' => $topsectionlist, 'token' => $token, 'counterpage' => $counterpage, 'category' => $category]);
		}
	}
	public function galleryimages(Request $request, $albumid, $albumname)
	{
		$albumid = $request->albumid;
		$albuminfo = Coordinator::where('id', $albumid)->where('type', 'albums')->first();
		$albumimages = Albumimage::where('albumid', $albumid)->orderBy('id', 'DESC')->get();
		return view('galleryimageshtml', ['webinfo' => $this->webinfo, 'albumimages' => $albumimages, 'albuminfo' => $albuminfo, 'menus_html' =>  get_navbar(session()->get('locale'))]);
	}
	public function scientistfaculity(Request $request, $userid)
	{
		if (is_numeric($userid)) {
			$userInfo  = User::where('oldids', $userid)->first();
			$userid = $userInfo->id;

			return redirect()->route('scientistfaculity', ['slugurl' => url_slugify($userInfo->id . '-' . $userInfo->name)]);
		}
		$info  = User::join('userdetails', 'users.id', '=', 'userdetails.userid')->select(['users.id', 'users.name', 'users.sirname', 'users.email', 'userdetails.*'])->where('users.roles', 'scientists')->where('users.id', $userid)->first();
		$relatedimages  = Researchinterest::where('userid', $userid)->where('type', 'relatedimages')->orderBy('id', 'DESC')->get();
		$researchinterests  = Researchinterest::where('userid', $userid)->where('type', 'researchinterest')->orderBy('sortorder', 'ASC')->get();

		$researchhighlights = Researchinterest::where('userid', $userid)->where('isactive',1)->where('type', 'researchhighlights')->orderBy('sortorder', 'ASC')->get();
		// dd($researchhighlights);

		$publicationtabs = Researchinterest::join('sections', 'sections.id', '=', 'researchinterests.sectionid')->where('sections.type', 'researchpublications')->join('users', 'users.id', '=', 'researchinterests.userid')->where('researchinterests.userid', (int)$userid)->distinct()->select(['sections.*'])->orderBy('researchinterests.id', 'DESC')->get();

		$publicationtabsarray = [];
		foreach ($publicationtabs as $publicationtab) {
			if ($publicationtab->sectionname == 'Journal Publications') {
				$order = 1;
			} elseif ($publicationtab->sectionname == 'Books & Book Chapter') {
				$order = 2;
			} elseif ($publicationtab->sectionname == 'Conference Proceedings') {
				$order = 3;
			} elseif ($publicationtab->sectionname == 'Patents') {
				$order = 4;
			}

			$publicationtabsarray[] = array(
				'id' => $publicationtab->id,
				'sectionname' => $publicationtab->sectionname,
				'type' => $publicationtab->type,
				'order' => $order,
			);
		}
		$publicationtabsarray = collect($publicationtabsarray)->sortBy('tenure')->values()->all();



		$biodatatabs = Researchinterest::join('sections', 'sections.id', '=', 'researchinterests.sectionid')->where('sections.type', 'researchbiodata')->join('users', 'users.id', '=', 'researchinterests.userid')->where('researchinterests.userid', (int)$userid)->distinct()->select(['sections.*'])->get();
		// dd($userid);

		$coregroupmemberstabs = Researchgroup::join('sections', 'sections.id', '=', 'researchgroups.corembrid')->where('sections.type', 'coregroupmembers')->join('users', 'users.id', '=', 'researchgroups.userid')->where('researchgroups.isactive', 1)->where('researchgroups.userid', (int)$userid)->distinct()->select(['sections.*'])->orderBy('sections.id', 'ASC')->get();

		$researchgrouptabs = Researchgroup::join('sections', 'sections.id', '=', 'researchgroups.sectionid')->where('sections.type', 'researchgroup')->join('users', 'users.id', '=', 'researchgroups.userid')->distinct()->select(['sections.*'])->where('researchgroups.userid', (int)$userid)->orderBy('researchgroups.sectionid', 'ASC')->orderBy('researchgroups.sortorder', 'ASC')->get();

		$viewFile = 'scientistfaculityhtml';
		if ($request->page == 'new') {
			$viewFile = 'scientistfaculitychangeshtml';
		}
		return view($viewFile, [
			'webinfo' => $this->webinfo,
			'banners' => $this->banners,
			'info' => $info,
			'userid' => (int)$userid,
			'menus_html' =>  get_navbar(session()->get('locale')),
			'relatedimages' => $relatedimages,
			'researchinterests' => $researchinterests,
			'researchhighlights' => $researchhighlights,
			'publicationtabs' => $publicationtabsarray,
			'researchgrouptabs' => $researchgrouptabs,
			'coregroupmemberstabs' => $coregroupmemberstabs,
			'biodatatabs' => $biodatatabs,
		]);
	}
	public function studentdetails(Request $request, $sectionid)
	{
		// $yearlist = Researchgroup::
		// whereNotIn('workingsince', [0, 1970])->
		// selectRaw('year(workingsince) as workingyears');

		// if ((int)$sectionid == 14) {
		// 	$yearlist->where('sectionid', $sectionid);
		// } else {
		// 	$yearlist->where('corembrid', $sectionid);
		// }

		$yearlists = Researchgroup::whereNotIn('workingsince', [0, 1970])->orderBy('workingsince', 'desc')->select('workingsince')->groupBy('workingsince')->distinct()->get();
		$years = array();
		foreach ($yearlists as $yearlist) {
			$years[] = array(
				'year' =>  Carbon::parse($yearlist->workingsince)->format('Y')
			);
		}
		$years = array_unique($years, SORT_REGULAR);
		// dd($years);
		$info = Section::where('isactive', 1)->where('id', $sectionid)->first();
		return view('studentdetailshtml', [
			'webinfo' => $this->webinfo,
			'banners' => $this->banners,
			'info' => $info,
			'yearlists' => $years,
			'sectionid' => $sectionid,
			'menus_html' =>  get_navbar(session()->get('locale'))
		]);
	}
	public function studentdetails_ajax(Request $request)
	{

		$sectionid = $request->sectionid;

		$workingsince = ($request->year . '-' . $request->month);
		if ($sectionid == 14) {
			$lists = Researchgroup::join('sections', 'sections.id', '=', 'researchgroups.sectionid')
				->where('sections.type', 'researchgroup')->where('researchgroups.isactive', 1)
				// ->join('userdetails', 'researchgroups.userid', '=', 'userdetails.userid')
				->join('users', 'users.id', '=', 'researchgroups.userid')
				->join('sections as corembrsection', 'corembrsection.id', '=', 'researchgroups.corembrid')
				->whereDate('researchgroups.enddate', '<=', now()->toDateString())
				->whereYear('researchgroups.enddate', '=', $request->year)
				->select(['corembrsection.sectionname', 'researchgroups.name', 'researchgroups.customname', 'researchgroups.personalemail','researchgroups.presentaffiliation', 'researchgroups.regno', 'researchgroups.workingsince', 'researchgroups.enddate', 'researchgroups.interimage', 'researchgroups.userid', 'researchgroups.sectionid', 'researchgroups.isactive', 'researchgroups.sortorder', 'corembrsection.sectionname as scholarname', 'users.name as professorname', 'users.sirname as sname', 'researchgroups.enddate'])
				->orderBy('researchgroups.enddate', 'desc')
				->get();




			$otherslists = Researchgroup::join('sections', 'sections.id', '=', 'researchgroups.sectionid')
				->where('sections.type', 'researchgroup')->where('researchgroups.isactive', 1)->where('researchgroups.userid', 777)
				// ->join('userdetails', 'researchgroups.userid', '=', 'userdetails.userid')
				// ->join('users', 'users.id', '=', 'researchgroups.userid')
				->join('sections as corembrsection', 'corembrsection.id', '=', 'researchgroups.corembrid')
				->whereDate('researchgroups.enddate', '<=', now()->toDateString())
				->whereYear('researchgroups.enddate', '=', $request->year)
				->select(['corembrsection.sectionname', 'researchgroups.name', 'researchgroups.customname', 'researchgroups.personalemail','researchgroups.presentaffiliation', 'researchgroups.regno', 'researchgroups.workingsince', 'researchgroups.enddate', 'researchgroups.interimage', 'researchgroups.userid', 'researchgroups.sectionid', 'researchgroups.isactive', 'researchgroups.sortorder', 'corembrsection.sectionname as scholarname', 'researchgroups.enddate'])
				->get();

			$lists = $lists->concat($otherslists);
			// $lists =$lists->merge($otherslists);






			// dd($lists);
			// $lists  = User::join('researchgroups', 'users.id', '=', 'researchgroups.userid')->select(['users.name as professorname', 'users.sirname', 'researchgroups.*'])->where('researchgroups.sectionid', $sectionid)->whereMonth('researchgroups.workingsince', '=', $request->month)->whereYear('researchgroups.workingsince', '=', $request->year)->orderBy('researchgroups.workingsince', 'desc')->get();
		} else {

			$lists = User::join('researchgroups', 'users.id', '=', 'researchgroups.userid')->where('researchgroups.isactive', 1)
				// ->join('userdetails', 'users.id', '=', 'userdetails.userid')
				->select(['users.name as professorname', 'users.sirname', 'users.sirname as sname', 'researchgroups.*'])
				->where('researchgroups.corembrid', $sectionid)
				->whereNull('researchgroups.enddate')
				->where(function ($query) use ($request) {
					if ($request->month == 6) {
						$query->whereMonth('researchgroups.workingsince', '<=', 7);
					} elseif ($request->month == 12) {
						$query->whereMonth('researchgroups.workingsince', '>=', 8);
					} else {
						$query->whereMonth('researchgroups.workingsince', '=', $request->month);
					}
				})
				->whereYear('researchgroups.workingsince', '=', $request->year)
				// ->orderBy('researchgroups.workingsince', 'desc')
				->get();


			$otherslists = Researchgroup::where('researchgroups.isactive', 1)->where('userid', 777)
				// ->join('userdetails', 'users.id', '=', 'userdetails.userid')
				->select(['researchgroups.*'])
				->whereNull('researchgroups.enddate')
				->where('researchgroups.corembrid', $sectionid)
				->where(function ($query) use ($request) {
					if ($request->month == 6) {
						$query->whereMonth('researchgroups.workingsince', '<=', 7);
					} elseif ($request->month == 12) {
						$query->whereMonth('researchgroups.workingsince', '>=', 8);
					} else {
						$query->whereMonth('researchgroups.workingsince', '=', $request->month);
					}
				})
				->whereYear('researchgroups.workingsince', '=', $request->year)
				->orderBy('researchgroups.workingsince', 'desc')
				->get();

			$lists = $lists->concat($otherslists);
			// dd($lists);





		}

		return view('studentdetails_ajaxhtml', [
			'lists' => $lists
		]);
	}
	public function researchunites(Request $request, $sectionid)
	{
		$info = Section::where('isactive', 1)->where('id', $sectionid)->first();
		return view('researchuniteshtml', [
			'webinfo' => $this->webinfo,
			'banners' => $this->banners,
			'info' => $info,
			'yearlists' => $yearlists,
			'menus_html' =>  get_navbar(session()->get('locale'))
		]);
	}
	public function phpadmissions(Request $request)
	{
		/******Sectionid 24 is for PHP admissions */
		$sectionid = 24;
		$lists = Coordinator::where('type', 'admissions')->where('postdate', '>', date('Y-m-d'))->where('catid', $sectionid)->where('isactive', 'active')->orderBy('id', 'DESC')->get();
		return view('phpadmissionshtml', [
			'webinfo' => $this->webinfo,
			'banners' => $this->banners,
			'lists' => $lists,
			'menus_html' =>  get_navbar(session()->get('locale'))
		]);
	}



	public function single_conference(Request $request, $page_slug)
	{

		$TodayDate = date('Y-m-d H:i:s');
		$lists = array();
		$topsectionlist = array();
		$sectionlists = array();
		$token = $request->token;
		$category = $request->category;
		$defaultview = 'default';
		$counterpage = '';
		$info = Conferences::with('conferencespeakers.speakers', 'conferencesponsers.sponser')->where('slug', $page_slug)->where('status', 'active')->first();
		// $info = \Illuminate\Support\Facades\Cache::rememberForever('conference_' . $page_slug, function () use ($page_slug) {
		// 	return Conferences::with('conferencespeakers.speakers', 'conferencesponsers.sponser')
		// 		->where('slug', $page_slug)
		// 		->where('status', 'active')
		// 		->first();
		// });
	
		// dd($info);
		if (isset($info['id'])) {

			$bb = $info['id'];
			$slider = Conferences::leftJoin('conference_files', 'conferences.id', '=', 'conference_files.conference_id')
				->where('conferences.id', $bb)
				->get();
			$logos = Conferences::leftJoin('conference_logos', 'conferences.id', '=', 'conference_logos.conf_id')
				->where('conf_id', $bb)
				->get();
		}

		if (!empty($info)) {
			// dd($info);
			// echo "<pre/>";	print_r($info);die();
			foreach ($info['conferencespeakers'] as $value) {
				// dd($value);

				$categoryWiseData = [];

				if (!empty($info) && isset($info['conferencespeakers'])) {
					foreach ($info['conferencespeakers'] as $value) {
						if (isset($value->speakers) && is_object($value->speakers)) {
							$speakerId = $value->speakers->id;

							// Now, proceed to retrieve the categories using the speakerId
							$categorys = SpeakerAssignedCategories::with('category')
								->where('speaker_id', $speakerId)
								->get();

							foreach ($categorys as $category) {
								// Get the category title
								$categoryTitle = $category->category->title;

								// Create an entry in the $categoryWiseData array if it doesn't exist
								if (!isset($categoryWiseData[$categoryTitle])) {
									$categoryWiseData[$categoryTitle] = [];
								}

								// Add speaker information to the corresponding category
								if( $value->speakers->isactive == 'active') {
									$categoryWiseData[$categoryTitle][] = [
										'imagefile' => $value->speakers->image_file,
										'title_en' => $value->speakers->title_en,
										'description' => $value->speakers->description,
									];
								}
							}
						}
					}
				}
			}
		}
		// dd($categoryWiseData);
		if ($info === null) {
			// If $info is null, redirect back to the same page, triggering a fresh request
			return redirect()->back();
		}
		// if( $info->id == 49) {
		// 	return view('conference_details_cunstom', ['webinfo' => $this->webinfo, 'banners' => $this->banners, 'page_slug' => $page_slug, 'lists' => $lists, 'categoryWiseData' => isset($categoryWiseData) ? $categoryWiseData : [], 'slider' => $slider, 'logos' => $logos, 'menus_html' =>  get_navbar(session()->get('locale')), 'info' => $info, 'sectionlists' => $sectionlists, 'topsectionlist' => $topsectionlist, 'token' => $token, 'counterpage' => $counterpage, 'category' => $category]);
		// } else {
			return view('conference-details', ['webinfo' => $this->webinfo, 'banners' => $this->banners, 'page_slug' => $page_slug, 'lists' => $lists, 'categoryWiseData' => isset($categoryWiseData) ? $categoryWiseData : [], 'slider' => $slider, 'logos' => $logos, 'menus_html' =>  get_navbar(session()->get('locale')), 'info' => $info, 'sectionlists' => $sectionlists, 'topsectionlist' => $topsectionlist, 'token' => $token, 'counterpage' => $counterpage, 'category' => $category]);
		//}
	}

	public function upcommingconferences()
	{

		$info = Post::where('slug', 'conferences-and-workshop')->where('isactive', 'active')->first();

		// $lists = Conferences::where('status', 'active')->where('last_of_registration', '<', date('Y-m-d'))->orderBy('id', 'DESC')->get();
		// $lists = Conferences::where('status', 'active')->where('expire_date', '<', date('Y-m-d'))->orderBy('id', 'DESC')->get();
		
		$lists = Conferences::where('status', 'active')->where('expire_date', '<', date('Y-m-d'))->orderBy('date', 'DESC')->get();

		// return  view('upcomingconferences')->with(['info' =>$lists , 'info' =>$info 'menus_html' =>  get_navbar(session()->get('locale')), 'webinfo' => $this->webinfo, 'banners' => $this->banners]);

		return view('upcomingconferences', ['webinfo' => $this->webinfo, 'banners' => $this->banners, 'lists' => $lists, 'menus_html' =>  get_navbar(session()->get('locale')), 'info' => $info,]);
	}

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function testupcomming()
	{

		$info = Post::where('slug', 'conferences-and-workshop')->where('isactive', 'active')->first();
		$lists = Conferences::where('status', 'inactive')->where('last_of_registration', '<', date('Y-m-d'))->orderBy('id', 'DESC')->get();
		// return  view('upcomingconferences')->with(['info' =>$lists , 'info' =>$info 'menus_html' =>  get_navbar(session()->get('locale')), 'webinfo' => $this->webinfo, 'banners' => $this->banners]);

		return view('testupcomming', ['webinfo' => $this->webinfo, 'banners' => $this->banners, 'lists' => $lists, 'menus_html' =>  get_navbar(session()->get('locale')), 'info' => $info,]);
	}

	public function jyotidesigner()
	{

		$info = Post::where('slug', 'conferences-and-workshop')->where('isactive', 'active')->first();
		$lists = Conferences::where('status', 'active')->where('last_of_registration', '<', date('Y-m-d'))->orderBy('id', 'DESC')->get();
		// return  view('upcomingconferences')->with(['info' =>$lists , 'info' =>$info 'menus_html' =>  get_navbar(session()->get('locale')), 'webinfo' => $this->webinfo, 'banners' => $this->banners]);

		return view('jyotidesigner', ['webinfo' => $this->webinfo, 'banners' => $this->banners, 'lists' => $lists, 'menus_html' =>  get_navbar(session()->get('locale')), 'info' => $info,]);
	}
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function conferences_and_workshop(Request $request)
	{
		$TodayDate = date('Y-m-d H:i:s');
		$lists = array();
		$topsectionlist = array();
		$sectionlists = array();
		$token = $request->token;
		$category = $request->category;
		$defaultview = 'default';
		$counterpage = '';
		$info = Post::where('slug', 'conferences-and-workshop')->where('isactive', 'active')->first();

		$lists = Conferences::where('status', 'inactive')->orderBy('id', 'DESC')->get();
		// dd($lists);
		return view('conferencetest', ['webinfo' => $this->webinfo, 'banners' => $this->banners, 'page_slug' => 'conferences-and-workshop', 'lists' => $lists, 'menus_html' =>  get_navbar(session()->get('locale')), 'info' => $info, 'sectionlists' => $sectionlists, 'topsectionlist' => $topsectionlist, 'token' => $token, 'counterpage' => $counterpage, 'category' => $category]);
	}

public function testphpadmissionform ()
{
	return view('testphpadmissionform');

}

	public function designerjyoti(Request $request, $page_slug)
	{

		$TodayDate = date('Y-m-d H:i:s');
		$lists = array();
		$topsectionlist = array();
		$conferencesponserlists = array();
		$sectionlists = array();
		$token = $request->token;
		$category = $request->category;
		$defaultview = 'default';
		$counterpage = '';
		$info = Conferences::with('conferencespeakers.speakers', 'conferencesponsers.sponser')->where('slug', $page_slug)->where('status', 'inactive')->first();
		// dd($info);

		// foreach ($info->conferencesponsers as $sponser) {
		// 	$conferencesponserlists[] = $sponser->sponser;
		// }
		// usort($conferencesponserlists, function($a, $b) {
		// 	return $a->sortorder > $b->sortorder ? 1 : -1;
		// });

		if (isset($info['id'])) {

			$bb = $info['id'];
			$slider = Conferences::leftJoin('conference_files', 'conferences.id', '=', 'conference_files.conference_id')
				->where('conferences.id', $bb)
				->get();
			$logos = Conferences::leftJoin('conference_logos', 'conferences.id', '=', 'conference_logos.conf_id')
				->where('conf_id', $bb)
				->get();
			//  dd($logos); 
		}

		if (!empty($info)) {
			// dd($info);
			// echo "<pre/>";	print_r($info);die();
			foreach ($info['conferencespeakers'] as $value) {
				// dd($value);

				$categoryWiseData = [];

				if (!empty($info) && isset($info['conferencespeakers'])) {
					foreach ($info['conferencespeakers'] as $value) {
						if (isset($value->speakers) && is_object($value->speakers)) {
							$speakerId = $value->speakers->id;

							// Now, proceed to retrieve the categories using the speakerId
							$categorys = SpeakerAssignedCategories::with('category')
								->where('speaker_id', $speakerId)
								->get();

							foreach ($categorys as $category) {
								// Get the category title
								$categoryTitle = $category->category->title;

								// Create an entry in the $categoryWiseData array if it doesn't exist
								if (!isset($categoryWiseData[$categoryTitle])) {
									$categoryWiseData[$categoryTitle] = [];
								}

								// Add speaker information to the corresponding category
								$categoryWiseData[$categoryTitle][] = [
									'imagefile' => $value->speakers->image_file,
									'title_en' => $value->speakers->title_en,
									'description' => $value->speakers->description,
								];
							}
						}
					}
				}
			}
		}
		// dd($categoryWiseData);
		if ($info === null) {
			// If $info is null, redirect back to the same page, triggering a fresh request
			return redirect()->back();
		}


		if( $request->test == 'ajay') {
			return view('review-pagehtmlnew', ['conferencesponserlists'=>$conferencesponserlists,'webinfo' => $this->webinfo, 'banners' => $this->banners, 'page_slug' => $page_slug, 'lists' => $lists, 'categoryWiseData' => isset($categoryWiseData) ? $categoryWiseData : [], 'slider' => $slider, 'logos' => $logos, 'menus_html' =>  get_navbar(session()->get('locale')), 'info' => $info, 'sectionlists' => $sectionlists, 'topsectionlist' => $topsectionlist, 'token' => $token, 'counterpage' => $counterpage, 'category' => $category]);
		} else {
			return view('review-pagehtml', ['conferencesponserlists'=>$conferencesponserlists,'webinfo' => $this->webinfo, 'banners' => $this->banners, 'page_slug' => $page_slug, 'lists' => $lists, 'categoryWiseData' => isset($categoryWiseData) ? $categoryWiseData : [], 'slider' => $slider, 'logos' => $logos, 'menus_html' =>  get_navbar(session()->get('locale')), 'info' => $info, 'sectionlists' => $sectionlists, 'topsectionlist' => $topsectionlist, 'token' => $token, 'counterpage' => $counterpage, 'category' => $category]);
		}
	}
}
