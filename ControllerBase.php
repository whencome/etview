<?php
/**
 * 控制器基类.
 *
 * @author Eric Tao <https://github.com/whencome>
 */
namespace etview;

/**
 * 控制器抽象基类.
 */
abstract class ControllerBase implements IDataProvider
{
    /**
     * 数据提供对象.
     *
     * @var \etview\PageDataProvider
     */
    protected $dataProvider;

    /**
     * 设置数据提供对象.
     */
    public function setDataProvider(\etview\PageDataProvider $provider)
    {
        $this->dataProvider = $provider;
    }

    /**
     * 添加批量数据.
     *
     * @param array $data
     */
    public function addData(array $data)
    {
        if (!empty($this->dataProvider)) {
            $this->dataProvider->addData($data);
        }
    }

    /**
     * 添加指定字段名的数据.
     *
     * @param string $field
     * @param mixed  $data
     */
    public function addFieldData($field, $data)
    {
        if (!empty($this->dataProvider)) {
            $this->dataProvider->setFieldData($field, $data);
        }
    }

    /**
     * 添加错误.
     *
     * @param string $error 错误信息.
     */
    public function addError($error)
    {
        if (!empty($this->dataProvider)) {
            $this->dataProvider->addError($error);
        }
    }

    /**
     * 添加错误到指定字段.
     *
     * @param string $field
     * @param string $error
     */
    public function addFieldError($field, $error)
    {
        if (!empty($this->dataProvider)) {
            $this->dataProvider->setFieldError($field, $error);
        }
    }

    /**
     * 注册一个方法.
     *
     * @param string   $funcName 方法名称.
     * @param \Closure $callback 方法.
     *
     * @return void
     */
    public function registerFunction($funcName, $callback)
    {
        if (!empty($this->dataProvider)) {
            $this->dataProvider->registerFunction($funcName, $callback);
        }
    }

}
