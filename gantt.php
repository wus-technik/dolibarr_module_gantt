<?php

	require 'config.php';
	dol_include_once('/projet/class/project.class.php');
	dol_include_once('/projet/class/task.class.php');
	
	
?><!DOCTYPE HTML>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE"/>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <title>Gantt Project</title>

  <link rel=stylesheet href="lib/jquerygantt/platform.css" type="text/css">
  <link rel=stylesheet href="lib/jquerygantt/libs/dateField/jquery.dateField.css" type="text/css">

  <link rel=stylesheet href="lib/jquerygantt/gantt.css" type="text/css">
  <link rel=stylesheet href="lib/jquerygantt/ganttPrint.css" type="text/css" media="print">

  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>

  <script src="lib/jquerygantt/libs/jquery.livequery.min.js"></script>
  <script src="lib/jquerygantt/libs/jquery.timers.js"></script>
  <script src="lib/jquerygantt/libs/platform.js"></script>
  <script src="lib/jquerygantt/libs/date.js"></script>
  <script src="lib/jquerygantt/libs/i18nJs.js"></script>
  <script src="lib/jquerygantt/libs/dateField/jquery.dateField.js"></script>
  <script src="lib/jquerygantt/libs/JST/jquery.JST.js"></script>

  <link rel="stylesheet" type="text/css" href="lib/jquerygantt/libs/jquery.svg.css">
  <script type="text/javascript" src="lib/jquerygantt/libs/jquery.svg.min.js"></script>

  <!--In case of jquery 1.7-->
  <!--<script type="text/javascript" src="lib/jquerygantt/libs/jquery.svgdom.pack.js"></script>-->

  <!--In case of jquery 1.8-->
  <script type="text/javascript" src="lib/jquerygantt/libs/jquery.svgdom.1.8.js"></script>


  <script src="lib/jquerygantt/ganttUtilities.js"></script>
  <script src="lib/jquerygantt/ganttTask.js"></script>
  <script src="lib/jquerygantt/ganttDrawerSVG.js"></script>
  <!--<script src="ganttDrawer.js"></script>-->
  <script src="lib/jquerygantt/ganttGridEditor.js"></script>
  <script src="lib/jquerygantt/ganttMaster.js"></script>  
</head>
<body style="background-color: #fff;">

<div id="workSpace" style="padding:0px; overflow-y:auto; overflow-x:hidden;border:1px solid #e5e5e5;position:relative;margin:0 5px"></div>
<?php
   
	$fk_project = GETPOST('fk_project');

	if(empty($fk_project)) {
		$TProjectId=array();
		
		$resultset = $db->query("SELECT rowid FROM ".MAIN_DB_PREFIX."projet WHERE fk_statut=1");
		while($obj=$db->fetch_object($resultset)) {
			$TProjectId[] = $obj->rowid;
		}
		
		$collapsed = true;
		$with_task = false;
	}
	else{
		$TProjectId = array($fk_project);
		$collapsed = false;
		$with_task = true;
	}
	
	$TData = array('tasks'=>array(),"selectedRow"=>0,"canWrite"=>'false',"canWriteOnParent"=>'true');
	
	foreach($TProjectId as $fk_project) { 
	
		$project=new Project($db);
		$project->fetch($fk_project);
	//	var_dump(dol_print_date($project->date_start),dol_print_date($project->date_end),_get_nb_days($project->date_start,$project->date_end));exit;
		$taskstatic=new Task($db);
		$TTask = $taskstatic->getTasksArray(0, 0, $taskstatic->id, $project->socid, 0); 
		
		//TODO loop with TTask for project progress
		  // exit(dol_print_date($project->date_end));
		  $TData['tasks'][]=array(
		   		"id"=>'P'.$project->id
		   		,"name"=>$project->title
		   		,"code"=>$project->ref
		   		,"level"=>0
		   		,"status"=>_get_status($project->statut)
		   		,"canWrite"=>true
		   		,"start"=>$project->date_start * 1000
		   		,"duration"=>_get_nb_days($project->date_start,$project->date_end)
		   		,"end"=>$project->date_end * 1000
		   		,"startIsMilestone"=>true
		   		,"endIsMilestone"=>true
		   		,"collapsed"=>$collapsed
		   		,"assigs"=>array()
		   		,"hasChild"=>false
		   		
		   	
		   );
		  
		if($with_task) {
		   
		   foreach($TTask as &$task) {
				  
		   		$level = (!empty($task->fk_task_parent) ? 2 : 1);
		   	
		   		if($level === 1) $last_level1 = count($TData['tasks'])-1; 
		   		
		   		if(empty($task->date_end)) {
		   			$task->date_end = $task->date_start + ($task->planned_workload / 7 * 24 );
		   		}
		   		
		   		$TData['tasks'][]=array(
		   				"id"=>'T'.$task->id
		   				,"name"=>$task->label
		   				,"code"=>$task->ref
		   				,"level"=>$level
		   				,"status"=>_get_status($project->statut, $task->percent)
		   				,"canWrite"=>true
		   				,"start"=>$task->date_start * 1000
		   				,"duration"=>_get_nb_days($task->date_start,$task->date_end)
		   				,"end"=>$task->date_end * 1000
		   				,"startIsMilestone"=>false
		   				,"endIsMilestone"=>false
		   				,"collapsed"=>false
		   				,"assigs"=>array()
		   				,"hasChild"=>false
		   				,'progress'=>$task->progress
		   				,'description'=>$task->description
		   				,'depends'=>( $level>1 ? "$last_level1" :  '' )
		   				,'times'=>dol_print_date( $task->planned_workload, 'hourduration')
		   		);
		   	
		   }
		   
		}

	}
   
  function _get_status($status, $percent=0) {
  	
  		if($status == 1) {
  			
  			if($percent===100) return "STATUS_DONE";
  				
  				
  			return "STATUS_ACTIVE";
  			
  			
  			
  		}
  		else return "STATUS_UNDEFINED";
  	
  }
   
  // var_dump($TData);exit;
