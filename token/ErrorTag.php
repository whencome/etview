<?php
/**
 * 错误标签.
 * 
 * @author Eric Tao <https://github.com/whencome>
 */
namespace etview\token;

/**
 * 错误标签.
 */
class ErrorTag
{

    // 定义属性列表
    protected $props = array();

    /**
     * 解析内容.
     */
    public function parse($pipeContent)
    {
        $reader = new TokenReader($pipeContent);
        $this->props = $reader->readKVMaps();
    }

    /**
     * 将标签转换为php代码.
     */
    public function format()
    {
        $field = isset($this->props['field']) ? $this->props['field'] : '';
        $code = "<?php\n";
        if (!empty($field)) {
            $code .= "\tif ( !empty(\$__errors__['{$field}']) ) {\n";
            $code .= "\t\techo '<p class=\"et_error\"><label>', \$__errors__['{$field}'],' </label></p>'; \n";
            $code .= "\t}\n";
        } else {
            $code .= "\tif ( !empty(\$__errors__) ) {\n";
            $code .= "\t\techo '<ul class=\"et_errors\">';\n";
            $code .= "\t\tforeach ( \$__errors__ as \$k => \$err ) {\n";
            $code .= "\t\t\tif ( !is_numeric(\$k) ) {\n";
            $code .= "\t\t\t\tcontinue;\n";
            $code .= "\t\t\t}\n";
            $code .= "\t\t\techo '<li class=\"et_error\">', \$err,'</li>'; \n";
            $code .= "\t\t}\n";
            $code .= "\t\techo '</ul>';\n";
            $code .= "\t}\n";
        }
        $code .= "?>\n";
        return $code;
    }

}
