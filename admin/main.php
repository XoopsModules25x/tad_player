<?php
//  ------------------------------------------------------------------------ //
// ���Ҳե� tad �s�@
// �s�@����G2008-02-28
// $Id: index.php,v 1.2 2008/05/14 01:22:58 tad Exp $
// ------------------------------------------------------------------------- //

/*-----------�ޤJ�ɮװ�--------------*/
$xoopsOption['template_main'] = "tp_adm_main.html";
include_once "header.php";
include_once "../function.php";


/*-----------function��--------------*/
//�C�X�Ҧ�tad_player���
function list_tad_player($pcsn=""){
	global $xoopsDB,$xoopsModule,$xoopsModuleConfig,$xoopsTpl;
	
	if(!file_exists(XOOPS_ROOT_PATH."/modules/tadtools/jeditable.php")){
   redirect_header("index.php",3, _MA_NEED_TADTOOLS);
  }
  include_once XOOPS_ROOT_PATH."/modules/tadtools/jeditable.php";
	$cate_select=cate_select($pcsn);
  $xoopsTpl->assign('cate_select',$cate_select);
  
	$cate=get_tad_player_cate($pcsn);
	$xoopsTpl->assign('link_to_cate',sprintf(_MA_TADPLAYER_LINK_TO_CATE,$cate["title"]));

  $where_pcsn=!empty($pcsn)?"where pcsn='{$pcsn}' order by sort":"order by pcsn,sort";
	
	$sql = "select `psn` , `title` , `location` , `image` , `info` , `width` , `height` , `counter` , `enable_group` , `uid` , `post_date` from ".$xoopsDB->prefix("tad_player")." {$where_pcsn} ";
	
	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());


  $i=0;

  $save_file=XOOPS_URL."/modules/tad_player/admin/save.php";
  $data="";
	while($all=$xoopsDB->fetchArray($result)){
    foreach($all as $k=>$v){
      $$k=$v;
    }

    $g_txt=txt_to_group_name($enable_group,_MA_TADPLAYER_ALL_OK, ', ');
    
		if(substr($image,0,4)=='http'){
      $pic=$image;
		}elseif(empty($image) or !file_exists(_TAD_PLAYER_IMG_DIR."{$psn}.png")){
			$ext=substr($location,-3);
	    if($ext=="mp3"){
	      $pic="mp3.png";
			}else{
	      $pic="flv.png";
			}
			$pic="../images/$pic";
		}else{
      $pic=_TAD_PLAYER_IMG_URL."{$psn}.png";
		}

    $uid_name=XoopsUser::getUnameFromId($uid,1);
    $uid_name=(empty($uid_name))?XoopsUser::getUnameFromId($uid,0):$uid_name;
    
    $post_date=substr($post_date,0,10);

    $data[$i]['psn']=$psn;
    $data[$i]['pic']=$pic;
    $data[$i]['title']=$title;
    $data[$i]['pcsn']=$pcsn;
    $data[$i]['uid_name']=$uid_name;
    $data[$i]['counter']=$counter;
    $data[$i]['width']=$height;
    $data[$i]['post_date']=$post_date;
    $data[$i]['g_txt']=$g_txt;
    $data[$i]['info']=$info;

    $i++;

	}

  
  $option=get_tad_player_cate_option(0,0,$pcsn,1,false);

  $xoopsTpl->assign('option',$option);
  $xoopsTpl->assign('pcsn',$pcsn);
  $xoopsTpl->assign('data',$data);
  $xoopsTpl->assign('cate_width',$cate["width"]);
  $xoopsTpl->assign('cate_height',$cate["height"]);
  $xoopsTpl->assign('jquery',get_jquery(true));

  
}



