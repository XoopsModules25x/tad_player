<?php
/*-----------引入檔案區--------------*/
include "header.php";
$xoopsOption['template_main'] = "tad_player_index.html";
include_once XOOPS_ROOT_PATH."/header.php";
include_once XOOPS_ROOT_PATH."/modules/tadtools/star_rating.php";
/*-----------function區--------------*/


//列出所有tad_player資料
function list_tad_player($pcsn=""){
	global $xoopsDB,$xoopsModule,$xoopsModuleConfig,$xoopsUser,$xoopsTpl;



	//先找出底下分類
	$sub_cate=list_tad_player_cate($pcsn);
//die(var_export($sub_cate));
  $count=empty($sub_cate)?0:count($sub_cate);
//die('count:'.$count);
	//取得所有分類名稱
	$cate=get_tad_player_cate_all();

	//進行排序
	//$order_by_sort=(empty($pcsn))?"":"a.sort ,";
	$order_by_sort="a.sort ,";

	$sql = "select a.psn,a.pcsn,a.location,a.title,a.image,a.info,a.creator,a.post_date,a.counter,a.enable_group,b.title,b.of_csn from ".$xoopsDB->prefix("tad_player")." as a left join ".$xoopsDB->prefix("tad_player_cate")." as b on a.pcsn=b.pcsn where a.pcsn='{$pcsn}' order by $order_by_sort a.post_date desc";


	//getPageBar($原sql語法, 每頁顯示幾筆資料, 最多顯示幾個頁數選項);
  $PageBar=getPageBar($sql,$xoopsModuleConfig['index_show_num'],10);
  $bar=$PageBar['bar'];
  $sql=$PageBar['sql'];

	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

	//檢查權限
  $ok_cat=chk_cate_power();

  //目前使用者所屬群組
  $user_group=array();
  if($xoopsUser){
    $user_group=$xoopsUser->getGroups();
	}

  $rating_js="";
  if($xoopsModuleConfig['use_star_rating']){
    $rating=new rating("tad_player","10",'show','simple');
  }

	$data=$no_power='';
	$i=0;
	while(list($psn,$new_pcsn,$location,$title,$image,$info,$creator,$post_date,$counter,$enable_group,$cate_title,$of_csn)=$xoopsDB->fetchRow($result)){

	  if(!empty($new_pcsn) and !in_array($new_pcsn,$ok_cat)){
	      $no_power[]=$psn;
			  //continue;
		}

	  //查看該分類是否允許目前使用者觀看
		$enable_group_arr=explode(",",$enable_group);
		$same=array_intersect($enable_group_arr,$user_group);
		if(!empty($enable_group) and empty($same)){
		    continue;
		}



		//整理影片圖檔
		if(substr($image,0,4)=='http'){
      $pic=$image;
		}elseif(empty($image) or !file_exists(_TAD_PLAYER_IMG_DIR."{$psn}.png")){
			$ext=substr($location,-3);
	    if($ext=="mp3"){
	      $pic="mp3.png";
			}else{
	      $pic="flv.png";
			}
			$pic="images/$pic";
		}else{
      $pic=_TAD_PLAYER_IMG_URL."{$psn}.png";
		}


    //無權限者，無連結
    $url=(is_array($no_power) and in_array($psn,$no_power))?"":"play.php?psn={$psn}";


    //無權限者，無標題
    $img_title=(is_array($no_power) and in_array($psn,$no_power))?sprintf(_MD_TADPLAYER_NO_POWER,$title):$title;

    //整理日期
    if(substr($post_date,0,2)=='20')$post_date=strtotime($post_date);
    $post_date=date("Y-m-d H:i:s",xoops_getUserTimestamp($post_date));
    $creator_col=(empty($creator))?"":_MD_TADPLAYER_CREATOR.": $creator";
    if($xoopsModuleConfig['use_star_rating']){
      $rating->add_rating("psn",$psn);
    }

    $data[$i]['pic']=$pic;
    $data[$i]['url']=$url;
    $data[$i]['post_date']=$post_date;
    //$data[$i]['counter']=sprintf(_MD_TADPLAYER_INDEX_COUNTER,$counter);
    $data[$i]['counter']=$counter;
    $data[$i]['info']=$info;
    $data[$i]['psn']=$psn;
    $data[$i]['img_title']=$img_title;
    $data[$i]['creator_col']=$creator_col;
    $i++;
	}

  $count+=$i;

	if($xoopsModuleConfig['use_star_rating']){
	 $rating_js=$rating->render();
  }


  if(!empty($pcsn)){
    $xoops_module_header="
    <meta proprery=\"og:title\" content=\"{$cate[$pcsn]}\" />
    <meta proprery=\"og:description\" content=\"{$info}\" />
    <meta property=\"og:image\" content=\"{$pic}\" />
    <meta property=\"og:video\" content=\"".XOOPS_URL."/modules/tad_player/index.php?pcsn=$pcsn\"/>
    ";
  }else{
    $xoops_module_header="";
  }

  $xoopsTpl->assign( "xoops_module_header" , $xoops_module_header);
  $xoopsTpl->assign( "content" , $data) ;
  $xoopsTpl->assign( "sub_cate" , $sub_cate) ;
  $xoopsTpl->assign( "bar" , $bar) ;
  $xoopsTpl->assign( "rating_js" , $rating_js) ;
  $xoopsTpl->assign( "mode" , "normal") ;
  $xoopsTpl->assign( "count" , $count) ;
}


