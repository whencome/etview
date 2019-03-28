<?php
/**
 * 视图.
 * 
 * @author Eric Tao <https://github.com/whencome>
 */
namespace etview;

/**
 * 视图
 */
class View
{

    // 配置管理工具
    protected $configManager;
    protected $viewRoute = '';
    // 视图文件地址
    protected $viewFile = '';
    // 缓存目录
    protected $cacheDir = '';
    // 解析后的模板文件
    protected $templateFile = '';
    // 标识是否需要重新解析文件
    protected $needParse = false;

    /**
     * 初始化视图.
     */
    public function __construct($viewRoute)
    {
        $this->viewRoute = $viewRoute;
        $this->configManager = ConfigManager::init();
        $this->viewFile = $this->configManager->getViewFile($viewRoute);
        $this->templateFile = $this->configManager->getCacheTemplateFile($viewRoute);
    }

    /**
     * 判断是否有视图文件.
     * 
     * @return bool
     */
    public function hasView()
    {
        if ($this->viewFile &&
            is_file($this->viewFile) &&
            file_exists($this->viewFile)) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否需要重新解析视图内容.
     * 
     * @return boolean
     */
    public function needParse()
    {
        if (!$this->configManager->isCacheEnabled()) {
            return true;
        }
        if (!file_exists($this->templateFile) || 
                (file_exists($this->templateFile) && filemtime($this->viewFile) >= filemtime($this->templateFile))) {
            return true;
        }
        return false;
    }

    /**
     * 初始化视图文件.
     */
    public function init()
    {
        if (!$this->hasView() || !$this->needParse()) {
            return false;
        }
        $viewParser = new ViewParser();
        $viewParser->parse($this->viewFile, $this->templateFile);
    }

    /**
     * 获取需要包含的视图文件.
     * 
     * @return string 视图文件地址.
     */
    public function getViewFile()
    {
        return $this->viewFile;
    }

    /**
     * 获取模板文件.
     * 
     * @return string 经过解析的模板文件.
     */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }

}