function _get_nb_days($t_start, $t_end) {
	
	$nb = ceil( ($t_end - $t_start) / 86400 ); 
	
	if($nb < 1) $nb = 1;
	
	return $nb;
	
}

/*
 * 
 * 
     {"tasks":[
     {"id":-1,"name":"Gantt editor","code":"","level":0,"status":"STATUS_ACTIVE","canWrite":true,"start":1396994400000,"duration":21,"end":1399672799999,"startIsMilestone":true,"endIsMilestone":false,"collapsed":false,"assigs":[],"hasChild":true}
     ,{"id":-2,"name":"coding","code":"","level":1,"status":"STATUS_ACTIVE","canWrite":true,"start":1396994400000,"duration":10,"end":1398203999999,"startIsMilestone":false,"endIsMilestone":false,"collapsed":false,"assigs":[],"description":"","progress":0,"hasChild":true}
     ,{"id":-3,"name":"gantt part","code":"","level":2,"status":"STATUS_ACTIVE","canWrite":true,"start":1396994400000,"duration":2,"end":1397167199999,"startIsMilestone":false,"endIsMilestone":false,"collapsed":false,"assigs":[],"depends":"","hasChild":false}
     ,{"id":-4,"name":"editor part","code":"","level":2,"status":"STATUS_SUSPENDED","canWrite":true,"start":1397167200000,"duration":4,"end":1397685599999,"startIsMilestone":false,"endIsMilestone":false,"collapsed":false,"assigs":[],"depends":"3","hasChild":false}
     ,{"id":-5,"name":"testing","code":"","level":1,"status":"STATUS_SUSPENDED","canWrite":true,"start":1398981600000,"duration":6,"end":1399672799999,"startIsMilestone":false,"endIsMilestone":false,"collapsed":false,"assigs":[],"depends":"2:5","description":"","progress":0,"hasChild":true}
     ,{"id":-6,"name":"test on safari","code":"","level":2,"status":"STATUS_SUSPENDED","canWrite":true,"start":1398981600000,"duration":2,"end":1399327199999,"startIsMilestone":false,"endIsMilestone":false,"collapsed":false,"assigs":[],"depends":"","hasChild":false}
     ,{"id":-7,"name":"test on ie","code":"","level":2,"status":"STATUS_SUSPENDED","canWrite":true,"start":1399327200000,"duration":3,"end":1399586399999,"startIsMilestone":false,"endIsMilestone":false,"collapsed":false,"assigs":[],"depends":"6","hasChild":false}
     ,{"id":-8,"name":"test on chrome","code":"","level":2,"status":"STATUS_SUSPENDED","canWrite":true,"start":1399327200000,"duration":2,"end":1399499999999,"startIsMilestone":false,"endIsMilestone":false,"collapsed":false,"assigs":[],"depends":"6","hasChild":false}
     ],"selectedRow":0,"canWrite":true,"canWriteOnParent":true}
 */
   
