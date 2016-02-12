<?php 
 /**
  * Display the dashboard of the person
  * @param String $id Not mandatory : if specify, look for the person with this Id. 
  * Else will get the id of the person logged
  * @return type
  */
class DirectoryAction extends CAction
{
    public function run( $id=null )
    {
        $controller = $this->getController();

      //get The person Id
      if (empty($id)) {
          if ( empty( Yii::app()->session["userId"] ) ) {
              $controller->redirect(Yii::app()->homeUrl);
          } else {
              $id = Yii::app()->session["userId"];
          }
      }

      /* **************************************
      *  PERSON
      ***************************************** */
      $person = Person::getPublicData($id);

      $controller->title = ((isset($person["name"])) ? $person["name"] : "")."'s Directory";
      $controller->subTitle = (isset($person["description"])) ? $person["description"] : "";
      $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;

      /* **************************************
      *  EVENTS
      ***************************************** */
      $events=array();
      if(isset($person["links"]["events"]))
        {
              foreach ($person["links"]["events"] as $keyEv => $valueEv) 
              {
                $event = Event::getPublicData($keyEv);
                $events[$keyEv] = $event; 
              }
       }
      /*$events = Authorisation::listEventsIamAdminOf($id);
      $eventsAttending = Event::listEventAttending($id);
      foreach ($eventsAttending as $key => $value) {
        $eventId = (string)$value["_id"];
        if(!isset($events[$eventId])){
          $events[$eventId] = $value;
        }
      }*/
      //TODO - SBAR : Pour le dashboard person, affiche t-on les événements des associations dont je suis memebre ?
      //Get the organization where i am member of;

      /* **************************************
      *  ORGANIZATIONS
      ***************************************** */
      $organizations = array();
      if( isset($person["links"]) && isset($person["links"]["memberOf"])) 
      {
        
          foreach ($person["links"]["memberOf"] as $key => $member) 
          {
              $organization;
              if( $member['type'] == Organization::COLLECTION )
              {
                  $organization = Organization::getPublicData( $key );
                  $profil = Document::getLastImageByKey($key, Organization::COLLECTION, Document::IMG_PROFIL);
                  if($profil !="")
                    $organization["imagePath"]= $profil;
                  array_push($organizations, $organization );
              }
         
            if(isset($organization["links"]["events"]))
            {
              foreach ($organization["links"]["events"] as $keyEv => $valueEv) 
              {
                $event = Event::getPublicData($keyEv);
                $events[$keyEv] = $event; 
              }
            }
          }        
          //$randomOrganizationId = array_rand($subOrganizationIds);
          //$randomOrganization = Organization::getById( $subOrganizationIds[$randomOrganizationId] );
          //$params["randomOrganization"] = $randomOrganization;
          
      }

      /* **************************************
      *  PEOPLE
      ***************************************** */
      $people = array();
      if( isset( $person["links"] ) && isset( $person["links"]["knows"] )) {
        foreach ( $person["links"]["knows"] as $key => $member ) {
              if( $member['type'] == Person::COLLECTION )
              {
                $citoyen = Person::getPublicData( $key );
				if(!empty($citoyen)){
	                $profil = Document::getLastImageByKey( $key, Person::COLLECTION, Document::IMG_PROFIL );
	                if($profil !="" )
	                    $citoyen["imagePath"]= $profil;
	                array_push( $people, $citoyen );
                }
              }
        }
      }

      /* **************************************
      *  PROJECTS
      ***************************************** */
      $projects = array();
      if(isset($person["links"]["projects"])){
        foreach ($person["links"]["projects"] as $key => $value) {
          $project = Project::getPublicData($key);
          $profil = Document::getLastImageByKey($key, Project::COLLECTION, Document::IMG_SLIDER);
           if($profil !="")
                $project["imagePath"]= $profil;
          array_push( $projects, $project );
        }
      }
      $follows = array("citoyens"=>array(),
      					"projects"=>array(),
      					"organizations"=>array(),
      					"count" => 0
      			);
	  if (isset($person["links"]["follows"])){
		  $countFollows=0;
		   foreach ( $person["links"]["follows"] as $key => $member ) {
	              if( $member['type'] == Person::COLLECTION )
	              {
	                $citoyen = Person::getPublicData( $key );
					if(!empty($citoyen)){
		                $profil = Document::getLastImageByKey( $key, Person::COLLECTION, Document::IMG_PROFIL );
		                if($profil !="" )
		                    $citoyen["imagePath"]= $profil;
		                array_push( $follows[Person::COLLECTION], $citoyen );
	                }
	              }
	              if( $member['type'] == Organization::COLLECTION )
	              {
						$organization = Organization::getPublicData( $key );
						$profil = Document::getLastImageByKey($key, Organization::COLLECTION, Document::IMG_PROFIL);
						if($profil !="")
							$organization["imagePath"]= $profil;
						array_push($follows[Organization::COLLECTION], $organization );
					}
	              if( $member['type'] == Project::COLLECTION )
	              {
						$project = Project::getPublicData($key);
						$profil = Document::getLastImageByKey($key, Project::COLLECTION, Document::IMG_SLIDER);
						if($profil !="")
							$project["imagePath"]= $profil;
						array_push( $follows[Project::COLLECTION], $project );
	              }
	            $countFollows++;
        	}
			$follows["count"]= $countFollows;
	  }
      $followers = array();
	  if (isset($person["links"]["followers"])){
		   foreach ( $person["links"]["followers"] as $key => $member ) {
	              if( $member['type'] == Person::COLLECTION )
	              {
	                $citoyen = Person::getPublicData( $key );
					if(!empty($citoyen)){
		                $profil = Document::getLastImageByKey( $key, Person::COLLECTION, Document::IMG_PROFIL );
		                if($profil !="" )
		                    $citoyen["imagePath"]= $profil;
		                array_push( $followers, $citoyen );
	                }
	              }
        	}

	  }
      $params["organizations"] = $organizations;
      $params["projects"] = $projects;
      $params["events"] = $events;
      $params["people"] = $people;
      $params["type"] = Person::CONTROLLER;
      $params["person"] = $person;
      $params["followers"] = $followers;
      $params["follows"] = $follows;

		  $page = "../default/directory";
      if( isset($_GET[ "tpl" ]) )
        $page = "../default/".$_GET[ "tpl" ];
      
      if(Yii::app()->request->isAjaxRequest){
        echo $controller->renderPartial($page,$params,true);
      }
      else {
        $controller->render($page,$params);
      }
    }
}
