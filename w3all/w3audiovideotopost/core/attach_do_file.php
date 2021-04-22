<?php
/**
*
* w3all Audio Video to Post. An extension for the phpBB Forum Software package.
*
* @copyright (c) 2021, axew3, http://www.axew3.com
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* @ignore
*/

define('IN_PHPBB', true);

///////////////////
//
// Define the root from here
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

//
///////////////////

// Thank you sun.
if (isset($_SERVER['CONTENT_TYPE']))
{
	if ($_SERVER['CONTENT_TYPE'] === 'application/x-java-archive')
	{
		exit;
	}
}
else if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Java') !== false)
{
	exit;
}

 include($phpbb_root_path . 'common.' . $phpEx);
 //require($phpbb_root_path . 'includes/functions_download' . '.' . $phpEx);

 // Start session management, do not update session page.
 $user->session_begin(false);
 $auth->acl($user->data);
 $user->setup('posting');

 $phpbb_content_visibility = $phpbb_container->get('content.visibility');
 
 $request	= $phpbb_container->get('request');
 $ajaxAdata = $request->variable('data', '');
 $ajaxAdatadel = $request->variable('data_attach_del', '');

  if( empty($ajaxAdata) && empty($ajaxAdatadel) ){
 		 echo'W3ERRORAV: NO AJAX DATA';
	 exit;
  }

  if( !empty($ajaxAdata) ){

   $ajaxAdata = explode(',',$ajaxAdata);
   $ajaxAdata = str_replace(chr(0), '', $ajaxAdata);
   
   if(isset($ajaxAdata[1])){ 
    $audioData = str_replace(chr(0), '', $ajaxAdata[1]);
   }
  
   if(empty($ajaxAdata[1])){
 	  echo'W3ERRORAV: NO AV DATA';
	  exit;  	
   }
   
   if( isset($ajaxAdata[2]) && !empty($ajaxAdata[2]) && strlen($ajaxAdata[2]) > 2 )
   {
   	 $ajaxAdata[2] = trim(str_replace(chr(0), '', $ajaxAdata[2]));
   	 
    if( preg_match('/[^-0-9A-Za-z]/',$ajaxAdata[2]) OR empty($ajaxAdata[2]) ){
    	$real_filename = $user->data['user_id'] . '_' . bin2hex(random_bytes(4)) . '.mp3';
    } else {
    	  $real_filename = $ajaxAdata[2] . '.mp3';
      }
      
    if( strlen($real_filename) > 255 ){
  	 $real_filename = $user->data['user_id'] . '_' . bin2hex(random_bytes(4)) . '.mp3';
  	  exit;
    }  
   } else {
   	        $real_filename = $user->data['user_id'] . '_' . bin2hex(random_bytes(4)) . '.mp3';
   	      } 
   
   unset($ajaxAdata);
  }
 
 if(!empty($ajaxAdatadel)){
 	 $ajaxAdatadel = str_replace(chr(0), '', $ajaxAdatadel);
   $ajaxAdatadel = explode(',',$ajaxAdatadel);

   // fid, w3usession, w3formtoken, w3formtokentime, w3forumid
   $fid = intval($ajaxAdatadel[0]);
   $w3usession = $ajaxAdatadel[1];
   $w3formtoken = $ajaxAdatadel[2];
   $w3formtokentime = $ajaxAdatadel[3];
   $w3forumid = $ajaxAdatadel[4];
   unset($ajaxAdatadel);
   
    if( preg_match('/[^0-9A-Za-z]/',$w3usession) OR preg_match('/[^0-9A-Za-z]/',$w3formtoken) OR preg_match('/[^0-9]/',$w3formtokentime) OR preg_match('/[^0-9]/',$w3forumid) ){
  	 echo 'FORBIDDEN: request contains unexpected chars';
  	 exit;
    }

   if ( $fid < 1 ){
  	echo 'ERROR: file ID too small';
  	exit;
   }
    
   // If the user session mismatch
  if ( $user->data['session_id'] != $w3usession ){
  	echo 'FORBIDDEN: session mismatch';
  	exit;
  }
 
  // function add_form_key(  on /includes/functions.php // create and check against passed values: should match
	$token_sid_ck = ($user->data['user_id'] == ANONYMOUS && !empty($config['form_token_sid_guests'])) ? $user->data['session_id'] : '';
	// using $user->data['session_time'] the token will fail Reloading the page, the $time param on add_form_key() is not the one of the db stored session
	// $token_ck = sha1($user->data['session_time'] . $user->data['user_form_salt'] . 'posting' . $token_sid_ck);
	$token_ck = sha1($w3formtokentime . $user->data['user_form_salt'] . 'posting' . $token_sid_ck);

 // If the token mismatch
  if( $w3formtoken !== $token_ck ){
  	echo 'FORBIDDEN: form token mismatch';
  	exit;
  }
  
// function check_form_key( on /includes/functions.php
// "we enforce a minimum value of half a minute here."
	$form_token_lifetime = ($config['form_token_lifetime'] < 30) ? 30 : $config['form_token_lifetime'];
// If the token time expired	
  if(  time() - $form_token_lifetime > $w3formtokentime  ){
  	echo 'FORBIDDEN: form token time expired';
  	exit;
  }

// If attachment is not allowed	
  if (!$config['allow_attachments'] && !$config['allow_pm_attach'])
  {
	  echo 'FORBIDDEN: ATTACHMENT FUNCTIONALITY DISABLED';
  	exit;
  }

//$allowed = ($auth->acl_get('f_attach', $forum_id) && $auth->acl_get('u_attach') && $config['allow_attachments'] && $form_enctype);

 // If attachment allowed for this user or/on this forum
  if ( $auth->acl_get('u_attach') < 1 OR $auth->acl_get('f_attach', $w3forumid) < 1 )
  {
	  echo 'FORBIDDEN: user with no attachment capabilities or no forum permissions';
  	exit;
  }
  
 // If there are permissions over this file
  $own_attachment = ($auth->acl_get('a_attach') || $attachment['poster_id'] == $user->data['user_id']) ? true : false;
	if (!$own_attachment) {
		 echo 'W3ERRORAV: you have no permissions over this file';
  	 exit;
		}
  
  
 $sql = 'SELECT * 
	FROM ' . ATTACHMENTS_TABLE . "
	WHERE attach_id = $fid";
 $result = $db->sql_query($sql);
 $attachment = $db->sql_fetchrow($result);
 $db->sql_freeresult($result);

  if (!$attachment)
  {
  	echo 'W3ERRORAV: there is no attachment with this ID';
  	exit;
  }
  
  
   $sql = 'DELETE FROM ' . ATTACHMENTS_TABLE . "
					WHERE attach_id = $fid";
					$db->sql_query($sql);
  
   echo 'FILEDELETED: ID ' . $fid; 
   exit;
 
 } // END // if(!empty($ajaxAdatadel)){

 
 $physical_filename_rand = $user->data['user_id'] . '_s1_' . substr(md5(bin2hex(random_bytes(16))), 2) . substr(time(), 2);
 $audioDataIns = base64_decode($audioData);

  if( strlen($audioDataIns) > $config['max_filesize'] ){
   echo'W3ERRORAV: FILE DATA IS TOO BIG';
	 exit;
  }
 
 $phpBBfilesFAN = $phpbb_root_path . $config['upload_path']. '/' . $physical_filename_rand;

 if(! file_put_contents($phpBBfilesFAN, $audioDataIns) ){
	 echo'W3ERRORAV: CANNOT SAVE FILE';
	 exit;
  } else {
  	
  // Add the new attach into db
 
  $filesize = @filesize($phpBBfilesFAN);
   if(!$filesize){
  	 echo'W3ERRORAV: FILESIZE';
  	 @unlink($phpBBfilesFAN);
  	 exit;
	 }

  $sql_arr = array(
    'attach_id'    => '0',
    'post_msg_id'  => 0,
    'topic_id'     => 0,
    'in_message'   => 0,
    'poster_id'    => $user->data['user_id'],
    'is_orphan'    => 1,
    'physical_filename' => $physical_filename_rand,
    'real_filename' => $real_filename,
    'download_count' => 0,
    'attach_comment' => '',
    'extension'    => 'mp3',
    'mimetype'     => 'audio/x-mpeg-3',
    'filesize'     => $filesize,
    'filetime'     => time(),
    'thumbnail'    => 0
  );

  $sql = 'INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_arr);
  $db->sql_query($sql);
  $attachID = (int) $db->sql_nextid();
 
   if($attachID > 0){
    echo "OK-AV-PROCESSED//#//".$attachID.'//#//'.$sql_arr['post_msg_id'].'//#//'.$sql_arr['topic_id'].'//#//'.$sql_arr['in_message'].'//#//'.$sql_arr['poster_id'].'//#//'.$sql_arr['is_orphan'].'//#//'.$sql_arr['physical_filename'].'//#//'.$sql_arr['real_filename'].'//#//'.$sql_arr['download_count'].'//#//'.$sql_arr['attach_comment'].'//#//'.$sql_arr['extension'].'//#//'.$sql_arr['mimetype'].'//#//'.$sql_arr['filesize'].'//#//'.$sql_arr['filetime'].'//#//'.$sql_arr['thumbnail'];
   } else {
    	echo "W3ERRORAV: AV NOT PROCESSED";
    }
  
 }
  
echo'AVRDONE';//'SPEECH DO CALL';
exit;