?>
<div id="taZone" style="display:none;" class="noprint">
   <textarea rows="8" cols="150" id="ta">
   <?php echo json_encode($TData); ?>
   </textarea>

  
</div>

<style>
  .resEdit {
    padding: 15px;
  }

  .resLine {
    width: 95%;
    padding: 3px;
    margin: 5px;
    border: 1px solid #d0d0d0;
  }

  body {
    overflow: hidden;
  }

  .ganttButtonBar h1{
    color: #000000;
    font-weight: bold;
    font-size: 28px;
    margin-left: 10px;
  }

</style>

<script type="text/javascript">

var ge;  //this is the hugly but very friendly global var for the gantt editor
$(function() {

  //load templates
  $("#ganttemplates").loadTemplates();

  // here starts gantt initialization
  ge = new GanttMaster();
  var workSpace = $("#workSpace");
  workSpace.css({width:$(window).width() - 20,height:$(window).height() - 100});
  ge.init(workSpace);

  //inject some buttons (for this demo only)
  $(".ganttButtonBar div").addClass('buttons');
  //overwrite with localized ones
  loadI18n();

  //simulate a data load from a server.
  loadGanttFromServer();


  //fill default Teamwork roles if any
  if (!ge.roles || ge.roles.length == 0) {
    setRoles();
  }

  //fill default Resources roles if any
  if (!ge.resources || ge.resources.length == 0) {
    setResource();
  }


  /*/debug time scale
  $(".splitBox2").mousemove(function(e){
    var x=e.clientX-$(this).offset().left;
    var mill=Math.round(x/(ge.gantt.fx) + ge.gantt.startMillis)
    $("#ndo").html(x+" "+new Date(mill))
  });*/

  $(window).resize(function(){
    workSpace.css({width:$(window).width() - 1,height:$(window).height() - workSpace.position().top});
    workSpace.trigger("resize.gantt");
  }).oneTime(150,"resize",function(){$(this).trigger("resize")});

});


function loadGanttFromServer(taskId, callback) {

  //this is a simulation: load data from the local storage if you have already played with the demo or a textarea with starting demo data
  loadFromLocalStorage();

  //this is the real implementation
  /*
  //var taskId = $("#taskSelector").val();
  var prof = new Profiler("loadServerSide");
  prof.reset();

  $.getJSON("ganttAjaxController.jsp", {CM:"LOADPROJECT",taskId:taskId}, function(response) {
    //console.debug(response);
    if (response.ok) {
      prof.stop();

      ge.loadProject(response.project);
      ge.checkpoint(); //empty the undo stack

      if (typeof(callback)=="function") {
        callback(response);
      }
    } else {
      jsonErrorHandling(response);
    }
  });
  */
}


function saveGanttOnServer() {
  if(!ge.canWrite)
    return;

  var prj = ge.saveProject();
  delete prj.resources;
  delete prj.roles;

  $.ajax({
		url:"<?php echo dol_buildpath('/gantt/script/interface.php',1) ?>"
		,type:'POST'
		,data:{
			'put':'projects'
			,TProject:prj
		}
  }).done(function(data) {
		alert('<?php echo $langs->trans('Done') ?>');
  });
  
}


//-------------------------------------------  Create some demo data ------------------------------------------------------
function setRoles() {
  ge.roles = [
    {
      id:"tmp_1",
      name:"Project Manager"
    },
    {
      id:"tmp_2",
      name:"Worker"
    },
    {
      id:"tmp_3",
      name:"Stakeholder/Customer"
    }
  ];
}

function setResource() {
  var res = [];
  for (var i = 1; i <= 10; i++) {
    res.push({id:"tmp_" + i,name:"Resource " + i});
  }
  ge.resources = res;
}


function editResources(){

}

function clearGantt() {
  ge.reset();
}

