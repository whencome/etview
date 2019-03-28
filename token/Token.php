<?php
/**
 * 普通Token对象.
 * 
 * @author Eric Tao <https://github.com/whencome>
 */
namespace etview\token;

/**
 * 普通Token对象.
 */
class Token
{
    // 字面量
    protected $value = '';
    // 是否是字面量（引号包含或者是纯数字）
    protected $isLiteral = false;
    // 是否是变量
    protected $isVariable = false;
    // 是否是一个方法
    protected $isCallback = false;

    /**
     * 初始化一个token.
     */
    public function __construct($val)
    {
        $val = trim($val);
        $this->value = $val;

        $startChar = $val{0};
        $lastPos = mb_strlen($val) - 1;
        $endChar = $val{$lastPos};
        $isLiteral = false;
        $isCallback = false;
        $isVariable = false;
        if ($endChar == $startChar && ($startChar == "'" || $startChar == '"') || 
            is_numeric($val) || 
            in_array($val, array('true', 'false'))) {
            $isLiteral = true;
        }
        if ($startChar == '{' && $endChar == '}') {
            $isVariable = true;
        }
        if (!$isLiteral && !$isVariable) {
            $isCallback = true;
        }

        $this->isLiteral = $isLiteral;
        $this->isVariable = $isVariable;
        $this->isCallback = $isCallback;
    }

    /**
     * 判断是否是字面量.
     * 
     * @return boolean
     */
    public function isLiteral()
    {
        return $this->isLiteral;
    }

    /**
     * 是否是变量.
     * 
     * @return boolean
     */
    public function isVariable()
    {
        return $this->isVariable;
    }

    /**
     * 是否是回调函数.
     * 
     * @return boolean
     */
    public function isCallback()
    {
        return $this->isCallback;
    }

    /**
     * 获取字面量值.
     * 
     * @return boolean
     */
    public function getValue()
    {
        return $this->value;
    }

}