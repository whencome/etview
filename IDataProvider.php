<?php
/**
 * 定义数据提供接口，接入方需要实现此接口用于接受并设置数据提供对象，以便于向页面添加数据.
 * 
 * @author Eric Tao <https://github.com/whencome>
 */
namespace etview;

/**
 * 数据提供接口.
 */
interface IDataProvider
{

    /**
     * 设置数据提供对象.
     */
    function setDataProvider(PageDataProvider $provider);

    /**
     * 对页面进行一些初始化.
     */
    function initPage();

}