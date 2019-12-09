<?php
// this is actually from a project i co-worked on 
    use GuzzleHttp\Client;
    use GuzzleHttp\RequestOptions;

    public function login (Request $request) {
        // this validates the form request
		$this->validate($request, [
			'LoginEmail' => ['required', 'regex:/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/']
		]);

		$status = "";
		try{
            // new guzzle client
			$client = new Client([
                // api url
                // using this is base url is coming from the env
                // 'base_uri' => env('base_url'),
                // or this for the app url can be the value
	            'base_uri' => env('www.api-url'),
	            'timeout'  => 19.0,
	            'verify' => false,
	            'headers' => [ 'Content-Type' => 'application/json' ],
            ]);
            // array of parameters to be sent to the api
			$vals = array('username' => $request->LoginEmail,
						   'password' => $request->LoginPassword,
						);
						// parsing the parameters on the body
			$response = $client->post('api/Account/Login', [ 'body' => json_encode($vals) ]);
			$result = json_decode($response->getBody()->getContents());
			
			$intended_url = Session::pull('referrer');
			// then get the response
			if($response->getStatusCode() == "200"){
				Session::put('logged_in', true);
				Session::put('user_roles', $result->Role);
				Session::put('name', $result->FirstName ." ". $result->LastName );
				Session::put('agency_id', $result->AgencyId);
				Session::put('user_id', $result->UserId);
				Session::put('easy_token', $result->Token);
				Session::put('email', $result->Username);

				$id = $result->Username;
				// Admin
				if($result->Role == "Admin"){
					return redirect()->to('/admin');
				}
				// User
				if($result->Role == "1"){	
						$response = $client->get("api/Users/GetUserProfileDetails?UserId=".$id);
						$profile_detail = json_decode($response->getBody()->getContents());
						
						if($profile_detail[0]->StateOfOrigin == null || $profile_detail[0]->StateOfOrigin  == "" ){
							return redirect()->to('/account/edit-profile');
						}
					return redirect()->to('/user');
				}
				// Agent
				if($result->Role == "2"){	
						$response = $client->get("api/Users/GetUserProfileDetails?UserId=".$id);
						$profile_detail = json_decode($response->getBody()->getContents());
						
						if($profile_detail[0]->StateOfOrigin == null || $profile_detail[0]->StateOfOrigin  == "" ){
							return redirect()->to('/account/edit-profile');
						}
					return redirect()->to('/user');
				}
				// SubAgent
				if($result->Role == "3"){	
						$response = $client->get("api/Users/GetUserProfileDetails?UserId=".$id);
						$profile_detail = json_decode($response->getBody()->getContents());
						
						if($profile_detail[0]->StateOfOrigin == null || $profile_detail[0]->StateOfOrigin  == "" ){
							return redirect()->to('/account/edit-profile');
						}
					return redirect()->to('/subagent');
				}
				// ContactCenterUser
				if($result->Role == "4"){	
						$response = $client->get("api/Users/GetUserProfileDetails?UserId=".$id);
						$profile_detail = json_decode($response->getBody()->getContents());
						
						if($profile_detail[0]->StateOfOrigin == null || $profile_detail[0]->StateOfOrigin  == "" ){
							return redirect()->to('/account/edit-profile');
						}
					return redirect()->to('/contact_center');
				}
				// OperationsUser
				if($result->Role == "5"){	
						$response = $client->get("api/Users/GetUserProfileDetails?UserId=".$id);
						$profile_detail = json_decode($response->getBody()->getContents());
						
						if($profile_detail[0]->StateOfOrigin == null || $profile_detail[0]->StateOfOrigin  == "" ){
							return redirect()->to('/account/edit-profile');
						}
					return redirect()->to('/operations');
				}
				// PlatformManger
				if($result->Role == "6"){	
						$response = $client->get("api/Users/GetUserProfileDetails?UserId=".$id);
						$profile_detail = json_decode($response->getBody()->getContents());
						
						if($profile_detail[0]->StateOfOrigin == null || $profile_detail[0]->StateOfOrigin  == "" ){
							return redirect()->to('/account/edit-profile');
						}
					return redirect()->to('/platform_manger');
				}
				// Authorizer
				if($result->Role == "7"){	
						$response = $client->get("api/Users/GetUserProfileDetails?UserId=".$id);
						$profile_detail = json_decode($response->getBody()->getContents());
						
						if($profile_detail[0]->StateOfOrigin == null || $profile_detail[0]->StateOfOrigin  == "" ){
							return redirect()->to('/account/edit-profile');
						}
					return redirect()->to('/authorizer');
				}
			} 
		    
		} catch (\Exception  $e){
			// dd($e); 
			if ( !method_exists($e, 'getResponse') || $e->getResponse() === null) {
				flash('Connection timeout! Easy30 cannot connect to the web service server', 'info');
				return redirect('/503');
			}

			if ( empty($e->getResponse()->getStatusCode()) ) return back();
			
			if($e->getResponse()->getStatusCode() == "400") {
				$result = json_decode($e->getResponse()->getBody()->getContents());
				
				Session::flash('loginerrors', $result->message);
				return redirect()->to('/account')->withInput($request->all());
			} elseif ($e->getResponse()->getStatusCode() == "500"){
				$result = json_decode($e->getResponse()->getBody()->getContents());
				dd($e);
				Session::flash('loginerrors', $result->message);
				return redirect()->to('/account')->withInput($request->all());
			}
			return redirect('errors/503');
		}
		
	}