<?php
/**
 * 配置管理对象.
 * 
 * @author Eric Tao <https://github.com/whencome>
 */
namespace etview;

/**
 * 配置管理对象.
 */
class ConfigManager
{

    // 保存静态实例
    protected static $instance = null;

    // 存放配置内容
    protected $config = array();

    /**
     * 初始化，单例入口.
     */
    public static function init()
    {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * 初始化一个实例.
     */
    public function __construct()
    {
        // 检查配置
        if (!class_exists('\config\ETView')) {
            ETViewException::raise(ETViewException::ERR_CONFIG_NOT_EXISTS);
        }
        $this->config = (array)new \config\ETView();
    }

    /**
     * 检查配置，判断是否启用缓存，一般开发环境不启用.
     * 
     * @return boolean
     */
    public function isCacheEnabled()
    {
        if (isset($this->config['enableCache']) && !$this->config['enableCache']) {
            return false;
        }
        return true;
    }

    /**
     * 获取缓存存放目录.
     * 
     * @return string
     */
    public function getCachePath()
    {
        if (!isset($this->config['cacheDir']) || empty($this->config['cacheDir'])) {
            ETViewException::raise(ETViewException::ERR_CACHE_DIR_NOT_SET);
        }
        return $this->config['cacheDir'];
    }

    /**
     * 获取视图目录.
     * 
     * @return string
     */
    public function getViewPath()
    {
        if (!isset($this->config['viewDir']) || empty($this->config['viewDir'])) {
            ETViewException::raise(ETViewException::ERR_VIEW_DIR_NOT_SET);
        }
        return $this->config['viewDir'];
    }

    /**
     * 获取视图扩展名，默认".html"，可以自行设置.
     * 
     * @return string
     */
    public function getViewExtension()
    {
        return !empty($this->config['viewExtension']) ? $this->config['viewExtension'] : '.html';
    }

    /**
     * 获取视图文件路径.
     * 
     * @param string  $viewRoute  View Route.
     * @param boolean $mustExists 是否要求视图必须存在，如果是，则视图不存在时将抛出异常.
     * 
     * @return string
     * @throws ETViewException
     */
    public function getViewFile($viewRoute, $mustExists = false)
    {
        $viewPath = $this->getViewPath();
        $viewExtension = $this->getViewExtension();
        $viewFile =  realpath($viewPath . DIRECTORY_SEPARATOR. str_ireplace('.', DIRECTORY_SEPARATOR, $viewRoute) . $viewExtension);
        if (!file_exists($viewFile)) {
            if ($mustExists) {
                ETViewException::raise(ETViewException::ERR_VIEW_NOT_FOUND);
            }
            return '';
        }
        return $viewFile;
    }

    /**
     * 获取缓存模板文件路径.
     * 
     * @param string $viewRoute View Route.
     * 
     * @return string
     * @throws ETViewException
     */
    public function getCacheTemplateFile($viewRoute)
    {
        $cachePath = $this->getCachePath();
        $templatePath = sprintf("%s/%s", $cachePath, 'template');
        if (!file_exists($templatePath)) {
            $rs = mkdir($templatePath, 0644, true);
            if (!$rs) {
                ETViewException::raiseError('could not make dir ['.$templatePath.'], please check your permission and disk left space', ETViewException::ERR_MKDIR_FAILED);
            }
        }
        $viewFile = $this->getViewFile($viewRoute);
        $templateFile = sprintf("%s/%s.php", $templatePath, md5($viewRoute));
        return $templateFile;
    }

}
