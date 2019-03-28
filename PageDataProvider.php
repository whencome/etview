<?php
/**
 * 页面数据提供者，专门用于向页面提供数据.
 * 
 * @author Eric Tao <https://github.com/whencome>
 */
namespace etview;

/**
 * 页面数据提供者.
 */
class PageDataProvider
{

    // 页面正常响应数据
    protected $data = array();

    // 请求页面的错误信息
    protected $errors = array();

    // 用于保存自定义函数列表
    protected $funcMaps = array();

    /**
     * 添加一批数据.
     * 
     * @param array $data 要添加的数据.
     * 
     * @return void
     */
    public function addData(array $data)
    {
        if (empty($data) || !is_array($data)) {
            return;
        }
        $this->data = array_merge($this->data, $data);
    }

    /**
     * 添加单个数据.
     * 
     * @param string $field 数据对应的key.
     * @param mixed  $value 值.
     * 
     * @return void
     */
    public function setFieldData($field, $value)
    {
        $this->data[$field] = $value;
    }

    /**
     * 获取全部数据.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 批量添加错误信息.
     * 
     * @param array $errors 错误信息列表.
     * 
     * @return void
     */
    public function addErrors(array $errors)
    {
        if (empty($errors)) {
            return;
        }
        $this->errors = array_merge($this->errors, $errors);
    }

    /**
     * 添加一条错误信息.
     *
     * @param string $error 错误信息.
     *
     * @return void
     */
    public function addError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * 设置单个错误信息.
     * 
     * @param string $field Key.
     * @param string $error 错误信息.
     * 
     * @return void
     */
    public function setFieldError($field, $error)
    {
        $this->errors[$field] = $error;
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
        $this->funcMaps[$funcName] = \Closure::bind($callback, null);
    }

    /**
     * 从指定的源中获取一个值.
     * 
     * @param string $key     指定的key.
     * @param array  $src     数据源.
     * @param mixed  $default 默认值.
     * 
     * @return mixed
     */
    protected function getValueFromSource($key, array $src, $default = '')
    {
        $key = trim($key);
        // 简单的值（一维数组）
        if (stripos($key, '.') === false) {
            if (!isset($src[$key])) {
                return '';
            }
            return $src[$key];
        }
        // 多维数组
        $val = $src;
        $fields = explode('.', $key);
        foreach ($fields as $depth => $field) {
            if (isset($val[$field])) {
                $val = $val[$field];
            }
        }
        return $val;
    }

    /**
     * 获取指定key对应的值.
     * 
     * @param string $key 对应的键.
     * 
     * @return string
     */
    public function getVal($key)
    {
        if (empty($key)) {
            return '';
        }
        /*
        $keySize = mb_strlen($key);
        $lastPos = $keySize - 1;
        // 如果不是合法的变量格式，直接原样返回
        if ($key{0} != '{' || $key{$lastPos} != '}') {
            return $key;
        }
        $key = mb_substr($key, 1, $keySize - 2);
        */
        $val = $this->getValueFromSource($key, $this->data, '');
        if (is_array($val)) {
            $val = json_encode($val);
        }
        return $val;
    }

    /**
     * 获取指定key对应的错误信息.
     * 
     * @param string $key 对应的键.
     * 
     * @return string
     */
    public function getError($key)
    {
        $val = $this->getValueFromSource($key, $this->errors, '');
        // $val =  isset($this->errors[$key]) ? $this->errors[$key] : '';
        if (is_array($val)) {
            $val = implode('; ', $val);
        }
        return $val;
    }

    /**
     * 获取错误数量.
     * 
     * @return integer
     */
    public function getErrorCount()
    {
        return count($this->errors);
    }

    /**
     * 获取全部错误列表.
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * 获取自定义方法.
     *
     * @param string $funcName 方法名称.
     *
     * @return \Closure
     */
    public function getFuncs($funcName)
    {
        if (isset($this->funcMaps[$funcName])) {
            return $this->funcMaps[$funcName];
        }
        // 返回一个空方法，防止报错
        return function(){};
    }

    public function getFuncMaps()
    {
        return $this->funcMaps;
    }

}
