# etview

etview是一个简单易用、轻量化的MVC框架。它通过自定义标签完成了常规需要繁琐代码才能展示的数据以及逻辑控制。etview具有如下特点：

1. 轻量化 ： 整个框架代码不压缩也才几十KB；
2. 简单： 我们只提供了一些常用且必须的标签和功能，其他比如数据库操作等，开发者可以根据自己需要选择不同的组件然后集成；
3. 支持布局文件： 使用布局的好处是不言而喻，而使用布局也只需要一行代码，开发者在引入布局后，在view层面只需要关心需要修改的部分即可；
4. 入手方便： etview应该来说几乎不要学习成本，几分钟即可完全掌握。

## 内容介绍

* 使用框架
* 配置
* 创建入口
* 控制器
* 展示数据
* 布局

## 使用框架

代码地址： 

下载好代码后，将etview目录放入项目的vendor目录下（或者其他目录），包含自动加载文件，使得可以直接通过 **\etview** 命名空间直接访问etview相关的功能代码。

## 配置

当前的版本是默认将配置放在 **\config\ETView** 中，配置示例可见 **\etview\config\ETView.php** 。 配置内容如下：
```php
<?php
namespace config;

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
```

## 创建入口

我们按搭建一个站点的顺序进一步了解etview。

事实上etview已经是一个完整的站点框架，我们设置好入口就可以正式使用了。etview的核心入口就是Page，用户的每个访问都是访问的一个Page。但是etview并没有使用固定的模式限定访问一个Page使用哪个控制器、哪个视图，这些都需要开发者自行配置。

也就是说，用户访问/a，向页面提供数据的可能是A，也可能是B，可以有视图，也可以没有视图，这些都是开发者自行配置的，etview并不关心，开发者只需要把必要信息告知etview即可。

这里的配置即RouteMaps，route maps的配置格式如下：
$routeMaps = array(
    'REQUEST_URI' => array(
        'controller',    // 用于指明当前需要访问哪个控制器，以“.”作为路径分隔符
        'action',        // Action用于生成数据，是控制器中的"actionXXX"方法，此处配置需要填“XXX”，不需要"action"前缀
        'view'           // 页面展示的视图，没有则置空，如果有，则以“.”号作为路劲分隔符
    ),
);

我们来看一个简单的示例：
```php
$routeMaps = array(
    '/home' => array('controller.Index', 'Index', 'index.index'),
    '/article' => array('controller.Index', 'View', 'index.view'),
    '/cate' => array('controller.Index', 'Category', 'index.index'),
    '/api' => array('controller.Index', 'Api'),   // 没有view
);
```

route maps配置好之后，我们再使用几行代码就可以了。看下面的例子：
```php
// 第一步，我们需要创建一个Page对象，这个整个站点的入口
$page = new \etview\Page();
// 第二步，我们需要设置默认的route，即没有任何匹配route的情况下我们指向哪个页面
$page->setDefaultRoute('/home');
// 第三步，设置route maps，其中第二个参数告诉etview通过哪个请求参数获取route信息
$page->init($routeMaps, '_route_');
// 第四步，展示页面
$page->display();
```

至此，一个完整的入口就搭建好了，下面我们就开始准备数据以及展示数据。

## 控制器

### controller简单入门

etview对控制器有一点强制性的要求：

1. 继承 \etview\ControllerBase 类；
2. action是以“action”开头的无参方法，如：“actionIndex”；

至于返回值，其实并不特别要求。那么，如何将数据反馈到页面呢？有两种方式：

1. 返回一个数组，其中键用于标识一个数据；
2. 在action中直接调用 *$this->addFieldData($key, $value); * 方法向页面添加数据。

一个简单的示例：
```php
<?php
namespace controller;

class Index extends ControllerBase
{

    public function actionIndex()
    {
        // 直接向页面添加一个数据
        $this->addFieldData('title', 'This is a title');
        // 返回需要向页面添加的内容
        return array(
            "content" => "Hello, World!"
        );
    }

}
```

