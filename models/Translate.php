<?php 

class Translate {
	const FORMAT_SCHEMA = "schema";
	const FORMAT_PLP = "plp";
	const FORMAT_AS = "activityStream";
	const FORMAT_COMMUNECTER = "communecter";

	public static function convert($data,$bindMap)
	{
		$newData = array();
		foreach ($data as $keyID => $valueData) {
			if ( isset($valueData) ) {

				$newData[$keyID] = self::bindData($valueData,$bindMap);
				break;
			}
		}
		return $newData;
	}

	private static function bindData ( $data, $bindMap )
	{
		$newData = array();

		foreach ( $bindMap as $key => $bindPath ) 
		{

			if ( is_array( $bindPath ) && isset( $bindPath["valueOf"] ) ) 
			{
				/*if( $key == "@id")
					$newData["debug"] = strpos( $bindPath["valueOf"], ".");*/

				if( is_array( $bindPath["valueOf"] ))
				{
					//var_dump($bindPath["valueOf"]);
					//parse recursively for objects value types , ex links.projects
					if(isset($bindPath["object"]) )
					{
						//if dots are specified , we adapt the valueData map by focusing on a subpart of it
						//var_dump($bindPath["object"]);

						$currentValue = ( strpos( $bindPath["object"], "." ) > 0 ) ? self::getValueByPath( $bindPath["object"] ,$data ) : (!empty($data[$bindPath["object"]])?$data[$bindPath["object"]] : "" );
						
						//parse each entry of the list
						//var_dump(strpos( $bindPath["object"], "." ));
						if(!empty($currentValue)){
							$newData[$key] = array();
							foreach ( $currentValue as $dataKey => $dataValue) 
							{

								$refData = $dataValue;
								//if "collection" field  is set , we'll be fetching the data source of a reference object
								//we consider the key as the dataKey if no "refId" is set
								if( isset( $bindPath["collection"] ) ){
									if ( isset( $bindPath["refId"] ) ) 
										$dataKey = $bindPath["refId"];
									$refData = PHDB::findOne( $bindPath["collection"], array( "_id" => new MongoId( $dataKey ) ) );
								}
								$valByPath = self::bindData( $refData, $bindPath["valueOf"]);
								if(!empty($valByPath))
									array_push( $newData[$key] , $valByPath );
							}
						}
					} 
					//parse recursively for array value types, ex : address
					else if( isset($bindPath["parentKey"]) && isset( $data[ $bindPath["parentKey"] ] ) ){
						$valByPath = self::bindData( $data[ $bindPath["parentKey"] ], $bindPath["valueOf"] );
						if(!empty($valByPath))
							$newData[$key] = $valByPath;
						//resulting array has more than one level 
					}
					else{
						$valByPath = self::checkAndGetArray(self::bindData( $data, $bindPath["valueOf"]));

						if(!empty($valByPath))
							$newData[$key] = $valByPath;
					}
						
				} 
				else if( strpos( $bindPath["valueOf"], ".") > 0 )
				{
					//the value is fetched deeply in the source data map
					$valByPath = self::getValueByPath( $bindPath["valueOf"] ,$data );
					if(!empty($valByPath))
						$newData[$key] = $valByPath;
				}
				else if( isset( $data[ $bindPath[ "valueOf" ] ] )  )
				{
					//otherwise simply get the value of the requested element
					$valByPath = $data[ $bindPath["valueOf"] ];
					if(!empty($valByPath))
						$newData[$key] = $valByPath;
				} 

			}  else if( is_array( $bindPath )){
				// there can be a first level with a simple key value
				// but can have following more than a single level 
				$valByPath = self::bindData( $data, $bindPath ) ;

				if(!empty($valByPath))
						$newData[$key] = $valByPath;
			}	
			else
				// otherwise it's just a simple label element 
				$newData[$key] = $bindPath;

			//post processing once the data has been fetched
			if( isset($newData[$key]) && ( isset( $bindPath["type"] ) || isset( $bindPath["prefix"] ) || isset( $bindPath["suffix"] ) ) ) 
				$newData[$key] = self::formatValueByType( $newData[$key] , $bindPath );			
		}
		return $newData;
	}


	private static function getValueByPath( $path , $currentValue ){
		//The value is somewhere in an array position is definied in a json syntax
		//explode dot seperators
		$path = explode(".", $path);
		//follow path until the leaf value
		foreach ($path as $pathKey) 
		{	
			if(!empty($currentValue[ $pathKey ])){
				if( is_object($currentValue[ $pathKey ]) && get_class( $currentValue[ $pathKey ] ) == "MongoId" ){
					$currentValue = (string)$currentValue[ $pathKey ];
					break;
				} 
				else
					$currentValue = $currentValue[ $pathKey ];
			}else{
				$currentValue = "" ;
			}
			
		}
		return $currentValue;
	}

	private static function formatValueByType($val, $bindPath ){	
		//prefix and suffix can be added to anything
		$prefix = ( isset( $bindPath["prefix"] ) ) ? $bindPath["prefix"] : "";
		$suffix = ( isset( $bindPath["suffix"] ) ) ? $bindPath["suffix"] : "";
		
		if( isset( $bindPath["type"] ) && $bindPath["type"] == "url" )
		{
			$server = ((isset($_SERVER['HTTPS']) AND (!empty($_SERVER['HTTPS'])) AND strtolower($_SERVER['HTTPS'])!='off') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
			$val = $server.Yii::app()->createUrl($prefix.$val.$suffix);
			//$val = $server.Yii::app()->createUrl(Yii::app()->controller->module->id.$prefix.$val.$suffix);
		}
		else if( isset( $bindPath["type"] ) && $bindPath["type"] == "urlOsm" )
		{
			$val = $prefix.$val["latitude"]."/".$val["longitude"].$suffix;
		} 
		else if( isset( $bindPath["prefix"] ) || isset( $bindPath["suffix"] ) )
		{
			$val = $prefix.$val.$suffix;
		}
		return $val;
	}


	private static function checkAndGetArray($array){	
		$val = $array ;
		if(count($array) == 0 || (count($array) == 1 && !empty($array["@type"])))
			$val = null ;

		return $val;
	}

}