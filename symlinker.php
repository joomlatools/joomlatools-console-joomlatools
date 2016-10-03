<?php

use Joomlatools\Console\Command\Extension;
use Joomlatools\Console\Joomla\Util;

use Symfony\Component\Console\Output\OutputInterface;

$dependencies = array(
    'extman' => array('nooku-framework', 'com_files', 'com_activities'), // deprecated

    'joomlatools-framework' => array(
        'joomlatools-framework-files', 'joomlatools-framework-activities', 'joomlatools-framework-scheduler',
        'joomlatools-framework-migrator', 'joomlatools-framework-tags'
    ),
    'docman' => array('joomlatools-framework'),
    'fileman' => array('joomlatools-framework'),
    'logman' => array('joomlatools-framework'),
    'textman' => array('joomlatools-framework', 'joomlatools-framework-ckeditor')
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

    if (!isset($manifest->name) || $manifest->name != 'joomlatools/framework') {
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

    /*
     * Special treatment for media files
     */
    $media = array(
        $project.'/code/libraries/joomlatools/component/koowa/resources/assets' => Util::buildTargetPath('/media/koowa/com_koowa', $destination),
        $project.'/code/libraries/joomlatools/library/resources/assets' => Util::buildTargetPath('/media/koowa/framework', $destination),
    );

    foreach ($media as $from => $to)
    {
        if (is_dir($from) && !file_exists($to))
        {
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(" * creating link `$to` -> $from");
            }

            `ln -sf $from $to`;
        }
    }

    // Let the default symlinker handle the rest
    return false;
});