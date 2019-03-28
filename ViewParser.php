<?php
/**
 * 视图解析器.
 * 
 * @author Eric Tao <https://github.com/whencome>
 */
namespace etview;

/**
 * 视图解析器.
 */
class ViewParser
{

    // 视图管理工具
    protected $viewManager;

    /**
     * 初始化一个实例.
     */
    public function __construct()
    {
        $this->viewManager = new ViewManager();
    }

    /**
     * 对视图进行解析.
     * 
     * @param string $viewFile     视图文件路径.
     * @param string $templateFile 解析后的缓存模板文件路径.
     * 
     * @return boolean
     */
    public function parse($viewFile, $templateFile)
    {
        if (!file_exists($viewFile)) {
            return false;
        }
        // 开始解析文件
        $viewContent = $this->parseViewFile($viewFile);
        file_put_contents($templateFile, $viewContent);
        return true;
    }

    /**
     * 解析视图文件.
     * 
     * @param string $viewFile 视图文件路径.
     * 
     * @return string 解析后视图内容（即模板内容）.
     */
    protected function parseViewFile($viewFile)
    {
        if (empty($viewFile) || !is_file($viewFile) || !file_exists($viewFile)) {
            return '';
        }
        // 获取视图内容
        $viewContent = file_get_contents($viewFile);
        // 解析布局
        $viewContent = $this->parseLayout($viewContent);
        // 1. 替换包含的文件
        $viewContent = $this->parseInclude($viewContent);
        // 2. 替换简单取值等操作
        $viewContent = $this->parseVariable($viewContent);
        $viewContent = $this->parseVal($viewContent);
        $viewContent = $this->parseError($viewContent);
        $viewContent = $this->parseRange($viewContent);
        $viewContent = $this->parseWhen($viewContent);
        $viewContent = $this->parseEndTag($viewContent);

        return $viewContent;
    }

    /**
     * 解析布局<et:layout view="route" />.
     * 
     * @param string $viewContent 视图内容.
     * 
     * @return string
     */
    protected function parseLayout($viewContent)
    {
        // 1. 获取布局内容(第一行必须定义声明布局)
        $layoutRoute = '';
        if (preg_match('/<et:layout\s+view=\"(.+?)\"\s*\/>/', $viewContent, $matches)) {
            $layoutRoute = $matches[1];
        }
        if (empty($layoutRoute)) {
            return $viewContent;
        }
        // 2. 存在布局则先读取布局内容
        $layoutContent = $this->viewManager->getViewContent($layoutRoute);
        // 3. 对视图中的section进行标准化
        $viewContent = preg_replace_callback(
            '/<et:section\s+name=\"(\w+)\"\s*>/',
            function($matches) {
                return '<et:section name="'.$matches[1].'">';
            },
            $viewContent
        );
        // 4. 将布局中的block使用视图中的section替换
        $layoutContent = preg_replace_callback(
            '/<et:block\s+name=\"(\w+)\"\s*\/>/',
            function($matches) use ($viewContent) {
                $blockName = $matches[1];
                $blockContent = '';
                if (preg_match_all('/(?<=<et:section\sname=\"'.$blockName.'\">)([\s\S]*?)(?=<\/et:section>)/', $viewContent, $sectionMatches)) {
                    $blockContent = $sectionMatches[1][0];
                }
                return $blockContent;
            },
            $layoutContent
        );
        return $layoutContent;
    }

    /**
     * 解析包含<et:include view="route" />.
     * 
     * @param string $viewContent 视图内容.
     * 
     * @return string
     */
    protected function parseInclude($viewContent)
    {
        $viewContent = preg_replace_callback(
            '/<et:include\s+view=\"(.+?)\"\s*\/>/',
            function($matches){
                return $this->viewManager->getViewContent($matches[1]);
            },
            $viewContent
        );
        return $viewContent;
    }

