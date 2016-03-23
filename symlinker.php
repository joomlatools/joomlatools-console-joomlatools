<?php

use Joomlatools\Console\Command\Extension;
use Joomlatools\Console\Joomla\Util;

use Symfony\Component\Console\Output\OutputInterface;

$dependencies = array(
    'joomlatools-framework' => array('com_files', 'com_activities', 'com_scheduler', 'com_migrator'),
    'docman' => array('joomlatools-framework'),
    'fileman' => array('joomlatools-framework'),
    'logman' => array('joomlatools-framework'),
    'textman' => array('joomlatools-framework', 'com_ckeditor')
);

foreach ($dependencies as $project => $deps) {
    Extension\Symlink::registerDependencies($project, $deps);
}

/**
 * Nooku Framework custom symlinker
 */
Extension\Symlink::registerSymlinker(function($project, $destination, $name, $projects, OutputInterface $output) {
    if (!is_file($project.'/composer.json')) {
        return false;
    }

    $manifest = json_decode(file_get_contents($project.'/composer.json'));

    if (!isset($manifest->name) || $manifest->name != 'joomlatools/joomlatools-framework') {
        return false;
    }

    // build the folders to symlink into
    $dirs = array(
        Util::buildTargetPath('/libraries/joomlatools/component', $destination),
        Util::buildTargetPath('/media/koowa', $destination)
    );

    foreach ($dirs as $dir)
    {
        if (!is_dir($dir))
        {
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(" * creating empty directory `$dir`");
            }

            mkdir($dir, 0777, true);
        }
    }

    // Special treatment for media files
    $media = $project.'/code/libraries/joomlatools/component/koowa/resources/assets';
    $target = Util::buildTargetPath('/media/koowa/com_koowa', $destination);

    if (is_dir($media) && !file_exists($target))
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln(" * creating link `$target` -> $media");
        }

        `ln -sf $media $target`;
    }

    // Let the default symlinker handle the rest
    return false;
});