<?php
/**
 *
 * Audio and Video Recorder on Posts. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021 axew3, http://axew3.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace w3all\w3audiovideotopost\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Audio and Video Recorder on Posts Event listener.
 */
class main_listener implements EventSubscriberInterface
{
  public static function getSubscribedEvents()
  {
    return array(
      'core.user_setup' => 'load_language_on_setup',
      'core.page_head' => 'overall_header_head_append',
      'core.page_footer' => 'overall_footer_body_after',
      'core.viewtopic_modify_post_data' => 'viewtopic_modify_post_data',

    );
  }

  protected $language;
  protected $config;
  protected $template;
  protected $request;
  protected $php_ext;

  /**
   * Constructor
   */
  public function __construct(\phpbb\language\language $language, \phpbb\config\config $config, \phpbb\template\template $template, \phpbb\request\request_interface $request, $php_ext)
  {
    $this->language = $language;
    $this->config   = $config;
    $this->template = $template;
    $this->request  = $request;
    $this->php_ext  = $php_ext;
  }

  /**
   * Load common language files during user setup
   *
   * @param \phpbb\event\data $event  Event object
   */
  public function load_language_on_setup($event)
  {
    $lang_set_ext = $event['lang_set_ext'];
    $lang_set_ext[] = array(
      'ext_name' => 'w3all/w3audiovideotopost',
      'lang_set' => 'common',
    );
    $event['lang_set_ext'] = $lang_set_ext;
  }

  public function viewtopic_modify_post_data($e)
  {

   // usernames/postID pairs
    foreach($e['rowset'] as $k => $v)
    {
      $pidUA[$v['post_id']] = $v['username'];
    } 

    // if on viewtopic
  if( strpos( $this->request->server('REQUEST_URI'), 'viewtopic.'.$this->php_ext) === false ){
      $viewtopic = '';
    } else 
     {
      $viewtopic = 1;
     }
  
   // extract all attachments for each post, and build a ready array of data to be used 
   // order attachments based on as they was, or as post_msg_id/attach_id
   // unset 'physical_filename' on resulting array
 
 // rebuild the same, but remove physical_filename
  if( !empty($e['attachments']) ){

    $posts_attachments_ary = $post_subary = array();
    $i=0;
    foreach( $e['attachments'] as $k => $v )
    {
     foreach( $v as $kk )
     {
      if( $kk['extension'] == 'mp3' && $kk['is_orphan'] == 0 )
      {
        unset($kk['physical_filename']);
        $post_subary[$i] = $kk;
        $i++;
       $posts_attachments_ary[$k] = $post_subary;
      }
     }
     $post_subary = array(); $i = 0;
    }

   // Due to above. If the post contain more than an mp3 (or any other attach) the phpBB array come with attachments ordered 
   // with the last inserted, on index 0, the previous on 1 etc. This cause that a post that contain more than one attachment
   // will have a time reversed order. So if the case, this sub array (array of attachments that belongs to this single post) is reversed here

   // Send the correct ordered array, also when a single post on topic contains more than one attachment
    foreach($posts_attachments_ary as $k => $a){
     if(count($a) > 1){ 
      $rev = array_reverse($a);
      $posts_attachments_ary[$k] = $rev;
     }
    }

  if(!empty($posts_attachments_ary)){
   $posts_attachments_ary = json_encode($posts_attachments_ary,  JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES);
   $posts_attachments_ary = base64_encode($posts_attachments_ary); 
  } else { 
    $posts_attachments_ary = ''; 
    }

   $pidUA = json_encode($pidUA,  JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES);
   $pidUA = base64_encode($pidUA);

    $this->template->assign_vars(array( 
     'W3ALL_AV_POST_ATTACHMENTS_ARY'  => $posts_attachments_ary,
     'W3AVR_MODEON_VIEWTOPIC' => $viewtopic,
     'W3AVR_USERS_APOST_OWN'  => $pidUA,
     'W3AVPHPBBEXT' => $this->php_ext,
    ));
   
  }
}


  public function overall_footer_body_after()
  {
    //echo $this->config['max_filesize'];exit;
    $this->template->assign_vars(array( 
     'W3ALLREQ_MODE'  => $this->request->variable('mode', ''),
     'W3ALLMAX_ATTACHDIM' => $this->config['max_filesize'],
    ));
  }

}
