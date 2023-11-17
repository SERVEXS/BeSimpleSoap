<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
//        __DIR__ . '/tests',
    ]);
    $rectorConfig->skip([
        AddLiteralSeparatorToNumberRector::class,
    ]);
    $rectorConfig->symfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.php');

    $rectorConfig->sets([
        //        \Rector\PHPUnit\Set\PHPUnitLevelSetList::UP_TO_PHPUNIT_100,
        //        \Rector\Doctrine\Set\DoctrineSetList::DOCTRINE_CODE_QUALITY,
                \Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_82,
//        \Rector\Symfony\Set\TwigLevelSetList::UP_TO_TWIG_240,
//                \Rector\Symfony\Set\SymfonyLevelSetList::UP_TO_SYMFONY_63,
//                \Rector\Doctrine\Set\DoctrineSetList::DOCTRINE_ORM_213,
//                \Rector\Doctrine\Set\DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
//                \Rector\Doctrine\Set\DoctrineSetList::GEDMO_ANNOTATIONS_TO_ATTRIBUTES,
//                \Rector\Symfony\Set\JMSSetList::ANNOTATIONS_TO_ATTRIBUTES,
//                \Rector\Symfony\Set\SymfonySetList::SYMFONY_52_VALIDATOR_ATTRIBUTES,
//                \Rector\Symfony\Set\SensiolabsSetList::ANNOTATIONS_TO_ATTRIBUTES,
//                \Rector\Symfony\Set\FOSRestSetList::ANNOTATIONS_TO_ATTRIBUTES,
    ]);
};
