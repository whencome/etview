<?php
/**
 * 页面，包含View以及数据.
 */
namespace etview;

/**
 * 当前页面.
 */
class Page
{

    // 路由配置
    protected $routeMaps = array();

    // 视图
    protected $view;

    protected $defaultViewRoute = 'index.index';
    protected $defaultRoute = '/index';
    protected $defaultAction = 'Index';

    protected $route = '';

    /**
     * 数据提供对象.
     * 
     * @var PageDataProvider
     */
    protected $dataProvider;

    /**
     * 构造一个实例.
     */
    public function __construct()
    {
        $this->dataProvider = new PageDataProvider();
    }

    /**
     * 页面初始化.
     */
    public function init($routeMaps, $routeField = '_r_')
    {
        // 获取路由
        $route = '';
        if (!empty($_GET[$routeField])) {
            $route = trim($_GET[$routeField]);
        }
        if ($route == '/' || empty($route)) {
            $route = $this->defaultRoute;
        }
        $this->route = $route;
        // 路由映射关系
        $this->routeMaps = $routeMaps;
    }

    /**
     * 设置默认ViewRoute.
     */
    public function setDefaultViewRoute($viewRoute)
    {
        $this->defaultViewRoute = $viewRoute;
        return $this;
    }

    /**
     * 设置默认Route.
     */
    public function setDefaultRoute($route)
    {
        $this->defaultRoute = $route;
        return $this;
    }

    /**
     * 设置默认动作.
     */
    public function setDefaultAction($action)
    {
        $this->defaultAction = $action;
        return $this;
    }

    /**
     * 显示页面内容.
     */
    public function display()
    {
        // 检查页面配置，配置不存在或者配置不正确则认为页面不存在
        if (!isset($this->routeMaps[$this->route]) ||
            !is_array($this->routeMaps[$this->route])) {
            ETViewException::raise(ETViewException::ERR_PAGE_NOT_FOUND);
        }
        $pageConfig = $this->routeMaps[$this->route];

        // 获取controller以及action
        $controller = $pageConfig[0];
        $action = $pageConfig[1];
        $viewRoute = isset($pageConfig[2]) ? $pageConfig[2] : $this->defaultViewRoute;

        // 渲染模板
        $this->initView($viewRoute);
        // 获取数据
        $this->initData($controller, $action);
        // 渲染并输出
        $this->render();
    }

    /**
     * 渲染并输出页面内容.
     */
    protected function render()
    {
        if ($this->view->hasView()) {
            // 导入变量
            $pageData = $this->dataProvider->getData();
            // 整合错误信息
            $pageData['__errors__'] = $this->dataProvider->getErrors();
            if (!empty($pageData)) {
                extract($pageData);
            }
            // 导入方法
            $funcMaps = $this->dataProvider->getFuncMaps();
            if (!empty($funcMaps)) {
                extract($funcMaps);
            }
            $templateFile = $this->view->getTemplateFile();
            require_once($templateFile);
        }
    }

    /**
     * 初始化视图.
     */
    protected function initView($viewRoute)
    {
        $this->view = new View($viewRoute);
        $this->view->init();
    }

    /**
     * 初始化数据.
     */
    public function initData($controller, $action)
    {
        $controllerClass = sprintf("\\%s", str_ireplace('.', '\\', $controller));
        if (!class_exists($controllerClass)) {
            ETViewException::raise(ETViewException::ERR_CONTROLLER_NOT_EXISTS);
        }
        $c = new $controllerClass();
        if (!$c instanceof IDataProvider) {
            ETViewException::raise(ETViewException::ERR_CONTROLLER_ILLEGAL);
        }
        $c->setDataProvider($this->dataProvider);
        $c->initPage();
        $method = 'action'.trim($action);
        if (!method_exists($c, $method)) {
            ETViewException::raise(ETViewException::ERR_ACTION_NOT_EXISTS);
        }
        $result = $c->$method();
        if (!empty($result) && is_array($result)) {
            $this->dataProvider->addData($result);
        }
    }

}
