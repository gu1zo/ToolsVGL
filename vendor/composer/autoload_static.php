<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9743642d6983ecee831de14ef53a1543
{
    public static $prefixLengthsPsr4 = array(
        'W' =>
            array(
                'WilliamCosta\\DotEnv\\' => 20,
                'WilliamCosta\\DatabaseManager\\' => 29,
            ),
        'P' =>
            array(
                'PHPMailer\\PHPMailer\\' => 20,
            ),
        'A' =>
            array(
                'App\\' => 4,
            ),
    );

    public static $prefixDirsPsr4 = array(
        'WilliamCosta\\DotEnv\\' =>
            array(
                0 => __DIR__ . '/..' . '/william-costa/dot-env/src',
            ),
        'WilliamCosta\\DatabaseManager\\' =>
            array(
                0 => __DIR__ . '/..' . '/william-costa/database-manager/src',
            ),
        'PHPMailer\\PHPMailer\\' =>
            array(
                0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
            ),
        'App\\' =>
            array(
                0 => __DIR__ . '/../..' . '/App',
            ),
    );

    public static $classMap = array(
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9743642d6983ecee831de14ef53a1543::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9743642d6983ecee831de14ef53a1543::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9743642d6983ecee831de14ef53a1543::$classMap;

        }, null, ClassLoader::class);
    }
}