function loadI18n() {
  GanttMaster.messages = {
    "CANNOT_WRITE":"<?php echo $langs->trans('CANNOT_WRITE'); ?>",
    "CHANGE_OUT_OF_SCOPE":"<?php echo $langs->trans('NO_RIGHTS_FOR_UPDATE_PARENTS_OUT_OF_EDITOR_SCOPE'); ?>",
    "START_IS_MILESTONE":"<?php echo $langs->trans('START_IS_MILESTONE'); ?>",
    "END_IS_MILESTONE":"<?php echo $langs->trans('END_IS_MILESTONE'); ?>",
    "TASK_HAS_CONSTRAINTS":"<?php echo $langs->trans('TASK_HAS_CONSTRAINTS'); ?>",
    "GANTT_ERROR_DEPENDS_ON_OPEN_TASK":"<?php echo $langs->trans('GANTT_ERROR_DEPENDS_ON_OPEN_TASK'); ?>",
    "GANTT_ERROR_DESCENDANT_OF_CLOSED_TASK":"<?php echo $langs->trans('GANTT_ERROR_DESCENDANT_OF_CLOSED_TASK'); ?>",
    "TASK_HAS_EXTERNAL_DEPS":"<?php echo $langs->trans('TASK_HAS_EXTERNAL_DEPS'); ?>",
    "GANTT_ERROR_LOADING_DATA_TASK_REMOVED":"<?php echo $langs->trans('GANTT_ERROR_LOADING_DATA_TASK_REMOVED'); ?>",
    "ERROR_SETTING_DATES":"<?php echo $langs->trans('ERROR_SETTING_DATES'); ?>",
    "CIRCULAR_REFERENCE":"<?php echo $langs->trans('CIRCULAR_REFERENCE'); ?>",
    "CANNOT_DEPENDS_ON_ANCESTORS":"<?php echo $langs->trans('CANNOT_DEPENDS_ON_ANCESTORS'); ?>",
    "CANNOT_DEPENDS_ON_DESCENDANTS":"<?php echo $langs->trans('CANNOT_DEPENDS_ON_DESCENDANTS'); ?>",
    "INVALID_DATE_FORMAT":"<?php echo $langs->trans('INVALID_DATE_FORMAT'); ?>",
    "TASK_MOVE_INCONSISTENT_LEVEL":"<?php echo $langs->trans('TASK_MOVE_INCONSISTENT_LEVEL'); ?>",

    "GANTT_QUARTER_SHORT":"<?php echo $langs->trans('QuarterShort'); ?>",
    "GANTT_SEMESTER_SHORT":"<?php echo $langs->trans('SemesterShort'); ?>"
  };
}



//-------------------------------------------  Get project file as JSON (used for migrate project from gantt to Teamwork) ------------------------------------------------------
function getFile() {
  $("#gimBaPrj").val(JSON.stringify(ge.saveProject()));
  $("#gimmeBack").submit();
  $("#gimBaPrj").val("");

  /*  var uriContent = "data:text/html;charset=utf-8," + encodeURIComponent(JSON.stringify(prj));
   neww=window.open(uriContent,"dl");*/
}


//-------------------------------------------  LOCAL STORAGE MANAGEMENT (for this demo only) ------------------------------------------------------
Storage.prototype.setObject = function(key, value) {
  this.setItem(key, JSON.stringify(value));
};


Storage.prototype.getObject = function(key) {
  return this.getItem(key) && JSON.parse(this.getItem(key));
};


function loadFromLocalStorage() {
  var ret;

    ret = JSON.parse($("#ta").val());


    //actualiza data
   /* var offset=new Date().getTime()-ret.tasks[0].start;
    for (var i=0;i<ret.tasks.length;i++)
      ret.tasks[i].start=ret.tasks[i].start+offset;
*/


  ge.loadProject(ret);
  ge.checkpoint(); //empty the undo stack
}


function saveInLocalStorage() {
  var prj = ge.saveProject();
  if (localStorage) {save
    localStorage.setObject("teamworkGantDemo", prj);
  } else {
    $("#ta").val(JSON.stringify(prj));
  }
}


//-------------------------------------------  Open a black popup for managing resources. This is only an axample of implementation (usually resources come from server) ------------------------------------------------------

