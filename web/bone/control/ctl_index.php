<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * index控制器
 *
 * @version $id
 */
class ctl_index
{
   
   /**
    * 构造函数
    * @return void
    */
    public function __construct()
    {
        $this->fields = array(
            'doc_title' => config::get('site_name'),
            'doc_keyword' => config::get('site_keyword'),
            'doc_description' => config::get('site_description'),
            'doc_tj' => config::get('site_tj'),
            'crumbs' => '',
        );
        cls_msgbox::$tpl = 'cls_msgbox.member.tpl';
    }
   
   /**
    * 主页
    */
    public function index()
    {
        if( file_exists(dirname(__FILE__).'/ctl_doc.php') )
        {
            include dirname(__FILE__).'/ctl_doc.php';
            $doc = new ctl_doc();
            $doc->index();
        } else {
            tpl::display('index.tpl');
            exit();
        }
    }
    
    //测试
    public function test()
    {
        tpl::display('index.tpl');
        exit();
    }
    
   /**
    * 提取框架
    */
    public function downframe()
    {
        if( req::$request_mdthod=='POST' )
        {
            $items = req::item('items');
            mod_make_project::make( $items );
            exit();
        }
        tpl::assign('fields', $this->fields);
        tpl::display('index.downframe.tpl');
        exit();
    }
    
}