通常，我们既然使用了模板，意味着我们在页面数据上也会有一部分始终是通用的，如果在每个action中都取一次，就算我们封装了方法，也会很麻烦。这个时候我们只需要在controller中实现一个initPage()方法即可。如：
```php
<?php
namespace controller;

class Index extends ControllerBase
{

    public function initPage()
    {
        // 每个使用该controller的页面都会包含一个title数据
        $this->addFieldData('title', 'This is a title');
    }

    public function actionIndex()
    {
        // 返回需要向页面添加的内容
        return array(
            "content" => "Hello, World!",
            'a' => array(
                'b' => 'c'
            )
        );
    }

}
```

### controller提供的方法

controller提供了几个基本方法，用于向页面中添加数据、错误信息以及注册自定义函数，下面简单列出了这些方法的定义。我们将在数据展示一部分介绍如果将添加的数据在页面中展示。

```php
    /**
     * 添加批量数据.
     *
     * @param array $data
     */
    public function addData(array $data)

    /**
     * 添加指定字段名的数据.
     *
     * @param string $field
     * @param mixed  $data
     */
    public function addFieldData($field, $data)

    /**
     * 添加错误.
     *
     * @param string $error 错误信息.
     */
    public function addError($error)

    /**
     * 添加错误到指定字段.
     *
     * @param string $field
     * @param string $error
     */
    public function addFieldError($field, $error)

    /**
     * 注册一个方法.
     *
     * @param string   $funcName 方法名称.
     * @param \Closure $callback 方法.
     *
     * @return void
     */
    public function registerFunction($funcName, $callback)
```

控制器就是这么简单，下面我们看下如何展示数据。

## 展示数据

展示数据分几种情况，简单来说就是：

1. 简单变量
2. 按条件展示
3. 批量数据处理
4. 错误展示

下面详细说明下以上各种情况的数据展示。

### 简单变量

如果我们需要简单展示一个变量的值，我们有两种方式：

1. &lt;et:val /&gt; 通过标签展示数据

    如果我们需要在页面中展示一个数据，我们就使用标签的形式展示，这样可以让整个html模板内容风格更加接近。etview定义了 **&lt;et:val /&gt;** 标签用于基本数据展示，比如我们在之前controller中添加了title以及content两项内容的数据，那么我们可以使用下面的方式来输出对应的内容：
 ```html
<et:val {title} />
<et:val {content} />
```

   如果我们链家的数据是数组，则可以使用下面的方式来输出数组中的一个值：
```html
<et:val {a.b} />
```

&lt;et:val /&gt;一些简单的示例如下：
```html
<et:val {student.score} | sprintf "%.2f" />
<et:val 'hello, world' | strtoupper />
<et:val 1553256879 | date "Y-m-d H:i:s" />
```

   如果，我们相对内容进行一些处理怎么办？ **&lt;et:val&gt;** 标签还支持管道以及函数调用，如：
```html
<!-- 下面两种方式都是将输出的内容转换为大写，效果一样 -->
<et:val {title} | strtoupper/>
<et:val strtoupper({title}) />
```

2. 通过{$var}展示数据

当然，如果我们在html标签中想要输出一个数据，比如在&lt;input /&gt;中我们需要将对应的值放到value属性中，我们在包含一个标签就显得不那么优雅了，这个时候我们就需要使用{$var}来输出变量的值，注意，这与通过标签输出中包含的{var}有点区别，如下：

```html
<!--输出简单变量的值-->
<input value="{$title}" />
<!--输出多维数组中的值-->
<input value="{$a.b}" />
```

*注意： {$var} 不支持管道以及函数。*

### &lt;et:when&gt; 按条件展示

通常，我们需要在某些条件下才展示一些内容，这个时候可以使用&lt;et:when&gt;。此标签对应于常见的if条件判断语句，只是在语法上略有不同。
```html
<et:when ({idx} gt 103) or ({category.name} eq '其它') or empty({category.name})>
show your content here
</et:when>
```

