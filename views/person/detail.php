<style>

.thumbnail {
    border: 0px;
}
.user-left{
	padding-left: 20px;
	padding-bottom: 25px;
}

.panel-tools{
	filter: alpha(opacity=1);
	-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=1)";
	-moz-opacity: 1;
	-khtml-opacity: 1;
	opacity: 1;
}

#btnTools{
	padding-right: 20px;
	padding-top: 10px;
}
</style>
<?php 
//if( isset($_GET["isNotSV"])) 
$this->renderPartial('../default/panels/toolbar'); 
?>
<div class="row">
	<div class="col-xs-12">
		<?php $this->renderPartial('dashboard/profil', array("person" => $person, "tags" => $tags, "countries" => $countries )); ?>
	</div>
</div>



<script>

jQuery(document).ready(function() {

	$(".changePasswordBtn").off().on("click",function () {
		openChangePasswordSV();
	})
});


</script>