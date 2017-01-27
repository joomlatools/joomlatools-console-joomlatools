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
