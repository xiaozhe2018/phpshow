<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * Lurd操作类，全自动操作的处理程序
 *
 * 可能通过 add、edit、list、delete 方法直接完成整个表相关的所有操作(模板也自动生成)
 *
 * @version $Id$
 *
 */
 class cls_lurd_control extends cls_lurd
 {

    public $appname = '';          //应用名称，没指定用表名

    public $template_path = '';     //生成的模板保存目录，由于模板类限制了目录为PATH_ROOT.'/templates/template/'
                                    //因此这里指定的是居于这个目录的子目录，不改变可以保留空，而不是绝对路径

    public $always_make = false;    //true--总是生成模板(通常在调试时才会使用)，false--没发现模板的情况才会生成。

    public $form_url = '?ct=lurd';  //默认控制器网址(在控制器的index方法里调用lurd类)

    public $list_config = array('listfield'=>'*', 'orderquery'=>'', 'wherequery' =>'', 'searchfield'=>'*');  //列出数据时指定的特殊条件
    
    public $search_conditions = array(); //规则化搜索条件

    public $datas = array();   //列表或编辑记录时的当前数据

    private $assign_vars = array();  //向模板传递特定值

    //手工指定模板文件名
    public $tpl_files = array('list'=>'', 'add'=>'', 'edit'=>'');
    
    //浏览附件的对话框路径
    public $dlg_path = '../share/fck/dialog';
    
    //是否使用排序按钮（表中必须有sortrank字段并有含有主键此项才会出现）
    private static $has_sort = false;
    
    //---------------------------------------
    //绑定hooks操作(使用bind方法)
    //--------------------------------------
    private  $is_bind      = false;
    private  $bindObj     = null;
    private  $bindHooks = array();
    
    //---------------------------------------------
    //非绑定事件操作的选项(使用listen方法)
    //--------------------------------------------
    //完成操作后自行操作还是lurd类由自行控制
    public $end_need_done = false;
    
    //完成处理后返回的网址
    public $end_return_url = '';
    
    //相关输出的模板
    private $display_tpl = '';
    
    //指定允许的事件(不指定表示全部，不管listen还是bind_hooks都会受此影响)
    public $allow_evens = array();

   /**
    * 把表名映射成类的工厂方法
    * @parem $tablename
    */
    public static function factory($tablename, $form_url='?ct=lurd', $template_path='', $always_make=false)
    {
        $cls = new cls_lurd_control();
        $tablename = str_replace('#PB#', $GLOBALS['config']['db_prefix'], $tablename);
        $cls->set_table( $tablename );
        $cls->template_path = $template_path;
        $cls->form_url = $form_url;
        $cls->always_make = $always_make;
        //高级字段设置分析(在commect里定义的@...@属性)
        $cls->_check_lurd_type( $cls->fields );
        return $cls;
    }

   /**
    * 设置排序条件
    * @parem $orderquery    排序查询条件，如： order by id desc
    * @return void          
    */
    public function set_order_query( $orderquery )
    {
        $this->list_config['orderquery'] = $orderquery;
    }
    
   /**
    * 设置要读取的字段
    * @parem $listfield list方法要列出的字段
    * @return void          
    */
    public function set_list_field( $listfield )
    {
        $this->list_config['listfield'] = $listfield;
    }
    
   /**
    * 设置要搜索的字段
    * 当前台请求含有 keyword， 并且没有指定搜索规则这个才会生效
    * 如果前台含有 keyword，所有搜索规则都不指定，程序强制全部字符串类型作为搜索条件
    * @parem $search_field 只支持字符串类型，用逗号分隔多个
    * @return void          
    */
    public function set_search_field( $search_field )
    {
        $this->list_config['searchfield'] = $search_field;
    }
    
   /**
    * 设置允许的事件
    * @parem $evens   事件 ( 默认 array('add', 'saveadd', 'edit', 'saveedit', 'delete', 'list') )
    */
    public function lock_evens( $evens )
    {
        $this->allow_evens = $evens;
    }
    
   /**
    * 追加手动查询条件
    * @parem $wherequery    手动指定查询条件
    * @parem $link          用 AND 还是 OR 连结
    * @return void
    */
    function add_search_condition($wherequery, $link='AND')
    {
        $this->search_conditions[] = array($link, $wherequery);
    }

   /**
    * 手工指定生成的模板名
    */
    public function set_tplfiles($list='', $add='', $edit='')
    {
        $this->tpl_files['list'] = $list;
        $this->tpl_files['add']  = $add;
        $this->tpl_files['edit'] = $edit;
    }
    
   /**
    * 分析搜索条件
    * @param $keyword  req请求的keyword
    * @paran $url      分页网址
    * @return string
    */
    protected function _analyse_search($keyword, &$url)
    {
        if( empty($this->search_conditions) )
        {
            if( empty($keyword) ) {
                return false;
            }
            if( $this->list_config['searchfield'] == '' ) {
                foreach( $this->fields as $field => $infos ) {
                    if(preg_match('/char/i', $infos['type']) ) {
                        $this->list_config['searchfield'] .= ($this->list_config['searchfield'] == '' ? $field : ','.$field);
                    }
                }
            }
            if( $this->list_config['searchfield'] == '' ) {
                return false;
            }
            $fs = explode(',', $this->list_config['searchfield']);
            foreach($fs as $f) {
                $this->search_conditions[] = array('OR', " LOCATE('{$keyword}', `{$f}`)  > 0 ");
            }
        }
        $wherequery = '';
        foreach( $this->search_conditions as $conditions ) {
            $wherequery .= $wherequery=='' ? "({$conditions[1]})" : " {$conditions[0]} ({$conditions[1]})";
        }
        if( $wherequery != '' ) {
            $this->list_config['wherequery'] = ' where '.$wherequery;
        }
        if( !empty($keyword) && !preg_match('/&keyword=/', $url) ) {
            $url .= "&keyword=".urlencode($keyword);
        }
        return true;
    }

   /**
    * 事件监听器——even对应的自动操作方法（add, saveadd, edit, saveedit, delete, list）
    * @parem array() $request_array  与字段相关的键值表
    * @parem  $even 
    * @return void
    */
    public function listen( &$request_array )
    {
        $even = isset($request_array['even']) ? $request_array['even'] : 'list';
        //检查是否允许执行某事件
        if( !empty($this->allow_evens) && !in_array($even, $this->allow_evens) )
        {
            cls_msgbox::show('系统提示', '这个操作不被允许执行！', -1, 3000);
            exit();
        }
        switch($even)
        {
            case 'add':
                $this->e_add($request_array);
                break;
            case 'saveadd':
                $this->e_saveadd($request_array);
                break;
            case 'edit':
                $this->e_edit($request_array);
                break;
            case 'saveedit':
                $this->e_saveedit($request_array);
                break;
            case 'delete':
                $this->e_delete($request_array);
                break;
            default:
                $this->e_list($request_array);
                break;
        }
    }
    
   /**
    * 事件绑定——把操作绑定到特定方法
    * @parem  $refObj  操作本类的类
    * @parem  $bind_hooks 事件绑定
    *         事件绑定针对方法： add, saveadd, edit, saveedit, delete
                                 add_start, add_end , saveadd_start, saveadd_end  增加或保存增加的数据操作的前后
    *                            edit_start, edit_end , saveedit_start, saveedit_end  增加或保存修改数据操作的前后
    *                            delete_end 删除操作的结束
    *         (并无针对list的事件绑定情况，因为通常控制器起始操作就是list，本身可以在操作它的前后进行一些处理)
    * @return void
    */
    public function bind_hooks(&$refObj, $bind_hooks)
    {
        $this->is_bind   = true;
        $this->bindObj   = &$refObj;
        $this->bindHooks = $bind_hooks;
    }

   /**
    * 向模板传递特定的值(仅保存值)
    */
    public function assign($key, $value)
    {
        $this->assign_vars[$key] = $value;
    }

   /**
    * 向模板传递特定的值（实际处理）
    */
    private function _assign_all()
    {
        foreach($this->assign_vars as $k=>$v)
        {
            tpl::assign($k, $v);
        }
    }

   /**
    * 显示模板
    */
    public function display( )
    {
        $this->assign_vars['lurd'] = $this;
        $this->_assign_all();
        //清空assign数组
        $this->assign_vars = array();
        tpl::display( $this->display_tpl );
    }

   /**
    * 检测模板是否存在
    */
    public function check_template( $even )
    {
        $tpl = tpl::init();
        $tpldir = $tpl->template_dir.( empty(bone::$instance->app_name) ? '' : bone::$instance->app_name.'/' );
        if( !is_dir($tpldir) ) {
            mkdir($tpldir, 0777);
        }
        $tplfile = empty($this->tpl_files[$even]) ? $tpldir.'lurd.'.$this->table_name.'.'.$even.'.tpl' : $tpldir.$this->tpl_files[$even];
        return array(file_exists($tplfile), $tplfile);
    }
    
    /**
       * 强制指定字模板（列表list和编辑edit、增加add模板里用）
       * @return void
       */
	public function bind_template($fieldname, $tmptype='list', $temp='')
	{
		if( isset($this->fields[$fieldname]) )
		{
			$this->fields[$fieldname][$tmptype.'template'] = $temp;
		}
	}

    /**
    * 获取lurd模板的模板
    */
    public function get_template( $even )
    {
        $tpl = tpl::init();
        $tpldir = $tpl->template_dir.'system/';
        $tplfile = $tpldir.'lurd.'.$even.'.tpl';
        return (file_exists($tplfile) ? file_get_contents($tplfile) : '');
    }

   /**
    * 获取当前页的数据
    */
    public function list_datas()
    {
        $datas = $this->get_datas($this->page_no, $this->list_config['listfield'], $this->list_config['orderquery'], $this->list_config['wherequery']);
        $this->datas = $GLOBALS['lurd_datas'] = $datas['data'];
    }

    /**
    * 具体事件——列出数据
    */
    public function e_list(&$request_array)
    {
        //生成模板
        $primary_key = !preg_match("/,/", $this->primary_key) ? $this->primary_key : 'primarykey';
        $testtpl = $this->check_template( 'list' );
        if( !$testtpl[0] || $this->always_make )
        {
            $replaces = array('appname'=>'','lurdurl'=> $this->form_url,'tablename'=> $this->table_name,'primarykey'=>$primary_key,'listtitle'=>'', 'listitem'=>'', 'itemcount'=>2);
            $arr = $this->build_tamplate_list_r( $this->list_config['listfield'] );
            $replaces['listtitle'] = $arr['listtitle'];
            $replaces['listitem'] = $arr['listitem'];
            $replaces['itemcount'] = $arr['itemcount'];
            $replaces['appname'] = empty($this->appname) ? $this->table_name : $this->appname;
            $tmpstr = $this->get_template('list');
            foreach($replaces as $k=>$v)
            {
                $tmpstr = str_replace('~'.$k.'~', $v, $tmpstr);
            }
            file_put_contents($testtpl[1], $tmpstr);
        }
        
        //分页、搜索、排序条件
        $pagesize = $this->page_size;
        $orderby = preg_replace("/[^a-z_]/i", '', (isset($request_array['orderby']) ? $request_array['orderby'] : ''));
        $keyword = trim(isset($request_array['keyword']) ? $request_array['keyword'] : '');
        $this->page_no = !empty($request_array['pageno']) ? intval($request_array['pageno']) : 1;
        $url = "{$this->form_url}&even=list";
        if( $this->list_config['orderquery'] == '' )
        {
            $order_key = '';
            if( $orderby != '') {
                $order_key = $orderby;
            } else if ( isset($this->fields['sortrank']) ) {
                $order_key = 'sortrank';
            } else if ( $this->primary_key != '' ) {
                $order_key = $this->primary_key;
            }
            if( $order_key != '' && isset($this->fields[ $order_key ]) )
            {
                $this->set_order_query("order by `{$order_key}` desc");
                $url .= "&orderby={$orderby}";
            }
        }
        $this->_analyse_search( $keyword, $url );
        
        //是否有排序字段
        $this->assign('has_sort', self::$has_sort);
        
        $tpl = empty($this->tpl_files['list']) ? 'lurd.'.$this->table_name.'.list.tpl' : $this->tpl_files['list'];
        $this->list_datas();
        $this->assign('lurd_pagination', $this->get_pagination($url));
        $this->display_tpl = $tpl;
        
        //显示列表
        $this->display();
        
    }

   /**
    * 具体事件——添加数据（显示表单部份的程序）
    */
    public function e_add(&$request_array)
    {
        //检测模板
        $testtpl = $this->check_template( 'add' );
        if( !$testtpl[0] || $this->always_make )
        {
            $replaces = array('appname'=>'','lurdurl'=> $this->form_url,'tablename'=> $this->table_name, 'listitem');
            $arr = $this->build_tamplate_list_r( $this->list_config['listfield'] );
            $replaces['appname'] = empty($this->appname) ? $this->table_name : $this->appname;
            $replaces['listitem'] = $this->build_tamplate_add();
            $tmpstr = $this->get_template('add');
            foreach($replaces as $k=>$v)
            {
                $tmpstr = str_replace('~'.$k.'~', $v, $tmpstr);
            }
            file_put_contents($testtpl[1], $tmpstr);
        }
        $tpl = empty($this->tpl_files['add']) ? 'lurd.'.$this->table_name.'.add.tpl' : $this->tpl_files['add'];
        $this->display_tpl = $tpl;
        
        //执行前绑定的操作
        if( isset($this->bindHooks['add_start']) ) {
            $this->bindObj->{$this->bindHooks['add_start']}('add_start', $request_array, '');
        }
        
        //正常执行
        $this->display();
        
        //执行后绑定的操作
        if( isset($this->bindHooks['add_end']) ) {
            $this->bindObj->{$this->bindHooks['add_end']}('add_end', $request_array, '');
        }
        
        //自动模式处理方式
        if( !$this->end_need_done )
        {
            exit();
        }
    }

    /**
    * 具体事件——保存添加数据
    */
    public function e_saveadd(&$request_array)
    {
        //执行前绑定的操作
        if( isset($this->bindHooks['saveadd_start']) ) {
            $this->bindObj->{$this->bindHooks['saveadd_start']}('saveadd_start', $request_array, '');
        }
        
        //正常执行
        $this->insert($request_array);
        
        //执行后绑定的操作
        if( isset($this->bindHooks['saveadd_end']) ) {
            $this->bindObj->{$this->bindHooks['saveadd_end']}('saveadd_end', $request_array, db::insert_id());
        }
        
        //自动模式处理方式
        if( !$this->end_need_done )
        {
            cls_msgbox::show('系统提示', '成功增加一条记录！', $this->end_return_url);
            exit();
        }
    }

    /**
    * 具体事件——修改数据（显示表单部份的程序）
    */
    public function e_edit(&$request_array)
    {
        //检测模板
        $primary_key = !preg_match("/,/", $this->primary_key) ? $this->primary_key : 'primarykey';

        if( !isset($request_array[$primary_key]) )
        {
            cls_msgbox::show('系统提示', '没有指定要修改的记录！', '-1');
            exit();
        }

        //获取更新条件
        $keyvalue = $request_array[$primary_key];
        $condition = ( !preg_match("/,/", $this->primary_key) ? " `{$this->primary_key}`='{$keyvalue}' " : " MD5( CONCAT(`".str_replace(',', '`,`', $this->primary_key)."`) ) = '{$keyvalue}' " );

        $testtpl = $this->check_template( 'edit' );
        if( !$testtpl[0] || $this->always_make )
        {
            $replaces = array('appname'=>'','lurdurl'=> $this->form_url,'tablename'=> $this->table_name, 'listitem');
            $replaces['appname'] = empty($this->appname) ? $this->table_name : $this->appname;
            $replaces['listitem'] = $this->build_tamplate_edit();
            $tmpstr = $this->get_template('edit');
            foreach($replaces as $k=>$v)
            {
                $tmpstr = str_replace('~'.$k.'~', $v, $tmpstr);
            }
            file_put_contents($testtpl[1], $tmpstr);
        }
        $sql = "Select * From `{$this->table_name}` where {$condition} limit 1";
        $this->datas[] = db::get_one($sql);
        $tpl = empty($this->tpl_files['edit']) ? 'lurd.'.$this->table_name.'.edit.tpl' : $this->tpl_files['edit'];
        $this->display_tpl = $tpl;
        
        
        //执行前绑定的操作
        if( isset($this->bindHooks['edit_start']) ) {
            $this->bindObj->{$this->bindHooks['edit_start']}('edit_start', $this->datas[0], '');
        }
        
        //正常执行
        $this->display( );
        
        //执行后绑定的操作
        if( isset($this->bindHooks['edit_end']) ) {
            $this->bindObj->{$this->bindHooks['edit_end']}('edit_end', $request_array, '');
        }
        
        //自动模式处理方式的结束
        if( !$this->end_need_done )
        {
            exit();
        }
    }

   /**
    * 具体事件——保存数据修改
    */
    public function e_saveedit(&$request_array)
    {
        //执行前绑定的操作
        if( isset($this->bindHooks['saveedit_start']) ) {
            $this->bindObj->{$this->bindHooks['saveedit_start']}('saveedit_start', $request_array, '');
        }
        
        //正常执行
        $this->update($request_array);
        
        //执行后绑定的操作
        if( isset($this->bindHooks['saveedit_end']) ) {
            $this->bindObj->{$this->bindHooks['saveedit_end']}('saveedit_end', $request_array, '');
        }
        
        //自动模式处理方式
        if( !$this->end_need_done )
        {
            cls_msgbox::show('系统提示', '成功修改一条记录！', $this->end_return_url);
            exit();
        }
    }

    /**
    * 具体事件——删除记录
    */
    public function e_delete(&$request_array)
    {
        if( !preg_match("/,/", $this->primary_key) )
        {
            $primary_key = $this->primary_key;
        } else {
            $primary_key = 'primarykey';
        }
        if( !isset($request_array[$primary_key]) || !is_array($request_array[$primary_key]) )
        {
            cls_msgbox::show('系统提示', '你没指定要删除的记录！', $this->end_return_url);
            exit();
        }
        else
        {
            //执行前绑定的操作
            if( isset($this->bindHooks['delete_start']) ) {
               $this->bindObj->{$this->bindHooks['delete_start']}('delete_start', $request_array[$primary_key], $request_array);
            }
            
            $this->delete($request_array[$primary_key]);
            
            //执行后绑定的操作
            if( isset($this->bindHooks['delete_end']) ) {
               $this->bindObj->{$this->bindHooks['delete_end']}('delete_end', $request_array[$primary_key], $request_array);
            }
            
            //自动模式处理方式
            if( !$this->end_need_done )
            {
                cls_msgbox::show('系统提示', '成功删除指定记录！', $this->end_return_url);
                exit();
            }
        }
    }

    /**
    * 生成列表模板
    * @parem  $listfields
    * @return array()
    */
    protected function build_tamplate_list_r($listfield="*")
    {
        $temparr = array();
        //设置选择项
        $totalitem = 1;
        $titleitem = "  <th> <label for='id[]'><input type='checkbox' name='id[]' id='id[]'  rel='parent' /> 选择</label> </th>\r\n";
        if( !preg_match("/,/", $this->primary_key) )
        {
            $fielditem = "  <td><a href=\"javascript:show_data('<{\$v.{$this->primary_key}}>');\"><img src='../../static/frame/admin/images/icons/text.gif' alt='修改' border='0' /></a> <input type='checkbox' rel='child' class='cbox' name='{$this->primary_key}[]' value='<{\$v.{$this->primary_key}}>' /></td>\r\n";
        }
        else
        {
            $fielditem = "  <td><a href=\"javascript:show_data('<{#lurd do=\"make_key\" var=\$v format=\"{$this->primary_key}\" }>');\"><img src='../../static/frame/admin/images/icons/text.gif' alt='修改' border='0' /></a> <input type='checkbox' rel='child' class='cbox' name='primarykey[]' value=\"<{#lurd do='make_key' var=\$v format='{$this->primary_key}' }>\" /></td>\r\n";
        }
        //使用手工指定列出字段
        if(empty($listfield) || trim($listfield) == '*' )
        {
            $listfield = '';
            foreach($this->fields as $k=>$v)
            {
                $listfield .= ($listfield=='' ? $k : ','.$k);
            }
        }
        $listfields = explode(',', $listfield);
        $totalitem = count($listfields) + 1;
        foreach($listfields as $k)
        {
             $k = trim($k);
             if( !isset($this->fields[$k]) ) continue;
             $v = $this->fields[$k];
             if( !empty($this->fields[$k]['comment']) )
             {
                  $titlename = $this->fields[$k]['comment'];
             } else {
                  $titlename = $k;
             }
             $titleitem .= "    <th>{$titlename}</th>\r\n";
             $totalitem++;
             //排序字段
             if( $k=='sortrank' )
             {
                  $fielditem .= "  <td>\r\n<input type='text' name='sortrank[<{\$v.{$this->primary_key}}>]' value='<{\$v.sortrank}>' style='width:50px;' />
                    <input type='hidden' name='old_sortrank[<{\$v.{$this->primary_key}}>]' value='<{\$v.sortrank}>' />\r\n  </td>\r\n";
             }
             //强制指定字段模板
             else if( !empty($v['listtemplate']) )
             {
                   $fielditem .= "  <td>{$v['listtemplate']}</td>\r\n";
             }
             //lurd特殊类型
             else if( !empty($v['lurd_type'][0]) )
             {
                  $restr = $this->_get_listlurd_template( $v, $k );
                  $fielditem .= "  <td> {$restr} </td>\r\n";
             }
             //bine_type指定的类型
             else
             {
                 $dofunc = $dtype = $fformat = '';
                 $dtype = !empty($v['type']) ? $v['type'] : 'check';
                 if( isset($v['format']) ) {
                      $fformat = $v['format'];
                 }
                 if( isset($v['dofunc']) ) {
                      $dofunc = $v['dofunc'];
                 }
                 if( isset($v['type']) ) {
                      $this->fields[$k]['type'] = $v['type'];
                 }
                 $dofunc = $this->_get_listitem_template( $v['type'], $k, $fformat, $dofunc );
                 $fielditem .= "  <td> $dofunc </td>\r\n";
             }
        }//End foreach
        //是否有联结其它的表
        $islink = count($this->link_tables) > 0 ? true : false;
        //附加表的字段
        if($islink)
        {
          foreach($this->add_fields as $k=>$v)
          {
             if(in_array($v['type'], $this->bin_types) ) {
                 continue;
             }
             $totalitem++;
             $titleitem .= "  <th> $k </th>\r\n";
             $dofunc = $this->_get_listitem_template( $v['type'], $k, '', '' );
             $fielditem .= "  <td> $dofunc </td>\r\n";
          }
        }
        $temparr['listtitle'] = $titleitem;
        $temparr['listitem'] = $fielditem;
        $temparr['itemcount'] = $totalitem;
        return $temparr;
    }

    /**
    * 生成列表模板(仅输出样例，用于由用户手动指定控制类的情况)
    * @parem  $listfields
    * @return string
    */
    public function build_tamplate_list($listfields="*")
    {
        $tempstr = '';
        $arr = $this->build_tamplate_list_r($listfields);
        $tempstr .= "<!-- datas数据来源： \$datas = \$lurdobj->get_datas( \$pageno, \$listfield, \$orderquery); -->\r\n";
        $tempstr .= '<form name="form1" action="" method="POST">'."\n";
        $tempstr .= '<table width="100%" border="0" cellspacing="1" cellpadding="1">'."\n";
        $tempstr .= "<tr>\r\n".$arr['listtitle']."</tr>\r\n";
        $tempstr .= "<{foreach from=\$datas.data key=k item=v}>\r\n";
        $tempstr .= "<tr>\r\n".$arr['listitem']."</tr>\r\n";
        $tempstr .= "<{/foreach}>\r\n";
        $tempstr .= "</table>\r\n";
        $tempstr .= "<form>\r\n";
        $tempstr .= "<{\$datas.pagination}>";
        return $tempstr;
    }
    
   /**
    * 获取lurd类型list字段模板
    */
    protected function _get_listlurd_template( &$v, $fname )
    {
        $restr = "<{\$v.{$fname}}>";
        if( $v['lurd_type'][0]=='catalog' )
        {
            $restr = "<{\$v.{$fname}.typeid|mod_catalog::get_name({$v['lurd_type'][1]}, @me)}>";
        }
        return $restr;
    }

   /**
    * 获取list普通或bindtype字段处理模板
    */
    protected function _get_listitem_template( $ftype, $fname, $fformat='', $dofunc='' )
    {
        if( in_array($ftype, $this->float_types) )
        {
            $dofunc = ($dofunc=='' ? "<{#lurd do=\"format_float\" var=\$v.{$fname} format=\"$fformat\" }>" : $dofunc);
        }
        else if( in_array($ftype, $this->date_types) )
        {
            $dofunc = ($dofunc=='' ? "<{#lurd do=\"format_date\" var=\$v.{$fname} format=\"$fformat\" }>" : $dofunc);
        }
        else {
            $dofunc = ($dofunc=='' ? "<{\$v.{$fname}}>" : $dofunc);
        }
        return $dofunc;
    }

    /**
    * 生成列表发布模板
    * @return string
    */
    public function build_tamplate_add()
    {
        return $this->build_tamplate_edit( 'add' );
    }

    /**
     *
     * 生成编辑模板
     *
     * @return string
    */
    public function build_tamplate_edit( $getTemplets='edit' )
    {
        $tempstr = '';
        $tempItems = array('fields'=>'', 'primarykey'=>'');
        $tempItems['fields'] = '';
        if( !preg_match('/,/', $this->primary_key) )
        {
            $tempItems['primarykey'] = "<input type='hidden' name='{$this->primary_key}' value='<{\$v.{$this->primary_key}}>' />\r\n";
        }
        else
        {
            $tempItems['primarykey'] = "<input type='hidden' name='primarykey' value=\"<{#lurd var=\$data do='make_key' format='{$this->primary_key}' }>\" />\r\n";
        }
        $fielditem = '';
        foreach($this->fields as $k=>$v)
        {
            $aeform = $dtype = $defaultvalue = $fformat = '';
            //在指定了字段模板情况下不使用自动生成
            if(isset($this->fields[$k][$getTemplets.'template']))
            {
                $fielditem .= $this->fields[$k][$getTemplets.'template'];
                continue;
            }
            //排除自动递增键和主键
            if( $k==$this->auto_field || $k==$this->primary_key )
            {
                continue;
            }
            
            //格式化选项(编辑时用，使用bind_type指定的数据)
            if(isset($this->fields[$k]['format']))
            {
                $fformat = $this->fields[$k]['format'];
            }
            
            //获得字段默认值（编辑时从数据库获取）
            if($getTemplets=='edit')
            {
                if( in_array($this->fields[$k]['type'], $this->bin_types) ) $dfvalue = '';
                else $dfvalue = "<{\$v.{$k}}>";
            } else {
                $dfvalue = $this->fields[$k]['default'];
            }
            
            //标题
            if( !empty($this->fields[$k]['comment']) )
            {
                $titlename = $this->fields[$k]['comment'];
            } else {
                $titlename = $k;
            }
            
            //lurd通过在commect指定的类型
            if( !empty($this->fields[$k]['lurd_type'][0]) )
            {
                $win_arg = '"scrollbars=yes,resizable=yes,statebar=no,width=600,height=400,left=100,top=100"';
                if( $this->fields[$k]['lurd_type'][0] == 'soft')
                {
                    if($getTemplets=='add')
                    {
                         $aeform  = "<input type='text' name='{$k}' id='lurd_{$k}' class='text' value='$dfvalue' />";
                    } else {
                         $aeform  = "<input type='text' name='{$k}' id='lurd_{$k}' class='text' value='<{\$v.{$k}}>' />";
                    }
                    $aeform .= pub_field_tool::get_dlg_btn("lurd_{$k}", 'soft', $win_arg);
                }
                else if( $this->fields[$k]['lurd_type'][0] == 'image')
                {
                    if($getTemplets=='add')
                    {
                         $aeform  = "<input type='text' name='{$k}' id='lurd_{$k}' class='text' value='$dfvalue' />";
                    } else {
                         $aeform  = "<input type='text' name='{$k}' id='lurd_{$k}' class='text' value='<{\$v.{$k}}>' />";
                    }
                    $preImg = $getTemplets=='edit' ? "<{\$v.{$k}}>" : '../../static/frame/admin/images/preview.gif';
                    $aeform  .= pub_field_tool::get_dlg_btn("lurd_{$k}", 'images', $win_arg);
                    $aeform  .= "<br /><img src='{$preImg}' id='preimg_lurd_{$k}' width='80' style='margin-top:10px;' />\r\n";
                }
                else if( $this->fields[$k]['lurd_type'][0] == 'catalog')
                {
                    $aeform = "<select name='{$k}'>\r\n";
                    if($getTemplets=='add')
                    {
                         $aeform  .= "<{#catalog_options cmid='{$this->fields[$k]['lurd_type'][1]}' selid=0 dfname='--请选择--' }>";
                    } else {
                         $aeform  .= "<{#catalog_options cmid='{$this->fields[$k]['lurd_type'][1]}' selid=\$v.{$k} dfname='--请选择--' }>";
                    }
                    $aeform .= "</select>\r\n";
                }
            }
            //普通类型
            else
            {
                //小数类型
                if( in_array($this->fields[$k]['type'], $this->float_types) )
                {
                    if($getTemplets=='edit')
                    {
                        $dfvalue = "<{#lurd var=\$v.{$k} do=\"format_float\" format=\"{$fformat}\" }>";
                    }
                    else if($this->fields[$k]['default']=='')
                    {
                        $dfvalue = 0;
                    }
                    $aeform  = "<input type='text' name='{$k}' class='text s' value='{$dfvalue}' />";
                }
                //整数类型
                if( in_array($this->fields[$k]['type'], $this->int_types) )
                {
                    $aeform  = "<input type='text' name='{$k}' class='text s' value='{$dfvalue}' />";
                }
                //时间类型
                else if( in_array($this->fields[$k]['type'], $this->date_types))
                {
                    if(empty($fformat)) $fformat = 'Y-m-d H:i:s';
                    if($getTemplets=='edit')
                    {
                        $dfvalue = "<{#lurd var=\$v.{$k} do=\"format_date\" format=\"$fformat\" }>";
                    }
                    else if(empty($this->fields[$k]['default']))
                    {
                        $dfvalue = "<{#lurd var=\"\" do=\"format_date\" format=\"\" }>";
                    }
                    $aeform  = "<input type='text' name='{$k}' class='txts' value='$dfvalue' />";
                }
                //长文本类型
                else if( in_array($this->fields[$k]['type'], $this->text_types))
                {
                    $aeform  = "<textarea name='$k' class='txtarea'>{$dfvalue}</textarea>";
                }
                //二进制类型
                else if( in_array($this->fields[$k]['type'], $this->text_types))
                {
                    $aeform = "<input type='file' name='$k' size='45' />";
                }
                //SET类型
                else if( $this->fields[$k]['type']=='SET' )
                {
                    $ems = explode(',', $this->fields[$k]['em']);
                    if($getTemplets=='edit')
                    {
                        $aeform .= "<{#lurd_set item='em' em='{$fields[$k]}' sel='$v.{$k}'}>";
                    }
                    foreach($ems as $em)
                    {
                        if($getTemplets=='add')
                        {
                           $aeform .= "<input type='checkbox' name='{$k}[]' value='<{$em}>' /><{$em}> \n";
                        } else {
                           $aeform .= "<input type='checkbox' name='{$k}[]' value='<{$em}>' <{if in_array('$em', \$enumItems)}>checked<{/if}> /><{$em}> \n";
                        }
                    }
                    $aeform .= "<{/lurd_set}>";
                }
                //ENUM类型
                else if( $this->fields[$k]['type']=='ENUM' )
                {
                    $ems = explode(',', $this->fields[$k]['em']);
                    foreach($ems as $em)
                    {
                        if($getTemplets=='edit')
                        {
                            $aeform .= "<input type='radio' name='$k' value='$em' <{if \$v.{$k}=='$em'}>checked<{/if}> />$em \n";
                        } else {
                            $aeform .= "<input type='radio' name='$k' value='$em' />$em \n";
                        }
                    }
                }
                else
                {
                    if($getTemplets=='add')
                    {
                        $aeform  = "<input type='text' name='{$k}' id='lurd_{$k}' class='text' value='$dfvalue' />";
                    } else {
                        $aeform  = "<input type='text' name='{$k}' id='lurd_{$k}' class='text' value='<{\$v.{$k}}>' />";
                    }
                 
                }
            }
            $fielditem .= "<tr>\r\n  <td class='title'>{$titlename}：</td>\r\n  <td class='fitem'>{$aeform}</td>\r\n</tr>\r\n";
        } //end foreach
        $tempItems['fields'] = $fielditem;
        if($getTemplets=='edit') {
            $tempstr .= $tempItems['primarykey'];
        }
        $tempstr .= $tempItems['fields'];
        return $tempstr;
    } //build_tamplate_edit
    
   /**
    * 字段特殊类型设置
    * 在字段comment中用@***@来声明特殊类型
    * @param $fields
    * @return void
    */
    public static function _check_lurd_type( &$fields )
    {
        foreach($fields as $field => $infos)
        {
            preg_match("/@([\w]+)[-]{0,}(.*)@/", $infos['comment'], $arr);
            if( $field=='sortrank' ) {
                self::$has_sort = true;
            }
            if( empty($arr[1]) )  {
                continue;
            } else {
                $fields[$field]['comment'] = preg_replace("/@([\w]+)[-]{0,}(.*)@/", '', $infos['comment']);
                $fields[$field]['lurd_type'] = array($arr[1], $arr[2]);
            }
        }
    }

 } //end class
