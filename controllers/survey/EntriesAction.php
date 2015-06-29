<?php
class EntriesAction extends CAction
{
    public function run($id)
    {
      $controller=$this->getController();
      $where = array( "type"=>Survey::TYPE_ENTRY, "survey"=>$id );

      //check if is moderated in which the proper filter will be added to the where clause
      $moduleId = "pppm";//$this->moduleId
      $app = PHDB::findOne (PHType::TYPE_APPLICATIONS, array("key" => $moduleId  ) );
      $isModerator = Survey::isModerator(Yii::app()->session["userId"], $moduleId);

      if(!$isModerator && isset($app["moderation"]))
        $where['applications.'.$moduleId.'.'.SurveyType::STATUS_CLEARED] = array('$exists'=>false);

      $list = PHDB::find(Survey::COLLECTION, $where );
      $survey = PHDB::findOne (Survey::PARENT_COLLECTION, array("_id"=>new MongoId ( $id ) ) );
      $where["survey"] = $survey;

      $user = ( isset( Yii::app()->session["userId"])) ? PHDB::findOne (Person::COLLECTION, array("_id"=>new MongoId ( Yii::app()->session["userId"] ) ) ) : null;

      $uniqueVoters = PHDB::count( Person::COLLECTION, array("applications.survey"=>array('$exists'=>true)) );

      $controller->title = "Sondages : ".$survey["name"] ;
      $controller->subTitle = "Nombres de votants inscrit : ".$uniqueVoters;
      $controller->pageTitle = "Communecter - Sondages";
      $surveyLink = ( isset( $survey["parentType"] ) && isset( $survey["parentId"] ) ) ? Yii::app()->createUrl("/communecter/survey/index/type/".$survey["parentType"]."/id/".$survey["parentId"]) : Yii::app()->createUrl("/communecter/survey"); 
      $controller->toolbarMBZ = array(
        '<a href="'.$surveyLink.'" class="surveys" title="proposer une " ><i class="fa fa-bars"></i> SURVEYS</a>',
        '<a href="#" class="newVoteProposal" title="proposer une loi" ><i class="fa fa-paper-plane"></i> PROPOSER</a>',
        '<a href="#voterloiDescForm" role="button" data-toggle="modal" title="lexique pour compendre" ><i class="fa fa-question-circle"></i> AIDE</a>',
        );

      $controller->render( "index", array( "list" => $list,
                                           "where"=>$where,
                                           "user"=>$user,
                                           "isModerator"=>$isModerator,
                                           "uniqueVoters"=>$uniqueVoters )  );
    }
}