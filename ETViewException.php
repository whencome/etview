<?php
/**
 * ETView异常.
 */
namespace etview;

/**
 * ETView异常.
 */
class ETViewException extends \Exception
{

    // 定义错误码
    const ERR_VIEW_FILE_NOT_EXITSTS         = 0xfa0001;
    const ERR_VIEW_FILE_INVALID             = 0xfa0002;
    const ERR_CONFIG_NOT_EXISTS             = 0xfa0003;
    const ERR_PAGE_NOT_FOUND                = 0xfa0004;
    const ERR_VIEW_NOT_FOUND                = 0xfa0005;
    const ERR_CONTROLLER_NOT_EXISTS         = 0xfa0006;
    const ERR_ACTION_NOT_EXISTS             = 0xfa0007;
    const ERR_VIEW_DIR_NOT_SET              = 0xfa0008;
    const ERR_CACHE_DIR_NOT_SET             = 0xfa0009;
    const ERR_MKDIR_FAILED                  = 0xfa0010;
    const ERR_CONTROLLER_ILLEGAL            = 0xfa0011;
    const ERR_ILLEGAL_TAG                   = 0xfa0012;

    // 定义错误列表
    public static $errors = array(
        self::ERR_VIEW_FILE_NOT_EXITSTS     => 'view file not exists',
        self::ERR_VIEW_FILE_INVALID         => 'view file invalid: view file should be a plain text file',
        self::ERR_CONFIG_NOT_EXISTS         => 'etview config missing',
        self::ERR_PAGE_NOT_FOUND            => 'page not found',
        self::ERR_VIEW_NOT_FOUND            => 'view file missing',
        self::ERR_CONTROLLER_NOT_EXISTS     => 'controller not exists',
        self::ERR_ACTION_NOT_EXISTS         => 'action not exists',
        self::ERR_VIEW_DIR_NOT_SET          => 'view dir not set',
        self::ERR_CACHE_DIR_NOT_SET         => 'cache dir not set',
        self::ERR_MKDIR_FAILED              => 'make dir failed, please check your permission and disk left space',
        self::ERR_CONTROLLER_ILLEGAL        => 'controller illegal, controller must implement interface etview\IDataProvider',
        self::ERR_ILLEGAL_TAG               => 'illegal tag'
    );

    /**
     * 抛出异常.
     */
    public static function raise($code)
    {
        $error = '';
        if (isset(self::$errors[$code])) {
            $error = self::$errors[$code];
        }
        self::raiseError($error, $code);
    }

    /**
     * 抛出异常.
     */
    public static function raiseError($error, $code)
    {
        throw new static($error, $code);
    }

}