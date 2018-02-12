<?php
// @phpcsSniff CodeQuality.AssignmentInsideCondition

if (($foo = 'x') || $foo += 'y' || ($foo = 'x')) {
    // @phpcsWarningOnPreviousLine
    return 1;
}

if (($foo = 'x') && ($foo += 'y' && ($a = 'b')) || ($foo = 'z')) {
    // @phpcsWarningOnPreviousLine
    return 1;
}

if ($foo = 'x') {
    // @phpcsWarningOnPreviousLine
    return 1;
}

if (($foo = 'x') || ($foo = 'y') || ($foo = 'x')) {
    return 1;
}

if (($foo = 'x')) {
    return 1;
} elseif (($foo = 'y')) {
    return 2;
} elseif ($foo = 'x') {
    // @phpcsWarningOnPreviousLine
    return 3;
}

function inside_function() {

    if (($foo = 'x')) {
        return 1;
    } elseif (($foo = 'y')) {
        return 2;
    } elseif ($foo = 'x') {
        // @phpcsWarningOnPreviousLine
        return 3;
    }

    return 4;
}

class OneClass {

    function oneMethod() {

        if (false) {
            return false;
        }

        $foo = 1;

        if (($foo = 5)) {
            return 1;
        } elseif (($foo |= 2)) {
            return 2;
        } elseif ($foo *= 0) {
            // @phpcsWarningOnPreviousLine
            return 3;
        }

        return 4;
    }
}