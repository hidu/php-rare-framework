<?php
namespace Rare\Util;

/**
 * html表单输出工具类
 */
class Html {
    private static $autoID = false;
    private static $autoClass = true;
    /**
     * 是否允许自动添加id字段
     * 
     * @param boolean $enable            
     */
    public static function enableAutoID($enable) {
        self::$autoID = $enable;
    }
    /**
     * 是否允许自动添加统一的class
     * 如input-text添加r-text
     * 
     * @param boolean $autoClass            
     */
    public static function enableAutoClass($autoClass) {
        self::$autoClass = $autoClass;
    }
    /**
     * 检查select radio_group checkbox_group的 选项
     * 如 选项为 array(3,5,8) 一般期望为 其key 和value同样的值
     * 
     * @param array $options            
     */
    public static function options($options) {
        $tmp = array_values ( $options );
        if ($tmp == $options) {
            $tmp = array ();
            foreach ( $options as $option ) {
                $tmp [$option] = $option;
            }
            return $tmp;
        }
        return $options;
    }
    
    public static function select($name, $value, $options, $params = '') {
        $params = self::_paramMergeWithClass ( array (), $params, $name, 'select' );
        $html = "<select " . self::_paramMerge ( $params, true ) . ">";
        if (! is_array ( $value )) {
            $value = explode ( ",", $value );
        }
        $value = array_flip ( $value );
        foreach ( $options as $_k => $_v ) {
            $html .= "<option value=\"" . self::h ( $_k ) . "\"" . (array_key_exists ( $_k, $value ) ? ' selected="selected"' : "") . ">" . self::h ( $_v ) . "</option>";
        }
        return $html . "</select>";
    }
    
    public static function textArea($name, $value = '', $params = '') {
        $params = self::_paramMergeWithClass ( array (), $params, $name, 'textarea' );
        return '<textarea ' . self::_paramMerge ( $params, true ) . ">" . self::h ( $value ) . "</textarea>";
    }
    
    public static function radio($name, $customValue, $itemValue, $params = "") {
        $_param = array ();
        if ($customValue === true) {
            $customValue = $itemValue;
        }
        if (strcmp ( $customValue, $itemValue ) == 0) {
            $_param ['checked'] = 'true';
        }
        return self::inputTag ( 'radio', $name, $itemValue, $params, $_param );
    }
    
    /**
     *
     * @param string $name            
     * @param string $value            
     * @param array $options            
     * @param string|array $params            
     */
    public static function radioGroup($name, $value, $options, $params = "") {
        $html = "<span class='r-radio-group'>";
        foreach ( $options as $_k => $_v ) {
            $_param = array ();
            if (strcmp ( $_k, $value ) == 0) {
                $_param ['checked'] = "checked";
            }
            $_param ['id'] = '';
            $html .= "<label>" . self::inputTag ( 'radio', $name, $_k, $params, $_param ) . self::h ( $_v ) . "</label>";
        }
        $html .= "</span>";
        return $html;
    }
    
    public static function checkboxGroup($name, $value, $options, $params = '') {
        if (! is_array ( $options )) {
            $options = array ($options => '');
        }
        if (! is_array ( $value )) {
            $value = explode ( ",", $value );
        }
        $html = "<span class='r-checkbox-group'>";
        $params = self::_paramMerge ( $params );
        foreach ( $options as $_k => $_v ) {
            $_param = array ();
            if (in_array ( $_k, $value )) {
                $_param ['checked'] = "checked";
            }
            if (! empty ( $params ['id'] ) && $params ['id']) {
                $_param ['id'] = $params ['id'] . "_" . $_k;
            } else if (self::$autoID) {
                $_param ['id'] = self::getIDByName ( $name ) . "_" . $_k;
            }
            $html .= "<label>" . self::inputTag ( 'checkbox', $name, $_k, $params, $_param ) . "{$_v}</label>";
        }
        $html .= "</span>";
        return $html;
    }
    
    /**
     *
     * @param string $name            
     * @param string $customValue
     *            用户输入的值，可能是数据库读取的
     * @param string $itemValue
     *            当前item的值
     * @param string|array $params            
     */
    public static function checkbox($name, $customValue, $itemValue, $params = '') {
        $_param = array ();
        if ($customValue === true) {
            $customValue = $itemValue;
        }
        if (strcmp ( $customValue, $itemValue ) == 0) {
            $_param ['checked'] = 'true';
        }
        return self::inputTag ( 'checkbox', $name, $itemValue, $_param, $params );
    }
    
    public static function input($name, $value = "", $params = "") {
        return self::inputTag ( 'text', $name, $value, $params );
    }
    
    public static function hidden($name, $value, $params = "") {
        return self::inputTag ( 'hidden', $name, $value, $params );
    }
    
    public static function inputFile($name, $params = "") {
        return self::inputTag ( 'file', $name, "", $params );
    }
    
    public static function password($name, $value = '', $params = "") {
        return self::inputTag ( 'password', $name, $value, $params );
    }
    
