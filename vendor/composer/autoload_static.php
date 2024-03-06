<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7f3de756490c23cc9cb611ba096f2141
{
    public static $prefixLengthsPsr4 = array (
        'c' => 
        array (
            'console\\' => 8,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'console\\' => 
        array (
            0 => __DIR__ . '/../..' . '/ComparisonProject/Integration/Incoming/Console',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/ComparisonProject',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7f3de756490c23cc9cb611ba096f2141::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7f3de756490c23cc9cb611ba096f2141::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit7f3de756490c23cc9cb611ba096f2141::$classMap;

        }, null, ClassLoader::class);
    }
}