<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * ue编辑器调用接口
 */
class ctl_ue_editor
{
    public function config()
    {
        $CONFIG = '{
        "imageActionName": "editor_upload",
        "imageFieldName": "upfile", 
        "imageMaxSize": 2048000,
        "imageAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"], 
        "imageCompressEnable": true, 
        "imageCompressBorder": 1600, 
        "imageInsertAlign": "none",
        "imageUrlPrefix": "",
        "imagePathFormat": "/uploads/images/{yyyy}{mm}{dd}/{time}{rand:6}", 

        "scrawlActionName": "uploadscrawl", 
        "scrawlFieldName": "upfile", 
        "scrawlPathFormat": "/uploads/images/{yyyy}{mm}{dd}/{time}{rand:6}", 
        "scrawlMaxSize": 2048000, 
        "scrawlUrlPrefix": "", 
        "scrawlInsertAlign": "none",

        "snapscreenActionName": "uploadimage",
        "snapscreenPathFormat": "/uploads/images/{yyyy}{mm}{dd}/{time}{rand:6}", 
        "snapscreenUrlPrefix": "", 
        "snapscreenInsertAlign": "none", 

        "catcherLocalDomain": ["127.0.0.1", "localhost"],
        "catcherActionName": "catchimage", 
        "catcherFieldName": "source", 
        "catcherPathFormat": "/uploads/images/{yyyy}{mm}{dd}/{time}{rand:6}", 
        "catcherUrlPrefix": "",
        "catcherMaxSize": 2048000, 
        "catcherAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"], 

        "videoActionName": "uploadvideo", 
        "videoFieldName": "upfile", 
        "videoPathFormat": "/uploads/video/{yyyy}{mm}{dd}/{time}{rand:6}",
        "videoUrlPrefix": "", 
        "videoMaxSize": 102400000, 
        "videoAllowFiles": [
                ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
                ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"], 

        "fileActionName": "uploadfile",
        "fileFieldName": "upfile",
        "filePathFormat": "/uploads/file/{yyyy}{mm}{dd}/{time}{rand:6}",
        "fileUrlPrefix": "", 
        "fileMaxSize": 51200000, 
        "fileAllowFiles": [
                ".png", ".jpg", ".jpeg", ".gif", ".bmp",
                ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
                ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
                ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
                ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
        ], 
        "imageManagerActionName": "listimage",
        "imageManagerListPath": "/uploads/images/", 
        "imageManagerListSize": 20,
        "imageManagerUrlPrefix": "", 
        "imageManagerInsertAlign": "none",
        "imageManagerAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"],

        "fileManagerActionName": "listfile",
        "fileManagerListPath": "/uploads/file/", 
        "fileManagerUrlPrefix": "",
        "fileManagerListSize": 20, 
        "fileManagerAllowFiles": [
                ".png", ".jpg", ".jpeg", ".gif", ".bmp",
                ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
                ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
                ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
                ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
        ] 

        }';
        echo $CONFIG;
    }
    
    /**
     * 百度editor
     * @return [type] [description]
     */
     public function editor_upload()
     {
        //header( 'Content-Type: text/javascript' );
        header('Content-type: text/html; charset=UTF-8');
        $fetch = request('fetch', '');
        //获取上传目录
        if($fetch == '1')
        {  
            $imgSavePathConfig = array ( '/uploads/images/' );
            echo 'updateSavePath('. json_encode($imgSavePathConfig) .');';
            exit();
        }
        //上传管理（swfupload虽然每次上传多个，但每个文件是单独处理的）
        try
        {
            $refile = pub_media_dlg::uploadfile('', 1, 'upfile');
            $rearr = array('state' => 'SUCCESS', 'url' => $refile['path'].'/'.$refile['filename'], 'refile' => $refile);
            exit( json_encode( $rearr ) );
        }
        catch( Exception $e )
        {
            exit( json_encode( array('state' => $e->getMessage(), 'url' => '') ) );
        }
    }
    
}