    public static function inputImage($src, $params = "") {
        return self::inputTag ( "image", '', '', $params, array (
            'src' => $src 
        ) );
    }
    
    public static function inputButton($label, $params = "") {
        return self::inputTag ( "button", '', $label, $params );
    }
    
    public static function inputSubmit($label = '', $params = "") {
        return self::inputTag ( "submit", '', $label, $params );
    }
    
    public static function submit($label = "", $params = "") {
        return self::inputSubmit ( $label, $params );
    }
    
    public static function inputReset($label, $params = '') {
        return self::inputTag ( "reset", '', $label, $params );
    }
    
    /**
     * html5
     * 
     * @param string $name            
     * @param array|string $params            
     */
    public static function inputEmail($name, $value, $params = '') {
        return self::inputTag ( 'email', $name, $value, $params );
    }
    
    /**
     * html5
     * 
     * @param string $name            
     * @param mix $params            
     */
    public static function inputSearch($name, $value, $params = '') {
        return self::inputTag ( 'search', $name, $value, $params );
    }
    
    /**
     * html5
     * 
     * @param string $name            
     * @param mix $params            
     */
    public static function inputUrl($name, $value, $params = '') {
        return self::inputTag ( 'url', $name, $value, $params );
    }
    
    public static function h($value) {
        return htmlspecialchars ( $value, ENT_QUOTES );
    }
    
    public static function getIDByName($name) {
        return trim ( str_replace ( array (
            "][",
            "[",
            "]" 
        ), array (
            "_",
            "_",
            "" 
        ), $name ), "_" );
    }
    public static function a($url, $text, $params = '') {
        if (! rare_isUrl ( $url ) && ! rare_strStartWith ( $url, '#' ) && ! rare_strStartWith ( $url, "javascript:" )) {
            $url = url ( $url );
        }
        return '<a href="' . self::h ( $url ) . '"' . self::_paramMerge ( $params, true ) . ">" . self::h ( $text ) . "</a>";
    }
    
    public static function jsAlertGo($message, $url) {
        $go = is_int ( $url ) ? "history.go($url)" : "location.href='{$url}'";
        return '<script>' . (strlen ( $message ) ? 'alert("' . self::h ( addcslashes ( $message, "\n\r" ) ) . '");' : '') . $go . ';</script>';
    }
    
    /**
     *
     * @param mixed $params1            
     * @param mixed $params2            
     * @param string $name            
     * @param string $type            
     * @return array
     */
    private static function _paramMergeWithClass($params1, $params2, $name, $type) {
        $param = self::_paramMerge ( $params1, $params2 );
        if (self::$autoClass) {
            $param ['class'] = "r-" . $type . (isset ( $param ['class'] ) ? " " . $param ['class'] : "");
        }
        
        if ($name) {
            $param ['name'] = $name;
            if (self::$autoID && ! array_key_exists ( "id", $param )) {
                $param ['id'] = self::getIDByName ( $name );
            }
        }
        return $param;
    }
    public static function inputTag($type = 'text', $name = '', $value = '', $param = "", $paramMore = '') {
        $param = self::_paramMergeWithClass ( $param, $paramMore, $name, $type );
        $paramStr = self::_paramMerge ( $param, true );
        return "<input type=\"{$type}\" value=\"" . self::h ( $value ) . "\"{$paramStr}/>";
    }
    
    
    private static function _paramMerge() {
        $numargs = func_num_args ();
        $param = array ();
        for($i = 0; $i < $numargs; $i ++) {
            $_param = func_get_arg ( $i );
            if ($numargs - 1 == $i && $_param === true) {
                continue; // 最后一个参数为true,将所有数组按照字符串返回
            }
            if (is_string ( $_param )) {
                $_param = self::stringToArray ( $_param );
            }
            if (! is_array ( $_param )) {
                $_param = array ();
            }
            $param = array_merge ( $param, $_param );
        }
        if ($_param === true) {
            $str = "";
            foreach ( $param as $_k => $_v ) {
                if (is_null ( $_v ) || ! strlen ( $_v )) {
                    continue;
                }
                $str .= $_k . '="' . self::h ( $_v ) . '" ';
            }
            return $str ? " " . trim ( $str ) : "";
        }
        return $param;
    }
   
