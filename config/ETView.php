<?php
/**
 * ETView模板配置文件内容.
 * 
 * @author Eric Tao<twqzy323@163.com>
 */
namespace config;

/**
 * ETView模板配置文件内容.
 */
class ETView
{

    // 设置模板文件根目录
    public $viewDir = '/home/www/view';

    // 设置缓存目录,用于存放缓存的文件
    public $cacheDir = '/home/www/cache';

    // 视图文件扩展名
    public $viewExtension = '.html';

    // 是否允许缓存（如果设置为false，每次都需要重新编译生成模板）
    public $enableCache = false;

}
