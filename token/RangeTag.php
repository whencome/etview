<?php
/**
 * Created by PhpStorm.
 * User: eric
 * Date: 2019/3/7
 * Time: 23:29
 */

namespace etview\token;


class RangeTag
{

    protected $content = '';

    protected $src = '';
    protected $key = '';
    protected $val = '';

    /**
     * 解析内容.
     */
    public function parse($content)
    {
        $this->content = trim($content);
        if (preg_match('/^\{([a-zA-Z0-9_.]+)}\s+as\s+((\$\w+),)?\s*(\$\w+)$/', $this->content, $matches)) {
            $this->src = Utils::getVariable($matches[1]);
            if (!empty($matches[3])) {
                $this->key = $matches[3];
            }
            $this->val = $matches[4];
        }
    }

    /**
     * 将标签转换为php代码.
     */
    public function format()
    {
        $code = "<?php\n";
        $code .= "\tforeach ({$this->src} as ".(!empty($this->key) ? $this->key.' => ' : '' )." {$this->val}) {\n";
        $code .= "?>\n";
        return $code;
    }

}
