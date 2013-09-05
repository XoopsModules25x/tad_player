<?php
function tad_player_search($queryarray, $andor, $limit, $offset, $userid){
	global $xoopsDB;
	//�B�z�\�\�\
	if(get_magic_quotes_gpc()){
		if(is_array($queryarray)){
			foreach($queryarray as $k=>$v){
				$arr[$k]=addslashes($v);
			}
			$queryarray=$arr;
		}else{
			$queryarray=array();
		}
	}
	$sql = "SELECT psn,title,post_date,uid FROM ".$xoopsDB->prefix("tad_player")." where 1";
	if ( $userid != 0 ) {
		$sql .= " AND uid=".$userid." ";
	}
	if ( is_array($queryarray) && $count = count($queryarray) ) {
		$sql .= " AND ((title LIKE '%$queryarray[0]%' OR creator LIKE '%$queryarray[0]%')";
		for($i=1;$i<$count;$i++){
			$sql .= " $andor ";
			$sql .= "( title LIKE '%$queryarray[$i]%' OR creator LIKE '%$queryarray[$i]%')";
		}
		$sql .= ") ";
	}
	$sql .= "ORDER BY post_date DESC";
	//die($sql);
	$result = $xoopsDB->query($sql,$limit,$offset);
	$ret = array();
	$i = 0;
 	while($myrow = $xoopsDB->fetchArray($result)){
		$ret[$i]['image'] = "images/video.png";
		$ret[$i]['link'] = "play.php?psn=".$myrow['psn'];
		$ret[$i]['title'] = $myrow['title'];
		$ret[$i]['time'] = tadplayer_tnsday2ts($myrow['post_date']);
		$ret[$i]['uid'] = $myrow['uid'];
		$i++;
	}
	return $ret;
}

//�ഫ���ɶ��W�O
function tadplayer_tnsday2ts($day=""){
    $dd=explode(" ",$day);
    $d=explode("-",$dd[0]);
    $t=explode(":",$dd[1]);
    $ts=mktime($t[0],$t[1],$t[2],$d['1'],$d['2'],$d['0']);
    return $ts;
}
?>
