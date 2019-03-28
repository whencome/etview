<?php
/**
 * {$var}变量标识解析.
 *
 * @author Eric Tao <https://github.com/whencome>
 */
namespace etview\token;

/**
 * Class VariableToken
 * @package etview\token
 */
class VariableToken
{

    protected $value = '';

    /**
     * 开始解析.
     *
     * @param string $val 变量值.
     *
     * @return void
     */
    public function parse($val)
    {
        $this->value = $val;
    }

    /**
     * 格式化成php code.
     *
     * @return string
     */
    public function format()
    {
        if (empty($this->value)) {
            return "";
        }
        $parts = explode('.', $this->value);
        $var = '$'.$parts[0];
        unset($parts[0]);
        if (count($parts) > 0) {
            $var .= "['".implode("']['", $parts)."']";
        }
        $code = "<?php echo isset({$var}) ? {$var} : ''; ?>";
        return $code;
    }

}
