<?php
/**
 * Val标签.
 * 
 * @author Eric Tao <https://github.com/whencome>
 */
namespace etview\token;

/**
 * Val标签.
 */
class ValTag
{

    protected $content = array();

    /**
     * 初始化.
     */
    public function __construct()
    {
    }

    /**
     * 解析内容.
     */
    public function parse($content)
    {
        $this->content = trim($content);
    }

    /**
     * 将标签转换为php代码.
     */
    public function format()
    {
        // 直接进行替换即可
        $content = $this->content;
        // 方法名处理
        $content = preg_replace_callback(
            '/^(\w+)\(/',
            function($matches){
                $method = $matches[1];
                if (function_exists($method)) {
                    return $method.'(';
                }
                return '$'.$method.'(';
            },
            $content
        );
        // 变量处理
        $content = preg_replace_callback(
            '/\{([a-zA-Z0-9_.]+)\}/',
            function($matches){
                return Utils::getVariable($matches{1});
            },
            $content
        );
        return '<?php echo ' . $content  . '; ?>';
    }

}
