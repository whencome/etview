<?php
/**
 * Token读取对象.
 * 
 * @author Eric Tao <https://github.com/whencome>
 */
namespace etview\token;

/**
 * Token读取对象.
 */
class TokenReader
{

    // 需要解读的原始内容
    protected $rawContent = '';
    // 内容长度
    protected $size = 0;
    // 起始位置
    protected $startPos = 0;
    // 是否解结束
    protected $isFinished = false;
    // 当前读取位置
    protected $curPos = 0;

    /**
     * 初始化一个Reader.
     */
    public function __construct($content)
    {
        $this->rawContent = $content;
        $this->size = mb_strlen($this->rawContent);
    }

    /**
     * 读取并获取一个Token.
     * 
     * @return Token
     */
    public function read()
    {
        if ($this->curPos >= $this->size - 1) {
            return '';
        }

        $startChar = '';
        $endChar = '';
        $recordPos = -1;
        $recordSize = 0;
        $isInQuote = '';
        $startQuote = '';
        $prevChar = '';

        $isLiteral = false;
        while ($this->curPos < $this->size) {
            $char = $this->rawContent{$this->curPos};
            $this->curPos++;
            // 为空，则忽略
            if ($char == ' ') {
                // 如果第一个字符还未记录，表示之前都是空值，跳过继续
                // 如果第一个值不是空，则看是否在引号中，不在引号中则表示读取结束
                if (empty($startChar)) {
                    continue;
                }
                if (!$isInQuote) {
                    $endChar = $prevChar;
                    break;
                }
            }
            $recordSize++;
            // 记录token起始位置
            if ($recordPos == -1) {
                $startChar = $char;
                $recordPos = $this->curPos - 1;
                if ($startChar == '"' || $startChar == "'") {
                    $isInQuote = true;
                    $startQuote = $char;
                    continue;
                }
            }
            
            // 暂时只支持引号，不支持括号等
            if (in_array($char, array('\'', '"'))) {
                if ($prevChar == '\\') {
                    continue;
                }
                if ($char == $startQuote) {
                    $isInQuote = false;
                    $endChar = $char;
                    break;
                }
            }
            $prevChar = $char;
        }
        $val = mb_substr($this->rawContent, $recordPos, $recordSize);
        return new Token($val);
    }

    /**
     * 读取键值对数组(针对简单的标签属性).
     * 
     * @return array
     */
    public function readKVMaps()
    {
        // 保存键值对数组
        $kv = array();
        // 当前游标
        $curPos = 0;
        $startChar = '';
        $field = '';
        while ($curPos < $this->size) {
            // 读取值
            $char = $this->rawContent{$curPos};
            // 为空
            if ($char == ' ') {
                $curPos++;
                continue;
            }
            // 读取字段
            if (empty($field)) {
                $lastPos = mb_stripos($this->rawContent, '=', $curPos + 1);
                if ($lastPos === false) {
                    return;
                }
                $field = mb_substr($this->rawContent, $curPos, $lastPos - $curPos);
                $field = trim($field);
                $curPos = $lastPos + 1;
                continue;
            }
            // 读取值
            if (empty($startChar)) {
                $startChar = $char;
            }
            $lastPos = 0;
            $offset = 0;
            $size = 0;
            // 暂不支持转义
            if ($startChar == '"' || $startChar == "'") {
                $lastPos = mb_stripos($this->rawContent, $startChar, $curPos + 1);
                $offset = $curPos + 1;
                $size = $lastPos - $curPos - 1;
            } else {
                $lastPos = mb_stripos($this->rawContent, ' ', $curPos);
                if ($lastPos === false) {
                    $lastPos = mb_strlen($this->rawContent);
                }
                $offset = $curPos;
                $size = $lastPos - $curPos;
            }
            if ($lastPos === false) {
                return;
            }
            $val = mb_substr($this->rawContent, $offset, $size);
            $kv[$field] = $val;
            $curPos = $lastPos + 1;
            $field = '';
            $startChar = '';
        }
        return $kv;
    }

}