//���s���ͩҦ���XML
function mk_all_xml($the_pcsn=""){
	global $xoopsDB;
	$sql = "select pcsn,title from ".$xoopsDB->prefix("tad_player_cate");
	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

  $log="";
	while(list($pcsn,$title)=$xoopsDB->fetchRow($result)){
		mk_list_xml($pcsn);
		$log.=sprintf(_MA_TADPLAYER_XML_OK,$title)."<br>";
	}
	$and_pcsn=(empty($the_pcsn))?"":"?pcsn=$the_pcsn";
	redirect_header($_SERVER['PHP_SELF'].$and_pcsn,3, $log);
	return;
}

//�x�s�Ƨ�
function save_sort(){
	global $xoopsDB;
	foreach($_POST['sort'] as $psn => $sort){
		$sql = "update  ".$xoopsDB->prefix("tad_player")." set sort='{$sort}' where psn='{$psn}'";
		$xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
	}
	return;

}


//�妸�R��
function batch_del(){
  foreach($_POST['video'] as $psn){
    delete_tad_player($psn);
  }
}

//�妸�h��
function batch_move($new_pcsn=""){
  global $xoopsDB;
  $videos=implode(",",$_POST['video']);
  $sql = "update ".$xoopsDB->prefix("tad_player")." set `pcsn` = '{$new_pcsn}' where psn in($videos)";
  $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error()."<br>$sql");
  return $sn;
}


//�妸�s�W���D
function batch_add_title(){
  global $xoopsDB;
  $videos=implode(",",$_POST['video']);
  $sql = "update ".$xoopsDB->prefix("tad_player")." set  `title` = '{$_POST['add_title']}' where psn in($videos)";
  $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
}


//�妸�s�W����
function batch_add_info(){
  global $xoopsDB;
  $videos=implode(",",$_POST['video']);
  $sql = "update ".$xoopsDB->prefix("tad_player")." set `info` = '{$_POST['add_info']}' where psn in($videos)";
  $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error()."<br>$sql");
  return $sn;
}

//�妸��s�e�P��
function update_wh(){
  global $xoopsDB;
  $videos=implode(",",$_POST['video']);
  $sql = "update ".$xoopsDB->prefix("tad_player")." set `width` = '{$_POST['width']}' , `height` = '{$_POST['height']}' where psn in($videos)";
  $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error()."<br>$sql");
  return $sn;
}
/*-----------����ʧ@�P�_��----------*/
$op = (!isset($_REQUEST['op']))? "":$_REQUEST['op'];
$psn=(empty($_REQUEST['psn']))?"":intval($_REQUEST['psn']);
$pcsn=(empty($_REQUEST['pcsn']))?"":intval($_REQUEST['pcsn']);
$new_pcsn=(empty($_REQUEST['new_pcsn']))?"":intval($_REQUEST['new_pcsn']);


switch($op){

	//�x�s�Ƨ�
	case "save_sort":
	save_sort();
	header("location: {$_SERVER['PHP_SELF']}?pcsn=$pcsn");
	break;

	//���s���ͩҦ���XML
	case "mk_all_xml":
	$main=mk_all_xml();
	break;


  case "del":
  batch_del();
	header("location: {$_SERVER['PHP_SELF']}?pcsn=$new_pcsn");
  break;
  

  case "move":
  batch_move($new_pcsn);
  mk_list_xml($pcsn);
  mk_list_xml($new_pcsn);
	header("location: {$_SERVER['PHP_SELF']}?pcsn=$new_pcsn");
  break;

  case "add_title":
  batch_add_title();
  mk_list_xml($pcsn);
  header("location: {$_SERVER['PHP_SELF']}?pcsn={$pcsn}");
  break;


  case "add_info":
  batch_add_info();
  mk_list_xml($pcsn);
  header("location: {$_SERVER['PHP_SELF']}?pcsn={$pcsn}");
  break;
  
  case "update_wh":
  update_wh();
  mk_list_xml($pcsn);
  header("location: {$_SERVER['PHP_SELF']}?pcsn={$pcsn}");
  break;

  
	//�w�]�ʧ@
	default:
	list_tad_player($pcsn);
	break;

}

/*-----------�q�X���G��--------------*/
include_once 'footer.php';
?>