    /**
     * 解析{$var[.field]}.
     *
     * @param string $viewContent 视图内容.
     *
     * @return string
     */
    protected function parseVariable($viewContent)
    {
        $viewContent = preg_replace_callback(
            '/(?<!{)\{\$(\w+(\.\w+){0,})\}(?!})/',
            function($matches){
                $varToken = new token\VariableToken();
                if (!empty($matches[1])) {
                    $varToken->parse($matches[1]);
                }
                return $varToken->format();
            },
            $viewContent
        );
        return $viewContent;
    }

    /**
     * 解析<et:val />.
     * 
     * @param string $viewContent 视图内容.
     * 
     * @return string
     */
    protected function parseVal($viewContent)
    {
        // 直接函数调用支持
        $viewContent = preg_replace_callback(
            '/<et:val\s+(\w+\(.+\))\s*\/>/',
            function($matches){
                $valTag = new token\ValTag();
                $valTag->parse($matches[1]);
                return $valTag->format();
            },
            $viewContent
        );
        // 管道支持
        $viewContent = preg_replace_callback(
            '/<et:val\s+([^|]+?)(\s*\|\s*(.+)?)?\s*\/>/',
            function($matches){
                $valTag = new token\ValPipeTag();
                if (!empty($matches[3])) {
                    $valTag->parse($matches[3]);
                }
                $valTag->append(trim($matches[1]));
                return $valTag->format();
            },
            $viewContent
        );
        return $viewContent;
    }

    /**
     * 解析<et:error />.
     * 
     * @param string $viewContent 视图内容.
     * 
     * @return string
     */
    protected function parseError($viewContent)
    {
        // 简单的错误信息
        $viewContent = preg_replace_callback(
            '/<et:error\s+(.+?)?\s*\/>/',
            function($matches){
                $errTag = new token\ErrorTag();
                if (!empty($matches[1])) {
                    $errTag->parse($matches[1]);
                }
                return $errTag->format();
            },
            $viewContent
        );
        // 其他块错误标签
        $viewContent = preg_replace_callback(
            '/<et:(if|no)error\s*>/',
            function($matches){
                if ($matches[1] == 'no') {
                    return "<?php if ( empty(\$__errors__) ) { ?>\n";
                } else {
                    return "<?php if ( !empty(\$__errors__) ) { ?>\n";
                }
            },
            $viewContent
        );
        // 返回解析结果
        return $viewContent;
    }

    /**
     * 解析条件判断<et:cond>，即if语句, 如: <et:cond not {id} et 100>...</et:cond>.
     */
    protected function parseWhen($viewContent)
    {
        $viewContent = preg_replace_callback(
            '/<et:when\s+(.+?)\s*>/',
            function($matches){
                $whenTag = new token\WhenTag();
                if (!empty($matches[1])) {
                    $whenTag->parse($matches[1]);
                }
                return $whenTag->format();
            },
            $viewContent
        );
        return $viewContent;
    }

    /**
     * 解析循环<et:range>，即foreach语句, 如: <et:range {data} as $row with $key>...</et:range>.
     */
    protected function parseRange($viewContent)
    {
        $viewContent = preg_replace_callback(
            '/<et:range\s+(.+?)?\s*>/',
            function($matches){
                $rangeTag = new token\RangeTag();
                if (!empty($matches[1])) {
                    $rangeTag->parse($matches[1]);
                }
                return $rangeTag->format();
            },
            $viewContent
        );
        return $viewContent;
    }

    /**
     * 解析全部的结束标签.
     *
     * @param $viewContent
     *
     * @return mixed
     */
    protected function parseEndTag($viewContent)
    {
        // else
        $viewContent = preg_replace_callback(
            '/<et:else\s*\/>/',
            function($matches){
                return "<?php } else { ?>\n";
            },
            $viewContent
        );
        // 结束标签
        $viewContent = preg_replace_callback(
            '/<\/et:\w+>/',
            function($matches){
                return "<?php } ?>\n";
            },
            $viewContent
        );
        return $viewContent;
    }

}
