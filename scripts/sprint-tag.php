<?php
/**
 * Creates a tag for the current sprint, if it does not already exist.
 */
shell_exec('git describe --long');
$options = getopt('', array('date::', 'sprint::', 'version:', 'help'));
if (isset($options['help'])) {
    echo "This script will tag the current repository as x.y\n";
    echo "Where x is the major version you specify with --version\n";
    echo "and y is the sprint number.\n";
    echo "You can specify the sprint number with --sprint,\n";
    echo "or allow it to be automatically calculated for the team,\n";
    echo "based on the start date of 2016-12-08.\n";
    echo "This start date can also be overriden by --date\n";
    exit(0);
}
if (empty($options['version'])) {
    echo "Please specify --version as the major version of the application. Examples: 1 or 7.23\n";
    exit(1);
}

if (empty($options['date'])) {
    // The date of our first sprint start.
    $options['date'] = '2016-12-08';
}

if (empty($options['sprint'])) {
    $first_sprint = new DateTime($options['date']);
    $now = new DateTime('now');
    $interval = $first_sprint->diff($now);
    $days = $interval->format('%a');
    $sprint = ceil($days / 7) + 1;
    echo "We have been following Agile/Scrum for $days days.\n";
    echo "We are currently in sprint $sprint.\n";
    $options['sprint'] = $sprint;
}

shell_exec('git pull --tags');
$tags = explode("\n", trim(shell_exec('git tag')));
echo "Found " . count($tags) . " tags.\n";
$new_tag = $options['version'] . '.' . $options['sprint'];
if (in_array($new_tag, $tags)) {
    echo "Tag already exists for this sprint: $new_tag\n";
    exit(0);
}

echo "Creating tag $new_tag\n";
shell_exec('git tag -a "' . $new_tag . '" -m "Sprint ' . $options['sprint'] . ' work started."');
shell_exec('git push origin "' . $new_tag . '"');
shell_exec('git describe --long');