<?php
/**
 * Val标签(管道模式).
 * 
 * @author Eric Tao <https://github.com/whencome>
 */
namespace etview\token;

/**
 * Val标签.
 */
class ValPipeTag
{

    // token列表
    protected $tokens = array();

    /**
     * 初始化.
     */
    public function __construct()
    {
    }

    /**
     * 解析内容.
     */
    public function parse($pipeContent)
    {
        $reader = new TokenReader($pipeContent);
        while (($token = $reader->read())) {
            $this->tokens[] = $token;
        }
    }

    /**
     * 追加一个值.
     */
    public function append($v)
    {
        $this->tokens[] = new Token($v);
    }

    /**
     * 将标签转换为php代码.
     */
    public function format()
    {
        $result = '';
        $appendChars = array();
        $callback = '';
        foreach ($this->tokens as $idx => $token) {
            if ($idx == 0) {
                if ($token->isCallback()) {
                    $callback = $token->getValue();
                    if (function_exists( $callback)) {
                        $result .= $callback.'(';
                    } else {
                        $result = '$'.$callback.'(';
                    }
                    $appendChars[] = ')';
                    continue;
                }
                if ($token->isLiteral()) {
                    $result .= $token->getValue();
                    break;
                }
                if ($token->isVariable()) {
                    $v = $token->getValue();
                    $v = mb_substr($v, 1, mb_strlen($v) - 2);
                    $v = Utils::getVariable($v);
                    $result .= "( isset({$v}) ? {$v} : '')";
                    break;
                }
            }
            // 这里全是callback的参数
            if ($idx > 1) {
                $result .= ', ';
            }
            if ($token->isLiteral()) {
                $result .= $token->getValue();
            }
            if ($token->isVariable()) {
                $v = $token->getValue();
                $v = mb_substr($v, 1, mb_strlen($v) - 2);
                $v = Utils::getVariable($v);
                $result .= "( isset({$v}) ? {$v} : '')";
            }
        }
        $result .= implode('', $appendChars);
        return '<?php echo ' . $result . '; ?>';
    }

}
