<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitdbdee6ba53adb2c2ed62cb0a2d69c26c
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInitdbdee6ba53adb2c2ed62cb0a2d69c26c', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitdbdee6ba53adb2c2ed62cb0a2d69c26c', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitdbdee6ba53adb2c2ed62cb0a2d69c26c::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}