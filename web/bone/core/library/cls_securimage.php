<?php
/**
 * 验证码类
 * @version $Id$  
 */
@session_start();
class cls_securimage
{
    public $text_num;
    public $interference_line;
    public $ttf_file;
    public $im_x;
    public $im_y;
    public $session_name;
    public $text;

    public function  __construct($text_num=4, $im_x = 150, $im_y = 30, $scale = 3, $session_name='securimage_code_value')
    {
        $this->text_num = $text_num;
        $this->interference_line = true;
        $this->ttf_file = PATH_SHARE.'/securimage_font/'.mt_rand(1,3).'.ttf';
        $this->im_x = $im_x;
        $this->im_y = $im_y;
        $this->scale = $scale;
        $this->session_name = $session_name;
    }

   /**
    * 显示验证码
    */
    public function show()
    {

        //获取字符串并写入session
        $this->_get_rand_text();
        
        $this->im_x *= $this->scale;
        $this->im_y *= $this->scale;

        $im = imagecreatetruecolor($this->im_x, $this->im_y);
        //颜色
        $text_c = ImageColorAllocate($im, mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
        //背景
        $buttum_c = ImageColorAllocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $buttum_c);
        
        //字体大小取高度或字符平均宽度90%中最小的一个
        $size = min($this->im_y, $this->im_x/$this->text_num);
        $size = ceil( $size * 0.9 );

        //输出文字
        for ($i=0; $i < $this->text_num; $i++)
        {
            $ypos = $size;
            //特殊字符调整y轴基数
            if(preg_match('/[ygJup]/', $this->text[$i])) {
                $ypos = ceil($ypos * 0.8); 
            }
            imagettftext($im, $size, 0, $this->im_x * 0.01 + $i * $size * 1.2, $ypos, 
                         $text_c, $this->ttf_file, $this->text[$i]);       
        }
        
        //背景线
	    $lineColor1 = ImageColorAllocate($im, 240, 220, 180);
	    $lineColor2 = ImageColorAllocate($im, 250, 250, 170);
	    for($j=3; $j <= $this->im_y; $j=$j+($this->im_y/6))
	    {
		    imageline($im, 2, $j, $this->im_x - 2, $j, $lineColor1);
	    }
	    for($j=2; $j < $this->im_x + 2; $j=$j+mt_rand(5, 10))
	    {
		    imageline($im, $j, 2, $j-6, $this->im_y - 2, $lineColor2);
	    }
	    
	    //最终图片
	    $distortion_im = imagecreatetruecolor($this->im_x, $this->im_y);
        $text_c = ImageColorAllocate($distortion_im, mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
        $buttum_c = ImageColorAllocate($distortion_im, 255, 255, 255);
        imagefill($distortion_im, 0, 0, $buttum_c);

        // 扭曲
        $xp = 10*rand(1,3);
        $k  = 10*rand($this->im_x, $this->im_y);
        for ($i = 0; $i < ($this->im_x); $i++) {
            imagecopy($distortion_im, $im, $i-4, sin($k+$i/$xp)*4 , $i, 0, 1, $this->im_y);
        }

        // 缩小
        $imResampled = imagecreatetruecolor($this->im_x/$this->scale, $this->im_y/$this->scale);
        imagecopyresampled($imResampled, $distortion_im, 0, 0, 0, 0, 
                           $this->im_x/$this->scale, $this->im_y/$this->scale, $this->im_x, $this->im_y );
        imagedestroy($distortion_im);
        $distortion_im = $imResampled;
        
        
        //清空其它源码可能残留的空白字符
        ob_clean();
        header("Content-type: image/jpeg");
        //ImagePNG($distortion_im);
        ImageJPEG($distortion_im);
        ImageDestroy($distortion_im);
        ImageDestroy($im);
    }

   /**
    * 生成随机字符串
    */
    protected function _get_rand_text()
    {
        $str    = 'ACDEFGHJKMNPQRSTUVWXYZacdefghkmnprstuvwxyz234578';
        $result = '';
        for($i=0; $i < $this->text_num; $i++) {
            $num[$i] = rand(0,strlen($str)-1);
            $result .= $str[$num[$i]];
        }
        $_SESSION[$this->session_name] = strtolower($result);
        $this->text = $result;
    }
    
   /**
    * 检查验证码
    */
    public function check($code)
    {
        if( empty($_SESSION[$this->session_name]) ) {
            return false;
        }
        if( $_SESSION[$this->session_name] ==  strtolower($code) ) {
            return true;
        }
        return false;
    }
}
