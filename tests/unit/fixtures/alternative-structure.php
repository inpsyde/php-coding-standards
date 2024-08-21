<?php

declare(strict_types=1);

// @phpcsSniff InpsydeTemplates.Formatting.AlternativeControlStructure

const FLAGS = [
    'YES',
    'NO',
    'MAYBE',
];

$flag = FLAGS[rand(0, 2)];

if ($flag === 'MAYBE') {
    echo 'maybe';

    while ($flag !== 'YES') {
        $flag = 'YES';
    }
} elseif ($flag === 'NO') {
    echo 'no';
} else if ($flag === 'YES') {
    echo 'yes';
} else {
    echo 'Non Empty value';
}

if ($flag === 'MAYBE') :
    echo 'maybe';

    while ($flag !== 'YES') {
        $flag = 'YES';
    }
elseif ($flag === 'NO') :
    echo 'no';
else :
    echo 'Non Empty value';
endif;


$arrayOfFlags = [];
for ($i = 1; $i <= 10; $i++) {
    $arrayOfFlags[] = FLAGS[rand(0, 2)];
}

foreach ($arrayOfFlags as &$item) {
    $item = false;
}
unset($item);

switch ($flag) {
    case 'YES':
        echo 'It is true';
        break;
    case 'NO':
        echo 'It is false';
        break;
}

?>

<?php if ($flag === 'MAYBE') { // @phpcsWarningOnThisLine ?>
    <div>Maybe.</div>
    <?php while ($flag !== 'YES') {
        $flag = 'YES';
    }
} elseif ($flag === 'NO') { // @phpcsWarningOnThisLine
    while ($flag !== 'YES') { // @phpcsWarningOnThisLine
        $flag = 'YES';
        ?>
        <div>No. Yes.</div>
        <?php }
} else if ($flag === 'YES') { // @phpcsWarningOnThisLine
    echo 'yes';
} else { // @phpcsWarningOnThisLine
    echo 'Non Empty value';
} ?>

<?php if ($flag === 'MAYBE') { // @phpcsWarningOnThisLine
    return;
} else if ($flag === 'NO') { // @phpcsWarningOnThisLine
    echo 'no';
} elseif ($flag === 'YES') { // @phpcsWarningOnThisLine ?>
    <div>Yes.</div>
<?php } else { // @phpcsWarningOnThisLine
    echo 'Non Empty value';
} ?>

<?php
for ($i = 1; $i <= 10; $i++) { // @phpcsWarningOnThisLine ?>
    <div><?= $i ?></div>
<?php }

foreach ($arrayOfFlags as $item) { // @phpcsWarningOnThisLine ?>
    <div><?= $item ?></div>
<?php }

switch ($flag) { // @phpcsWarningOnThisLine
    case 'YES':
        ?>
        <div>YES</div>
        <?php
        break;
    case 'NO':
        echo 'It is false';
        break;
}
