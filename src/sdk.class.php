<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * YoudaoAi PHP SDK v1.0.1
 */

//设置默认时区
date_default_timezone_set('Asia/Shanghai');

defined('DS') OR define('DS', DIRECTORY_SEPARATOR);
//检测API路径
defined('SDK_PATH') OR define('SDK_PATH', dirname(__FILE__).DS);

//定义软件名称，版本号等信息
define('SDK_NAME','YoudaoAi-sdk-php');
define('SDK_VERSION','1.0.0');
define('SDK_AUTHOR', 'leander@tchost.cn');

//加载conf.inc.php文件
require_once SDK_PATH.'conf.inc.php';

//加载common.inc.php文件
require_once SDK_PATH.'lib'.DS.'common.inc.php';

//加载common.inc.php文件
require_once SDK_PATH.'lang'.DS.'zh.inc.php';

/**
 * SDK异常类，继承自基类
 */
class SDK_Exception extends Exception {}

/**
 * SDK基础类
 * @author leander@tchost.cn
 * @since 2012-05-31
 */
class YoudaoAi{

	/**
	 * OSS服务地址
	 */
	const DEFAULT_HOST = 'openapi.youdao.com';

	/**
	 * 软件名称
	 */
	const NAME = SDK_NAME;

	/**
	 * 版本号
	 */
	const VERSION = SDK_VERSION;

	/**
	 * 作者
	 */
	const AUTHOR = SDK_AUTHOR;
	
	/**
	 * APP_KEY
	 */
    private $app_key;

	/**
	 * SEC_KEY
	 */
    private $sec_key;

    /**
     * API服务器
     */
    private $api_serv;
    
    /**
     * API路径
     */
    private $api_path;

	/**
	 * debug_mode
	 */
    private $debug_mode = true;

	/**
	 * 默认构造函数
	 * @param string $appkey (Optional)
	 * @param string $seckey (Optional)
	 * 
	 * @throws SDK_Exception
	 * @author	leander@tchost.cn
	 * @since	2011-11-08
	 */
	public function __construct($appkey = NULL,$seckey = NULL){
		if(!$appkey && !defined('APP_KEY')){
				throw new SDK_Exception(NOT_SET_APP_KEY);
		}

		if(!$seckey && !defined('SEC_KEY')){
			throw new SDK_Exception(NOT_SET_SEC_KEY);
		}

		if($appkey && $seckey){
			$this->app_key = $appkey;
			$this->sec_key = $seckey;
		}elseif (defined('APP_KEY') && defined('SEC_KEY')){
			$this->app_key = APP_KEY;
			$this->sec_key = SEC_KEY;
		}else{
			throw new SDK_Exception(NOT_SET_APP_KEY_AND_SEC_KEY);
		}

		//校验app_key&sec_key 
		if(empty($this->app_key) || empty($this->sec_key)){
			throw new SDK_Exception(APP_KEY_OR_SEC_KEY_EMPTY);
		}

		//校验API服务器
		if(defined('API_SERV')){
			$this->api_serv = API_SERV;
		}else{
			throw new SDK_Exception(NOT_SET_API_SERV);
		}

		//校验API路径
		if(defined('API_PATH')){
			$this->api_path = json_decode(API_PATH, TRUE);
		}else{
			throw new SDK_Exception(NOT_SET_API_PATH);
		}
	}
	
    /**
     * 自然语言解析/文本翻译
     * @param string $query 待翻译文段
     * @param string $from 原文段语言代码，代码参见/lib/trans/lang.php
     * @param string $to 目标语言代码
     * 
     * @return Array $ret 该结果由官方定义
     */
    public function translate($query, $from, $to) {
        require_once 'lib/trans/lang.php';
        if(!(in_array($from, $lang) && in_array($to, $lang))) {
            throw new SDK_Exception(TRANS_NOT_SUPPORT_LANGUAGE);
        }
        $args = array(
            'q' => $query,
            'appKey' => $this->app_key,
            'salt' => rand(10000,99999),
            'from' => $from,
            'to' => $to,
        );
        $args['sign'] = buildSign($this->app_key, $query, $args['salt'], $this->sec_key);
        $ret = call($this->api_serv.$this->api_path['trans'], $args);
        $ret = json_decode($ret, true);
        return $ret;
    }
    
    	
    /**
     * 自然语言解析/语音翻译
     * @param string $file 音频文件路径
     * @param string $from 原文段语言代码，代码参见/lib/sti/lang.php
     * @param string $to 目标语言代码
     * @param string $format 语音文件的格式， 目前只支持wav，不区分大小写
     * @param string $channel 声道数， 仅支持单声道，请填写固定值1
     * $param string $rate 上传类型， 仅支持base64上传，请填写固定值1
     * 
     * @return Array $ret 该结果由官方定义
     */
    function speechtrans($file, $type, $from, $to, $format = 'format', $channel = 1, $rate = 1) {
        require_once 'lib/sti/lang.php';
        if(!(in_array($from, $lang) && in_array($to, $lang))) {
            throw new SDK_Exception(TRANS_NOT_SUPPORT_LANGUAGE);
        }

        if(!$fp=fopen($file, "r"))
            throw SDK_Exception(FAIL_TO_OPEN_FILE);
        $q = chunk_split(base64_encode(fread($fp, filesize($file))));//base64编码
        fclose($fp);

        $args = array(
            'q' => $q,
            'appKey' => APP_KEY,
            'salt' => rand(10000,99999),
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'format' => $format,
            'channel' => $channel,
            'rate' => $rate,
        );
        $args['sign'] = buildSign(APP_KEY, $q, $args['salt'], SEC_KEY);
        $ret = call($this->api_serv.$this->api_path['sti'], $args);
        $ret = json_decode($ret, true);
        return $ret;
    }

    /**
     * 自然语言解析/图片翻译
     * @param string $file 图片文件路径
     * @param string $from 原文段语言代码，代码参见/lib/oti/lang.php
     * @param string $to 目标语言代码
     * 
     * @return Array $ret 该结果由官方定义
     */
    function ocrtrans($file, $type, $from, $to) {
        require_once 'lib/oti/lang.php';
        if(!(in_array($from, $lang) && in_array($to, $lang))) {
            throw new SDK_Exception(TRANS_NOT_SUPPORT_LANGUAGE);
        }

        if(!$fp=fopen($file, "r"))
            throw SDK_Exception(FAIL_TO_OPEN_FILE);
        $q = chunk_split(base64_encode(fread($fp, filesize($file))));//base64编码
        fclose($fp);

        $args = array(
            'q' => $q,
            'appKey' => APP_KEY,
            'salt' => rand(10000,99999),
            'type' => $type,
            'from' => $from,
            'to' => $to );
        $args['sign'] = buildSign(APP_KEY, $q, $args['salt'], SEC_KEY);
        $ret = call($this->api_serv.$this->api_path['oti'], $args);
        $ret = json_decode($ret, true);
        return $ret;
    }

	/**
	 * 设置debug模式
	 * @param boolean $debug_mode (Optional)
	 * 
	 * @return void
	 */
	public function set_debug_mode($debug_mode = true){
		$this->debug_mode = $debug_mode;
	}
}
