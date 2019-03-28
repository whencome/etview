<?php
/**
 * Created by PhpStorm.
 * User: eric
 * Date: 2019/3/7
 * Time: 23:25
 */

namespace etview\token;


class WhenTag
{
    protected $content = '';

    protected $opMaps = array(
        'not' => '!',
        'and' => '&&',
        'or' => '||',
        'eq' => '==',
        'neq' => '!=',
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<='
    );

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
        // if语句直接进行替换即可
        $content = $this->content;
        // 1. 替换变量
        $content = preg_replace_callback(
            '/\{([a-zA-Z0-9_.]+)\}/',
            function($matches){
                return Utils::getVariable($matches{1});
            },
            $content
        );
        // 2. 替换操作符
        $me = $this;
        $content = preg_replace_callback(
            '/^(not)\s+|\s+(not|eq|neq|gt|gte|lt|lte|or|and)\s+/',
            function($matches) use ($me){
                $op = trim($matches[0]);
                return isset($me->opMaps[$op]) ? ' '.$me->opMaps[$op].' ' : '';
            },
            $content
        );
        // 3. 格式化成php语句
        $code = "<?php\n";
        $code .= "\tif ( {$content} ) {\n";
        $code .= "?>\n";
        return $code;
    }
}