function editResources(){

  //make resource editor
  var resourceEditor = $.JST.createFromTemplate({}, "RESOURCE_EDITOR");
  var resTbl=resourceEditor.find("#resourcesTable");

  for (var i=0;i<ge.resources.length;i++){
    var res=ge.resources[i];
    resTbl.append($.JST.createFromTemplate(res, "RESOURCE_ROW"))
  }


  //bind add resource
  resourceEditor.find("#addResource").click(function(){
    resTbl.append($.JST.createFromTemplate({id:"new",name:"resource"}, "RESOURCE_ROW"))
  });

  //bind save event
  resourceEditor.find("#resSaveButton").click(function(){
    var newRes=[];
    //find for deleted res
    for (var i=0;i<ge.resources.length;i++){
      var res=ge.resources[i];
      var row = resourceEditor.find("[resId="+res.id+"]");
      if (row.size()>0){
        //if still there save it
        var name = row.find("input[name]").val();
        if (name && name!="")
          res.name=name;
        newRes.push(res);
      } else {
        //remove assignments
        for (var j=0;j<ge.tasks.length;j++){
          var task=ge.tasks[j];
          var newAss=[];
          for (var k=0;k<task.assigs.length;k++){
            var ass=task.assigs[k];
            if (ass.resourceId!=res.id)
              newAss.push(ass);
          }
          task.assigs=newAss;
        }
      }
    }

    //loop on new rows
    resourceEditor.find("[resId=new]").each(function(){
      var row = $(this);
      var name = row.find("input[name]").val();
      if (name && name!="")
        newRes.push (new Resource("tmp_"+new Date().getTime(),name));
    });

    ge.resources=newRes;

    closeBlackPopup();
    ge.redraw();
  });


  var ndo = createBlackPage(400, 500).append(resourceEditor);
}


</script>


