<?php
/**
 * 视图管理对象.
 * 
 * @author Eric Tao <https://github.com/whencome>
 */
namespace etview;

/**
 * 视图管理对象.
 */
class ViewManager
{

    // 配置管理器
    protected $configManager;

    /**
     * 初始化.
     */
    public function __construct()
    {
        $this->configManager = ConfigManager::init();
    }

    /**
     * 获取视图内容.
     * 
     * @param string 视图内容.
     */
    public function getViewContent($viewRoute)
    {
        $viewFile = $this->configManager->getViewFile($viewRoute, false);
        if (empty($viewFile) || !is_file($viewFile) || !file_exists($viewFile)) {
            return '';
        }
        return file_get_contents($viewFile);
    }

}