&lt;et:when&gt; 支持的运算符如下：
```php
$opMaps = array(
        'not' => '!',
        'and' => '&&',
        'or' => '||',
        'eq' => '==',
        'neq' => '!=',
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<='
    );
```

一般说道if，就不得不提else，但是html标签没有三标签，虽然etview中也支持&lt;else /&gt;这样的标签，但是在格式化的时候或者实际书写的时候，总还是显得不够优雅，因此这里不做过多篇幅介绍。

### &lt;et:range&gt; 批量数据处理

&lt;et:range&gt; 对应代码中的foreach循环。语法如下：
```html
<et:range {articles} as $aid, $article>
    <et:when {article.is_pubed} neq '0'>
        已发布
    <et:else />
        未发布
    </et:when>
</et:range>
```

### 错误展示

错误展示分几种情况：
1. 展示全部错误： &lt;et:error /&gt;
2. 只展示特定的错误，比如用户登录时验证码错误：&lt;et:error field="code" /&gt;
3. 只在有错误的时候展示：&lt;et:iferror&gt; 在此处展示你的内容 &lt;/et:iferror&gt;
4. 只在没有有错误的时候展示：&lt;et:noerror&gt; 在此处展示你的内容 &lt;/et:noerror&gt;
5. 从本质上来说，iferror和noerror都是if语句，因此都支持&lt;else /&gt;标签。

## 布局

布局涉及到下面的标签：

1. 包含一个视图文件，view属性指定视图的路径（基于配置的viewDir）
```html
<et:include view="index.index" />
```

2. 定义一个块，使用name属性进行标识，block标签只是一个占位符
```html
<et:block name="content" />
```

3. 指定布局模板，其中view属性指定的就是在viewDir下面的视图文件，该文件是一个模板文件
```html
<et:layout view="layout" />
```

4. 针对定义的block使用section进行填充
```html
<et:section name="content">
在此区域填充你的内容
</et:section>
```

## 实战示例

在简单介绍了etview的基本内容后，我们简单做一个示例。我们假设相关的库文件放置在项目的vendor目录下。

1. 站点配置：

**引入自动加载**

略

**route参数**

不管使用url重写也好，还是直接将参数防止在地址中也好，只要能够获取到route信息就可以了，因此这里不做过多介绍，因为这不是我们关注的重点。

**配置内容**

在\config目录放入etview的配置（示例参考\etview\config\ETView.php）。配置示例如下：

```php
<?php
namespace config;

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
```

2. view文件

我们用最简单的方式实现一个视图模板，在viewDir下面防止如下三个文件：

header.html --- 通用的头部文件
layout.html --- 布局文件
index.html  --- 视图内容

文件内容分别如下：
FILE: header.html
```html
<head>
    <title><et:val {title} /></title>
</head>
```

FILE: layout.html
```html
<!DOCTYPE HTML>
<html>
    <et:include view="header" />
    <body>
        <div id="main">
            <et:block name="main" />
        </div>
    </body>
</html>
```

FILE: index.html
```html
<et:layout view="layout" />

<et:section name="main">
    <div>
        <et:val {content} />
    </div>
</et:section>
```

3. 实现controller
```php
<?php
namespace controller;

class Index extends ControllerBase
{

    public function initPage()
    {
        $this->addFieldData('title', 'This is a title');
    }

    public function actionIndex()
    {
        // 返回需要向页面添加的内容
        return array(
            "content" => "Hello, World!"
        );
    }

}
```

4. 实现入口（单一入口）
```php
<?php
// 自动加载文件
require_once(__DIR__.'/vendor/bootstrap/AutoLoader.php');
// 注册自动加载
\bootstrap\Autoloader::instance()->init();

$routeMaps = array(
    '/home' => array('controller.Index', 'Index', 'index'),
);

$page = new \etview\Page();
$page->setDefaultRoute('/home');
$page->init($routeMaps, '_route_');
$page->display();
```

