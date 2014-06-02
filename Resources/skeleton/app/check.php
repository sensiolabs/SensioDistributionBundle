<?php

require_once dirname(__FILE__).'/SymfonyRequirements.php';

$symfonyRequirements = new SymfonyRequirements();

$okMessage = '[OK] Your system is ready to execute Symfony2 projects';
$errorMessage = '[ERROR] Your system is not ready to execute Symfony2 projects';

$lineSize = strlen($errorMessage) + 2;

echo PHP_EOL;
echo 'Symfony2 Requirements Checker'.PHP_EOL;
echo '~~~~~~~~~~~~~~~~~~~~~~~~~~~~~';

$iniPath = $symfonyRequirements->getPhpIniConfigPath();

echo_title('Looking for the INI configuration file used by PHP:');
echo $iniPath ? $iniPath : 'WARNING: No configuration file (php.ini) used by PHP!';

echo_title('Checking Symfony requirements:');

$checkPassed = true;
$messages = array();
foreach ($symfonyRequirements->getRequirements() as $req) {
    /** @var $req Requirement */
    if ($helpText = getErrorMessage($req, $lineSize)) {
        echo 'E';
        $messages['error'][] = $helpText;
    } else {
        echo '.';
    }

    if (!$req->isFulfilled()) {
        $checkPassed = false;
    }
}

foreach ($symfonyRequirements->getRecommendations() as $req) {
    if ($helpText = getErrorMessage($req, $lineSize)) {
        echo 'W';
        $messages['warning'][] = $helpText;
    } else {
        echo '.';
    }
}

if (empty($messages['error'])) {
    echo_result($okMessage, $lineSize, 'ok');
}

if (!empty($messages['error'])) {
    echo_result($errorMessage, $lineSize, 'error');

    echo PHP_EOL.'Fix the following mandatory requirements'.PHP_EOL;
    echo str_repeat('~', $lineSize).PHP_EOL;

    foreach ($messages['error'] as $helpText) {
        echo ' * '.$helpText.PHP_EOL;
    }
}

if (!empty($messages['warning'])) {
    echo PHP_EOL.'Optional recommendations to improve your setup'.PHP_EOL;
    echo str_repeat('~', $lineSize).PHP_EOL;

    foreach ($messages['warning'] as $helpText) {
        echo ' * '.$helpText.PHP_EOL;
    }
}

echo PHP_EOL;
echo 'Note  the command console could use a different php.ini file'.PHP_EOL;
echo '~~~~  than the one used with your web server. To be on the'.PHP_EOL;
echo '      safe side, please check the requirements from your web'.PHP_EOL;
echo '      server using the web/config.php script.'.PHP_EOL;
echo PHP_EOL;

exit($checkPassed ? 0 : 1);

function getErrorMessage(Requirement $requirement, $lineSize)
{
    if ($requirement->isFulfilled()) {
        return;
    }

    $errorMessage  = wordwrap($requirement->getTestMessage(), $lineSize - 3, PHP_EOL.'   ').PHP_EOL;
    $errorMessage .= '   > '.wordwrap($requirement->getHelpText(), $lineSize - 5, PHP_EOL.'   > ').PHP_EOL;

    return $errorMessage;
}

function echo_title($title)
{
    echo PHP_EOL.PHP_EOL;
    echo '> '.$title.PHP_EOL;
    echo '  ';
}

function echo_result($message, $lineSize, $type)
{
    // ANSI color codes
    $colorCodes = array(
        "none"  => "\033[0m",
        "ok"    => "\033[32m",
        "error" => "\033[37;41m",
    );

    $lineStart = hasColorSupport() ? $colorCodes[$type] : '';
    $lineEnd   = hasColorSupport() ? $colorCodes['none'].PHP_EOL : PHP_EOL;

    echo PHP_EOL.PHP_EOL;

    echo $lineStart.'+'.str_repeat('-', $lineSize).'+'.$lineEnd;
    echo $lineStart.'|'.str_repeat(' ', $lineSize).'|'.$lineEnd;
    echo $lineStart.'| '.$message.str_repeat(' ', $lineSize - strlen($message) - 2).' |'.$lineEnd;
    echo $lineStart.'|'.str_repeat(' ', $lineSize).'|'.$lineEnd;
    echo $lineStart.'+'.str_repeat('-', $lineSize).'+'.$lineEnd;
}

function hasColorSupport()
{
    if (DIRECTORY_SEPARATOR == '\\') {
        return false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI');
    }

    return function_exists('posix_isatty') && @posix_isatty(STDOUT);
}
