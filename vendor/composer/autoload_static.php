<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit741198e676f2f73fee47eb1c2964c9cd
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit741198e676f2f73fee47eb1c2964c9cd::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit741198e676f2f73fee47eb1c2964c9cd::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit741198e676f2f73fee47eb1c2964c9cd::$classMap;

        }, null, ClassLoader::class);
    }
}