<div id="gantEditorTemplates" style="display:none;">
  <div class="__template__" type="GANTBUTTONS"><!--
  <div class="ganttButtonBar noprint">
    <div class="buttons">
    <button onclick="$('#workSpace').trigger('addAboveCurrentTask.gantt');" class="button textual" title="insert above"><span class="teamworkIcon">l</span></button>
    <button onclick="$('#workSpace').trigger('addBelowCurrentTask.gantt');" class="button textual" title="insert below"><span class="teamworkIcon">X</span></button>
    <span class="ganttButtonSeparator"></span>
    <button onclick="$('#workSpace').trigger('indentCurrentTask.gantt');" class="button textual" title="indent task"><span class="teamworkIcon">.</span></button>
    <button onclick="$('#workSpace').trigger('outdentCurrentTask.gantt');" class="button textual" title="unindent task"><span class="teamworkIcon">:</span></button>
    <span class="ganttButtonSeparator"></span>
    <button onclick="$('#workSpace').trigger('moveUpCurrentTask.gantt');" class="button textual" title="move up"><span class="teamworkIcon">k</span></button>
    <button onclick="$('#workSpace').trigger('moveDownCurrentTask.gantt');" class="button textual" title="move down"><span class="teamworkIcon">j</span></button>
    <span class="ganttButtonSeparator"></span>
    <button onclick="print();" class="button textual" title="print"><span class="teamworkIcon">p</span></button>
    <span class="ganttButtonSeparator"></span>
    <button onclick="saveGanttOnServer();" class="button first big" title="save"><?php echo $langs->trans('Save') ?></button>
    </div></div>
  --></div>

  <div class="__template__" type="TASKSEDITHEAD"><!--
  <table class="gdfTable" cellspacing="0" cellpadding="0">
    <thead>
    <tr style="height:40px">
      <th class="gdfColHeader" style="width:35px;"></th>
      <th class="gdfColHeader" style="width:25px;"></th>
      <th class="gdfColHeader gdfResizable" style="width:100px;"><?php echo $langs->trans('Ref') ?></th>

      <th class="gdfColHeader gdfResizable" style="width:200px;"><?php echo $langs->trans('Title') ?></th>
      <th class="gdfColHeader gdfResizable" style="width:100px;"><?php echo $langs->trans('Times') ?></th>
      <th class="gdfColHeader gdfResizable" style="width:80px;"><?php echo $langs->trans('StartDate') ?></th>
      <th class="gdfColHeader gdfResizable" style="width:80px;"><?php echo $langs->trans('EndDate') ?></th>
      <th class="gdfColHeader gdfResizable" style="width:50px;"><?php echo $langs->trans('Duration') ?></th>
      <th class="gdfColHeader gdfResizable" style="width:50px;">dep.</th>
      <th class="gdfColHeader gdfResizable" style="width:200px;"><?php echo $langs->trans('Ressources') ?></th>
    </tr>
    </thead>
  </table>
  --></div>

  <div class="__template__" type="TASKROW"><!--
  <tr taskId="(#=obj.id#)" class="taskEditRow" level="(#=level#)">
    <th class="gdfCell edit" align="right" style="cursor:pointer;"><span class="taskRowIndex">(#=obj.getRow()+1#)</span> <span class="teamworkIcon" style="font-size:12px;" >e</span></th>
    <td class="gdfCell noClip" align="center"><div class="taskStatus cvcColorSquare" status="(#=obj.status#)"></div></td>
    <td class="gdfCell">(#=obj.code?obj.code:''#)</td>
    <td class="gdfCell indentCell" style="padding-left:(#=obj.level*10#)px;">
      <div class="(#=obj.isParent()?'exp-controller expcoll exp':'exp-controller'#)" align="center"></div>
      (#=obj.name#)
    </td>
	<td class="gdfCell">(#=obj.times#)</td>
    
    <td class="gdfCell"><input type="text" name="start"  value="" class="date"></td>
    <td class="gdfCell"><input type="text" name="end" value="" class="date"></td>
    <td class="gdfCell">(#=obj.duration#)</td>
    <td class="gdfCell"><input type="text" name="depends" value="(#=obj.depends#)" (#=obj.hasExternalDep?"readonly":""#)></td>
    <td class="gdfCell taskAssigs">(#=obj.getAssigsString()#)</td>
  </tr>
  --></div>

  <div class="__template__" type="TASKEMPTYROW"><!--
  <tr class="taskEditRow emptyRow" >
    <th class="gdfCell" align="right"></th>
    <td class="gdfCell noClip" align="center"></td>
    <td class="gdfCell"></td>
    <td class="gdfCell"></td>
    <td class="gdfCell"></td>
    <td class="gdfCell"></td>
    <td class="gdfCell"></td>
    <td class="gdfCell"></td>
    <td class="gdfCell"></td>
  </tr>
  --></div>

  <div class="__template__" type="TASKBAR"><!--
  <div class="taskBox taskBoxDiv" taskId="(#=obj.id#)" >
    <div class="layout (#=obj.hasExternalDep?'extDep':''#)">
      <div class="taskStatus" status="(#=obj.status#)"></div>
      <div class="taskProgress" style="width:(#=obj.progress>100?100:obj.progress#)%; background-color:(#=obj.progress>100?'red':'rgb(153,255,51);'#);"></div>
      <div class="milestone (#=obj.startIsMilestone?'active':''#)" ></div>

      <div class="taskLabel"></div>
      <div class="milestone end (#=obj.endIsMilestone?'active':''#)" ></div>
    </div>
  </div>
  --></div>

  <div class="__template__" type="CHANGE_STATUS"><!--
    <div class="taskStatusBox">
      <div class="taskStatus cvcColorSquare" status="STATUS_ACTIVE" title="active"></div>
      <div class="taskStatus cvcColorSquare" status="STATUS_DONE" title="completed"></div>
      <div class="taskStatus cvcColorSquare" status="STATUS_FAILED" title="failed"></div>
      <div class="taskStatus cvcColorSquare" status="STATUS_SUSPENDED" title="suspended"></div>
      <div class="taskStatus cvcColorSquare" status="STATUS_UNDEFINED" title="undefined"></div>
    </div>
  --></div>


  <div class="__template__" type="TASK_EDITOR"><!--
  <div class="ganttTaskEditor">
  <table width="100%">
    <tr>
      <td>
        <table cellpadding="5">
          <tr>
            <td><label for="code">code/short name</label><br><input type="text" name="code" id="code" value="" class="formElements"></td>
           </tr><tr>
            <td><label for="name">name</label><br><input type="text" name="name" id="name" value=""  size="35" class="formElements"></td>
          </tr>
          <tr></tr>
            <td>
              <label for="description">description</label><br>
              <textarea rows="5" cols="30" id="description" name="description" class="formElements"></textarea>
            </td>
          </tr>
        </table>
      </td>
      <td valign="top">
        <table cellpadding="5">
          <tr>
          <td colspan="2"><label for="status">status</label><br><div id="status" class="taskStatus" status=""></div></td>
          <tr>
          <td colspan="2"><label for="progress">progress</label><br><input type="text" name="progress" id="progress" value="" size="3" class="formElements"></td>
          </tr>
          <tr>
          <td><label for="start">start</label><br><input type="text" name="start" id="start"  value="" class="date" size="10" class="formElements"><input type="checkbox" id="startIsMilestone"> </td>
          <td rowspan="2" class="graph" style="padding-left:50px"><label for="duration">dur.</label><br><input type="text" name="duration" id="duration" value=""  size="5" class="formElements"></td>
        </tr><tr>
          <td><label for="end">end</label><br><input type="text" name="end" id="end" value="" class="date"  size="10" class="formElements"><input type="checkbox" id="endIsMilestone"></td>
        </table>
      </td>
    </tr>
    </table>

  <h2>assignments</h2>
  <table  cellspacing="1" cellpadding="0" width="100%" id="assigsTable">
    <tr>
      <th style="width:100px;">name</th>
      <th style="width:70px;">role</th>
      <th style="width:30px;">est.wklg.</th>
      <th style="width:30px;" id="addAssig"><span class="teamworkIcon" style="cursor: pointer">+</span></th>
    </tr>
  </table>

  <div style="text-align: right; padding-top: 20px"><button id="saveButton" class="button big"><?php echo $langs->trans('Save') ?></button></div>
  </div>
  --></div>


  <div class="__template__" type="ASSIGNMENT_ROW"><!--
  <tr taskId="(#=obj.task.id#)" assigId="(#=obj.assig.id#)" class="assigEditRow" >
    <td ><select name="resourceId"  class="formElements" (#=obj.assig.id.indexOf("tmp_")==0?"":"disabled"#) ></select></td>
    <td ><select type="select" name="roleId"  class="formElements"></select></td>
    <td ><input type="text" name="effort" value="(#=getMillisInHoursMinutes(obj.assig.effort)#)" size="5" class="formElements"></td>
    <td align="center"><span class="teamworkIcon delAssig" style="cursor: pointer">d</span></td>
  </tr>
  --></div>


  <div class="__template__" type="RESOURCE_EDITOR"><!--
  <div class="resourceEditor" style="padding: 5px;">

    <h2>Project team</h2>
    <table  cellspacing="1" cellpadding="0" width="100%" id="resourcesTable">
      <tr>
        <th style="width:100px;">name</th>
        <th style="width:30px;" id="addResource"><span class="teamworkIcon" style="cursor: pointer">+</span></th>
      </tr>
    </table>

    <div style="text-align: right; padding-top: 20px"><button id="resSaveButton" class="button big"><?php echo $langs->trans('Save') ?></button></div>
  </div>
  --></div>


  <div class="__template__" type="RESOURCE_ROW"><!--
  <tr resId="(#=obj.id#)" class="resRow" >
    <td ><input type="text" name="name" value="(#=obj.name#)" style="width:100%;" class="formElements"></td>
    <td align="center"><span class="teamworkIcon delRes" style="cursor: pointer">d</span></td>
  </tr>
  --></div>


</div>
<script type="text/javascript">
  $.JST.loadDecorator("ASSIGNMENT_ROW", function(assigTr, taskAssig) {

    var resEl = assigTr.find("[name=resourceId]");
    for (var i in taskAssig.task.master.resources) {
      var res = taskAssig.task.master.resources[i];
      var opt = $("<option>");
      opt.val(res.id).html(res.name);
      if (taskAssig.assig.resourceId == res.id)
        opt.attr("selected", "true");
      resEl.append(opt);
    }


    var roleEl = assigTr.find("[name=roleId]");
    for (var i in taskAssig.task.master.roles) {
      var role = taskAssig.task.master.roles[i];
      var optr = $("<option>");
      optr.val(role.id).html(role.name);
      if (taskAssig.assig.roleId == role.id)
        optr.attr("selected", "true");
      roleEl.append(optr);
    }

    if(taskAssig.task.master.canWrite && taskAssig.task.canWrite){
      assigTr.find(".delAssig").click(function() {
        var tr = $(this).closest("[assigId]").fadeOut(200, function() {
          $(this).remove();
        });
      });
    }


  });
</script>
</body>
</html>