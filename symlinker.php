<?php

use Joomlatools\Console\Command\Extension;
use Joomlatools\Console\Joomla\Util;

use Symfony\Component\Console\Output\OutputInterface;

$dependencies = array(
    'joomlatools-framework' => array('nooku-framework'),
    'extman'  => array('joomlatools-framework'),
    'docman'  => array('extman', 'com_files'),
    'fileman' => array('extman', 'com_files'),
    'logman'  => array('extman', 'com_activities')
);

foreach ($dependencies as $project => $deps) {
    Extension\Symlink::registerDependencies($project, $deps);
}

/**
 * Nooku Framework custom symlinker
 */
Extension\Symlink::registerSymlinker(function($project, $destination, $name, $projects, OutputInterface $output) {
    // If we are symlinking Koowa, we need to create this structure to allow multiple symlinks in them
    if (array_intersect(array('nooku-framework', 'joomlatools-framework', 'koowa'), $projects))
    {
        $dirs = array(Util::buildTargetPath('/libraries/koowa/components', $destination), Util::buildTargetPath('/media/koowa', $destination));
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
    }

    if (!is_file($project.'/code/koowa.php')) {
        return false;
    }

    if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
        $output->writeln("Symlinking `" . basename($project) . "` into `$destination`");
    }

    $vendor_path = $destination.'/vendor';

    if(file_exists($destination.'/composer.json'))
    {
        $content  = file_get_contents($destination.'/composer.json');
        $composer = json_decode($content);

        if(isset($composer->config->{'vendor-dir'})) {
            $vendor_path = $destination.'/'.$composer->config->{'vendor-dir'};
        }
    }

    $code_destination = $vendor_path.'/nooku/nooku-framework';

    if (!is_dir(dirname($code_destination))) {
        mkdir(dirname($code_destination), 0777, true);
    }

    if (!file_exists($code_destination))
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln(" * creating link `$code_destination` -> `$project`");
        }

        `ln -sf $project $code_destination`;
    }

    $media_source      = $project.'/code/resources/assets';
    $media_destination = Util::buildTargetPath('/media/koowa/framework', $destination);

    if (!file_exists($media_destination))
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln(" * creating link `$media_destination` -> `$media_source`");
        }

        `ln -sf $media_source $media_destination`;
    }

    return true;
});