//底下分類數
function count_cate_num($pcsn="0"){
	global $xoopsDB,$xoopsModule;
	$sql = "select count(*) from ".$xoopsDB->prefix("tad_player_cate")." where of_csn='{$pcsn}'";
	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
	list($count)=$xoopsDB->fetchRow($result);
	if(empty($count))$count=0;
	return $count;
}

//列出分類
function list_tad_player_cate($pcsn='0'){
	global $xoopsDB,$xoopsModule,$xoopsUser,$xoopsConfig;

  //目前使用者所屬群組
  $user_group=array();
  if($xoopsUser){
    $user_group=$xoopsUser->getGroups();
	}

	$sql = "select * from ".$xoopsDB->prefix("tad_player_cate")." where of_csn='{$pcsn}' order by sort";
	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

  $data="";
  $i=0;
	while(list($pcsn,$of_csn,$title,$enable_group,$sort,$width,$height)=$xoopsDB->fetchRow($result)){
	  //查看該分類是否允許目前使用者觀看
		$enable_group_arr=explode(",",$enable_group);
		$same=array_intersect($enable_group_arr,$user_group);
		if(!empty($enable_group) and empty($same)){
		    continue;
		}

    //底下影片數
  	$video=count_video_num($pcsn);
  	$counter=$video['num'];


		$pcsn_num=count_cate_num($pcsn);

		$num=empty($counter)?"0":$counter;

    $data[$i]['pcsn']=$pcsn;
    $data[$i]['pic']=empty($video['img'])?"images/empty_cate_{$xoopsConfig['language']}.png":$video['img'];
    $data[$i]['title']=$title;
    $data[$i]['num']=sprintf(_MD_TADPLAYER_CATE_VIDEO_NUM,$num);
    $data[$i]['pcsn_num']=sprintf(_MD_TADPLAYER_CATE_NUM,$pcsn_num);
    $i++;
	}

	return $data;

}

//清單播放
function playlist($pcsn="0"){
	global $xoopsModuleConfig , $xoopsUser , $xoopsTpl;
	if(empty($pcsn))$pcsn=0;
  $cate=get_tad_player_cate($pcsn);
  $ok_cat=chk_cate_power();

  $user_group=array();
  if($xoopsUser){
    $user_group=$xoopsUser->getGroups();
	}
  if(!empty($pcsn) and !in_array($pcsn,$ok_cat)){
		redirect_header("index.php",3,sprintf(_MD_TADPLAYER_NO_POWER,$cate['title']));
	}


	$playcode=play_code_jwplayer("list{$pcsn}",$cate,$pcsn,"playlist",false,null,null,$xoopsModuleConfig['display_max'],$xoopsModuleConfig['display']);


  $title=(empty($cate[$pcsn]))?"":$cate[$pcsn];


  $xoopsTpl->assign( "mode" , "list") ;
  $xoopsTpl->assign( "title" , $title) ;
  $xoopsTpl->assign( "playcode" , $playcode) ;

}


/*-----------執行動作判斷區----------*/
$op=(empty($_REQUEST['op']))?"":$_REQUEST['op'];
$psn=(empty($_REQUEST['psn']))?"":intval($_REQUEST['psn']);
$pcsn=(empty($_REQUEST['pcsn']))?"":intval($_REQUEST['pcsn']);
$xoops_module_header="";

$xoopsTpl->assign( "toolbar" , toolbar_bootstrap($interface_menu)) ;
$xoopsTpl->assign( "bootstrap" , get_bootstrap()) ;
$xoopsTpl->assign( "jquery" , get_jquery(true)) ;


switch($op){

	case "playlist":
	playlist($pcsn);
	break;

	//預設動作
	default:
	list_tad_player($pcsn);
	break;
}

/*-----------秀出結果區--------------*/
$arr=get_pcsn_path($pcsn);
if(!file_exists(XOOPS_ROOT_PATH."/modules/tadtools/jBreadCrumb.php")){
  redirect_header("index.php",3, _MD_NEED_TADTOOLS);
}
include_once XOOPS_ROOT_PATH."/modules/tadtools/jBreadCrumb.php";
$jBreadCrumb=new jBreadCrumb($arr);
$path=$jBreadCrumb->render();

$xoopsTpl->assign( "select" , cate_select($pcsn,1)) ;
$xoopsTpl->assign( "pcsn" , $pcsn) ;
$xoopsTpl->assign( "path_bar" , $path);
$xoopsTpl->assign( "push" , push_url($xoopsModuleConfig['use_social_tools']));

if(isset($title) and !empty($title)){
  $xoopsTpl->assign( "xoops_pagetitle",$title);
  if (is_object($xoTheme)) {
      $xoTheme->addMeta( 'meta', 'keywords', $title);
      $xoTheme->addMeta( 'meta', 'description', $info) ;
  } else {
      $xoopsTpl->assign('xoops_meta_keywords','keywords',$title);
      $xoopsTpl->assign('xoops_meta_description', $info);
  }
}

include_once XOOPS_ROOT_PATH.'/footer.php';
