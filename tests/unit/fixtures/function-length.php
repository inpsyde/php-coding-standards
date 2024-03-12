<?php
// @phpcsSniff Inpsyde.CodeQuality.FunctionLength

// @phpcsErrorOnNextLine
function test() {
    $a = '
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    ';
}

function test_docblocks() {
    /**
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    */
}

function test_docblock_whitespaces() {
    /**
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    */










    /**
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    */










    /**
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    */
}

function test_comments_whitespaces() {

    #1
    #2
    #3
    #4
    #5
    #6
    #7
    #8
    #9
    #10









    #1
    #2
    #3
    #4
    #5
    #6
    #7
    #8
    #9
    #10










    #1
    #2
    #3
    #4
    #5
    #6
    #7
    #8
    #9
    #10










    #1
    #2
    #3
    #4
    #5
    #6
    #7
    #8
    #9
    #10
}

class FooBar {

    // @phpcsErrorOnNextLine
    function test() {
        $a = '
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    1
    2
    3
    4
    5
    6
    7
    8
    9
    10
    ';
    }

    function testDocblocks() {
        /**
        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        */
    }

    function testDocblockWhitespaces() {
        /**
        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        */










        /**
        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        */










        /**
        1
        2
        3
        4
        5
        6
        7
        8
        9
        10
        */
    }

    function test_comments_whitespaces()
    {
        #1
        #2
        #3
        #4
        #5
        #6
        #7
        #8
        #9
        #10























































    }

    function testCommentsWhitespaces() {

        #1
        #2
        #3
        #4
        #5
        #6
        #7
        #8
        #9
        #10









        #1
        #2
        #3
        #4
        #5
        #6
        #7
        #8
        #9
        #10










        #1
        #2
        #3
        #4
        #5
        #6
        #7
        #8
        #9
        #10










        #1
        #2
        #3
        #4
        #5
        #6
        #7
        #8
        #9
        #10
    }
}