   /**
    * 将字符串参数解析成数组
    * copy from sfToolkit
    * @param string $string  eg: $str="style='width:50px' data-curent=1 readonly=\"readonly\"";
    * @return array  eg:array("style"=>"width:50px","data-curent"=>1,"readonly"=>"readonly")
    */
   public static function stringToArray($string) {
        preg_match_all ( '/
      \s*([\w-]+)              # key                               \\1
      \s*=\s*               # =
      (\'|")?               # values may be included in \' or " \\2
      (.*?)                 # value                             \\3
      (?(2) \\2)            # matching \' or " if needed        \\4
      \s*(?:
        (?=[\w-]+\s*=) | \s*$  # followed by another key= or the end of the string
      )
    /x', $string, $matches, PREG_SET_ORDER );
        
        $attributes = array ();
        foreach ( $matches as $val ) {
            $attributes [$val [1]] = $val [3];
        }
        return $attributes;
    }
    
    /**
     * html5
     * @param string $id            
     * @param string|array $values            
     */
    public static function datalist($id, $values) {
        if (is_string ( $values )){
            $values = explode ( ",", $values );
        }
        $html = '<datalist id="' . $id . '">';
        foreach ( $values as $val ) {
            $html .= '<option value="' . self::h ( $val ) . '">';
        }
        return $html . "</datalist>";
    }
    
    
    /**
     * 使用post将数据提交到指定的地址
     * 
     * @param string $url
     *            提交的action 地址
     * @param array $params
     *            提交的参数
     * @param string $charset
     *            提交目标的编码
     */
    public static function postToUrl($url, $params = array(), $charset = "utf-8") {
        $html = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
                </head>
                <body onload='document.form1.submit()'>";
        // fix ie charset
        $charset4ie = strtoupper ( $charset ) == 'utf-8' ? "" : "<script>if(top.execScript){document.charset=\"$charset\";}</script>";
        $html .= $charset4ie;
        
        $html .= "<form action='{$url}' method='post' name='form1' accept-charset='{$charset}'>";
        foreach ( $params as $k => $v ) {
            $html .= self::hidden ( $k, $v );
        }
        $html .= "</form>";
        $html .= "</body></html>";
        return $html;
    }
    
    /**
     * 压缩html 代码 取出换行符，回车符和多余空白
     *
     * 该函数会有几ms的时间消耗，但是压缩为一行的代码兼容性更好（空格符、换行符的表现）
     * 书写的html代码需要满足以下条件:
     * 1.javascript代码中不能使用单行注释
     * 2.javascript每句完成后添加；
     * 
     * @param string $html            
     * @param boolean $allSpec 是否支持pre、textarea标签
     */
    public static function reduceSpace($html, $allSpec = false) {
        $pattern = array ();
        $replacement = array ();
        $spec_tags = array (
            "pre",
            "textarea" 
        );
        $st = array ();
        if ($allSpec) {
            foreach ( $spec_tags as $tag ) {
                $rule = "#\s?<{$tag}[^>]*?>.*</{$tag}\s*>\s?#si";
                preg_match_all ( $rule, $html, $_matches );
                $tmpStr = "_" . uniqid ( $tag ) . "_";
                $pattern [] = $rule;
                $replacement [] = $tmpStr;
                $st [$tag] = array (
                    'matches' => $_matches,
                    "pattern" => $rule,
                    "replace" => $tmpStr 
                );
            }
            $html = preg_replace ( $pattern, $replacement, $html );
        }
        $pattern = array (
            "/\n|\r/",
            "/\s+/",
            "/>\s+</",
            "/\s+</",
            "/>\s+/" 
        );
        $replacement = array (
            "",
            " ",
            "><",
            "<",
            ">" 
        );
        $html = preg_replace ( $pattern, $replacement, $html );
        
        if ($allSpec) {
            foreach ( $st as $tag => $info ) {
                if (! $info ['matches'] || ! $info ['matches'] [0]) {
                    continue;
                }
                $p2 = array ();
                $p2 = array_fill ( 0, count ( $info ['matches'] [0] ), "/" . $info ['replace'] . "/" );
                $html = preg_replace ( $p2, $info ['matches'] [0], $html, 1 );
            }
            unset ( $st );
        }
        return $html;
    }
    
    /**
     * 将url地址重新进行url_encode
     * 
     * @param string $url            
     * @param string $charset
     *            将参数进行编码转换
     */
    public static function urlReEncode($url, $charset = null) {
        $url_info = parse_url ( $url );
        if (! isset ( $url_info ['query'] )) {
            return $url;
        }
        
        $defaultCharset = "utf-8";
        parse_str ( $url_info ['query'], $params );
        if ($charset && $charset != $defaultCharset) {
            foreach ( $params as $k => $v ) {
                $params [$k] = mb_convert_encoding ( $v, $charset, $defaultCharset );
            }
        }
        $url = isset ( $url_info ['scheme'] ) ? $url_info ['scheme'] . "://" . $url_info ['host'] : "";
        $url .= $url_info ['path'] . "?" . http_build_query ( $params );
        $url .= isset ( $url_info ['fragment'] ) ? "#" . $url_info ['fragment'] : "";
        return $url;
    }
    
    public static function buttonLink($text, $url, $confirm = null, $params = null) {
        if (! empty ( $confirm )) {
            $confirm = "if(!confirm('" . addslashes ( $confirm ) . "')){return false;}";
        }
        $p = array (
            'onclick' => $confirm . " location.href='" . self::h($url) . "'" 
        );
        if (self::$autoClass) {
            $p ['class'] = 'r-button-link';
        }
        $_param = self::_paramMerge ( $p, $params );
        return self::inputButton ( $text, $_param );
    }
}
