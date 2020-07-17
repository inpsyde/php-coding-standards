<?php
// @phpcsSniff CodeQuality.LineLength

?>
    <!-- Warning two lines below: multiple attributes can go each in one line -->
    <!-- @phpcsWarningOnNextLine -->
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="foo bar baz foo-bar-baz foo_bar_baz foo_bar_baz__foo_bar_baz foo_bar_baz__foo_bar_baz--one">
        <rect x="0" fill="none" width="20" height="20"/>
        <g>
            <!-- NO warning below: don't require splitting a single attribute in multiple lines -->
            <path d="x8h2.93v-7.3h2 45l.37-2.84h-2 82V6.04c0-.82.23-1.38 8h2.93v-7.3h2.45l.37-2.84h-2.82V6.04c0-.82.23-1.38 8h2.93v-7.3h2.45l.37-2.84h-2.82V6.04c0-.82.23-1.38"/>
        </g>
        <g>
            <!-- NO warning below: don't require splitting a single attribute in multiple lines -->
            <path d="x8h2.93v-7.3h2 45l.37-2.84h-2 82V6.04c0-.82.23-1.38 <?= somePhPCodeUsedInsideTheAttribute('foo bar = "baz"') ?>"/>
        </g>
    </svg>
<?php

use Some\Very\Very\Very\Very\Very\Long\Long\Long\Long\Long\Long\Long\Long\Nested\Nested\Nested\Nested\Nested\Nested\Name\Space;

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
 * No warnings for a long URL: it can't be split.
 * @link https://foo.example.com/some-path/to/#a-page-that-co0ntains-some-important-information-that-you-should-really-look-at
 *
 * @return string
 */
function longComment() {
    return;
}

?>
    <div>
        <div>
            <div>
                <div>
                    <div>
                        <!-- Don't expect any warning for this tag -->
                        <img class="foo-bar__baz foo-bar__baz--meh"
                             src="https://www.example.com/some/oath/to/an/image-file-with-a-quite-long-file-name-200x400.png"
                             alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>

