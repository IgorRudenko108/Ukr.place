<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdbdee6ba53adb2c2ed62cb0a2d69c26c
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'Parsedown' => 
            array (
                0 => __DIR__ . '/..' . '/erusev/parsedown',
            ),
        ),
    );

    public static $classMap = array (
        'Access' => __DIR__ . '/..' . '/xfra35/f3-access/lib/access.php',
        'Audit' => __DIR__ . '/..' . '/bcosca/fatfree-core/audit.php',
        'Auth' => __DIR__ . '/..' . '/bcosca/fatfree-core/auth.php',
        'Base' => __DIR__ . '/..' . '/bcosca/fatfree-core/base.php',
        'Basket' => __DIR__ . '/..' . '/bcosca/fatfree-core/basket.php',
        'Bcrypt' => __DIR__ . '/..' . '/bcosca/fatfree-core/bcrypt.php',
        'CLI\\Agent' => __DIR__ . '/..' . '/bcosca/fatfree-core/cli/ws.php',
        'CLI\\WS' => __DIR__ . '/..' . '/bcosca/fatfree-core/cli/ws.php',
        'Cache' => __DIR__ . '/..' . '/bcosca/fatfree-core/base.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'DB\\Cursor' => __DIR__ . '/..' . '/bcosca/fatfree-core/db/cursor.php',
        'DB\\Jig' => __DIR__ . '/..' . '/bcosca/fatfree-core/db/jig.php',
        'DB\\Jig\\Mapper' => __DIR__ . '/..' . '/bcosca/fatfree-core/db/jig/mapper.php',
        'DB\\Jig\\Session' => __DIR__ . '/..' . '/bcosca/fatfree-core/db/jig/session.php',
        'DB\\Mongo' => __DIR__ . '/..' . '/bcosca/fatfree-core/db/mongo.php',
        'DB\\Mongo\\Mapper' => __DIR__ . '/..' . '/bcosca/fatfree-core/db/mongo/mapper.php',
        'DB\\Mongo\\Session' => __DIR__ . '/..' . '/bcosca/fatfree-core/db/mongo/session.php',
        'DB\\SQL' => __DIR__ . '/..' . '/bcosca/fatfree-core/db/sql.php',
        'DB\\SQL\\Mapper' => __DIR__ . '/..' . '/bcosca/fatfree-core/db/sql/mapper.php',
        'DB\\SQL\\Session' => __DIR__ . '/..' . '/bcosca/fatfree-core/db/sql/session.php',
        'F3' => __DIR__ . '/..' . '/bcosca/fatfree-core/f3.php',
        'Feed' => __DIR__ . '/..' . '/dg/rss-php/src/Feed.php',
        'FeedException' => __DIR__ . '/..' . '/dg/rss-php/src/Feed.php',
        'ISO' => __DIR__ . '/..' . '/bcosca/fatfree-core/base.php',
        'Image' => __DIR__ . '/..' . '/bcosca/fatfree-core/image.php',
        'Log' => __DIR__ . '/..' . '/bcosca/fatfree-core/log.php',
        'Magic' => __DIR__ . '/..' . '/bcosca/fatfree-core/magic.php',
        'Markdown' => __DIR__ . '/..' . '/bcosca/fatfree-core/markdown.php',
        'Matrix' => __DIR__ . '/..' . '/bcosca/fatfree-core/matrix.php',
        'Pagination' => __DIR__ . '/..' . '/ikkez/f3-pagination/lib/pagination.php',
        'Prefab' => __DIR__ . '/..' . '/bcosca/fatfree-core/base.php',
        'Preview' => __DIR__ . '/..' . '/bcosca/fatfree-core/base.php',
        'Registry' => __DIR__ . '/..' . '/bcosca/fatfree-core/base.php',
        'SMTP' => __DIR__ . '/..' . '/bcosca/fatfree-core/smtp.php',
        'Session' => __DIR__ . '/..' . '/bcosca/fatfree-core/session.php',
        'Template' => __DIR__ . '/..' . '/bcosca/fatfree-core/template.php',
        'Test' => __DIR__ . '/..' . '/bcosca/fatfree-core/test.php',
        'UTF' => __DIR__ . '/..' . '/bcosca/fatfree-core/utf.php',
        'View' => __DIR__ . '/..' . '/bcosca/fatfree-core/base.php',
        'Web' => __DIR__ . '/..' . '/bcosca/fatfree-core/web.php',
        'Web\\Geo' => __DIR__ . '/..' . '/bcosca/fatfree-core/web/geo.php',
        'Web\\Google\\Recaptcha' => __DIR__ . '/..' . '/bcosca/fatfree-core/web/google/recaptcha.php',
        'Web\\Google\\StaticMap' => __DIR__ . '/..' . '/bcosca/fatfree-core/web/google/staticmap.php',
        'Web\\OAuth2' => __DIR__ . '/..' . '/bcosca/fatfree-core/web/oauth2.php',
        'Web\\OpenID' => __DIR__ . '/..' . '/bcosca/fatfree-core/web/openid.php',
        'Web\\Pingback' => __DIR__ . '/..' . '/bcosca/fatfree-core/web/pingback.php',
        'simplehtmldom\\Debug' => __DIR__ . '/..' . '/simplehtmldom/simplehtmldom/Debug.php',
        'simplehtmldom\\HtmlDocument' => __DIR__ . '/..' . '/simplehtmldom/simplehtmldom/HtmlDocument.php',
        'simplehtmldom\\HtmlElement' => __DIR__ . '/..' . '/simplehtmldom/simplehtmldom/HtmlElement.php',
        'simplehtmldom\\HtmlNode' => __DIR__ . '/..' . '/simplehtmldom/simplehtmldom/HtmlNode.php',
        'simplehtmldom\\HtmlWeb' => __DIR__ . '/..' . '/simplehtmldom/simplehtmldom/HtmlWeb.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdbdee6ba53adb2c2ed62cb0a2d69c26c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdbdee6ba53adb2c2ed62cb0a2d69c26c::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitdbdee6ba53adb2c2ed62cb0a2d69c26c::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitdbdee6ba53adb2c2ed62cb0a2d69c26c::$classMap;

        }, null, ClassLoader::class);
    }
}
