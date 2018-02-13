<?php
// @phpcsSniff CodeQuality.LineLength

// @phpcsWarningOnNextLine
$b = '7f4a87f2df8a8e8b8febd6631589e062 7f4a87f2df8a8e8b8febd6631589e062 7f4a87f2df8a8e8b8febd6631589e062 7f4a87f2df8a8e8b8febd6631589e062 
a long line like previous does trigger errors!.
';

$a = 'a long line like next
http://example.com/?7f4a87f2df8a8e8b8febd6631589e062=7f4a87f2df8a8e8b8febd6631589e0627f4a87f2df8a8e8b8febd6631589e0627f4a87f2df8a8e8b8febd6631589e062
does not trigger errors because it is a single word.
';

__(
    'This line does not trigger error because it is the first argument of a translation string that cannot be split or WordPress style will complain.',
    'textdomain'
);

foo( // @phpcsWarningOnNextLine
    'This line does trigger error because it is not a translation string, so can be split in multiple lines without issues.',
    'foo bar'
);

esc_html__(
    'This line does not trigger error because it is the first argument of a translation string that cannot be split or WordPress style will complain.',
    'textdomain'
);

function meh()
{

    _ex(
        'This line does not trigger error because it is the first argument of a translation string that cannot be split or WordPress style will complain.',
        'meh',
        'textdomain'
    );

    foo( // @phpcsWarningOnNextLine
        'This line does trigger error because it is not a translation string, so can be split in multiple lines without issues.',
        'foo bar'
    );
}

class FooBarBazMeh
{

    function meh()
    {

        esc_attr_x(
            'This line does not trigger error because it is the first argument of a translation string that cannot be split or WordPress style will complain.',
            'meh',
            'textdomain'
        );

        foo( // @phpcsWarningOnNextLine
            'This line does trigger error because it is not a translation string, so can be split in multiple lines without issues.',
            'foo bar'
        );
    }

    public function render()
    {
        ?>
        <p class="description">
            <?php
            esc_html_e(
                'Lorem ipsum dolor sit amet, consectetur adipiscing el. Morbi egestas, purus non luctus semper, ligula ante venenatis',
                'ipsum'
            );
            ?>
        </p>
        <?php
    }
}

/**
 * @link https://foo.example.com/some-path/to/#a-page-that-co0ntains-some-important-information-that-you-should-really-look-at
 *
 * @return string
 */
function longComment() {
    return;
}

