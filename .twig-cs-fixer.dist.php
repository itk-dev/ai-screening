<?php
// This file is copied from config/drupal/twig/.twig-cs-fixer.dist.php in https://github.com/itk-dev/devops_itkdev-docker.
// Feel free to edit the file, but consider making a pull request if you find a general issue with the file.

// https://github.com/VincentLanglet/Twig-CS-Fixer/blob/main/docs/configuration.md#configuration-file

$finder = new TwigCsFixer\File\Finder();
// Check all files …
$finder->in(__DIR__);
// … that are not ignored by VCS
$finder->ignoreVCSIgnored(true);

$config = new TwigCsFixer\Config\Config();
$config->setFinder($finder);

return $config;
