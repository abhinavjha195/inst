<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\myadmin\Post;
use App\Models\User;

class ContactusController extends Controller {
	public function reloadCaptcha() {
        return response()->json(['captcha'=> captcha_img()]);
    }
	public function sitemapxml() {
        $posts = Post::where('isactive','active')->get();
        $scientists = User::where('isactive',1)->where('roles','scientists')->get();
        return response()->view('sitemapxmlhtml', [
            'posts' => $posts,
            'scientists' => $scientists
        ])->header('Content-Type', 'text/xml');
      }
	
	public function formdata(Request $request) {
        $query = Contact::query();
		$type = $request->query('type');
		if( request('type') ) {
			$query->where('type','=',request('type'));
		}
		$lists = $query->orderBy('id','DESC')->paginate(40);
		return view('myadmin.formdata.formdatahtml',['lists' =>$lists, 'type'=>$type,'totalrecords'=>'Users : '.$lists->count().' Records found'] );
    }
	public function formdetails( $id ) {
        $info = Contact::where('id',$id)->first();
		return view('myadmin.formdata.formdetailshtml',['info' =>$info] );
    }
    
	public function store(Request $request) {
		$validator = \Validator::make($request->all(),
			[
				'name' => 'required|max:25',
				'email' => 'required|email:filter',
				'subject' => 'required|max:200',
				'message' => 'required|max:800',
				'captcha' => 'required|captcha'
			],
			['captcha.captcha' => 'Invalid captcha'],
			[
				'name.required' => 'The name is required',
				'email.required' => 'The email address is required',
				'subject.required' => 'The subject is required',
				'message.required' => 'The message is required',
				'captcha.required' => 'The captcha is required',
			]
		);
		if ($validator->fails()) {
			return response()->json(array('status'=>false,'message'=> $validator->errors()->all()), 200);
        }
		$formdata = new Contact();
		$formdata->name = $request->name;
		$formdata->email = $request->email;
		$formdata->subject = $request->subject;		
		$formdata->query = $request->message;
		$formdata->save();
		
		$subject = 'Enquiry form | INST ['.date('d-m-Y H:i:s').']';
		$EmailBody = '
			<h3>Below are the applicant details:</h3>
			<p>Name : '.$request->name.'</p>p>
			<p>Email address : '.$request->email.'</p>
			<p>Subject : '.$request->subject.'</p>
			<p>Message : '.$request->message.'</p>
		';
		//\Mail::to('mukul@cybrain.co.in')->send(new \App\Mail\MyTestMail($subject,$EmailBody));
		//\Mail::to('ajay@cybrain.co.in')->send(new \App\Mail\MyTestMail($subject,$EmailBody));
		
		/*************/
		$usersubject = 'Thank you for getting in touch!';
		$userEmailBody = '<h3>Thank you for getting in touch!</h3> 
			<p>We appreciate you contacting us. One of our colleagues will get back in touch with you soon!</p>
			<p>Have a great day!</p>';
		//\Mail::to($request->email)->send(new \App\Mail\MyTestMail($usersubject,$userEmailBody));
		/*************/
		return response()->json(array('status'=>true,'message'=> 'Thank you, Your Message has been sent successfully, We will get back to you soon'), 200);
		//return redirect()->back()->with('contactstatus', 'Thank you, Your Message has been sent successfully, We will get back to you soon');
    }
}
