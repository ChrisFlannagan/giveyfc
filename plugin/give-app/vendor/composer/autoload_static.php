<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit773699bf955a8f570aac1c31654b10d2
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

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit773699bf955a8f570aac1c31654b10d2::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit773699bf955a8f570aac1c31654b10d2::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
