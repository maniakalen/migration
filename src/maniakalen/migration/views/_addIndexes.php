<?php foreach ($indexes as $index): ?>
    <?php if (!($index instanceof \yii\db\IndexConstraint) || $index->isPrimary) { continue; } ?>
    // creates index
    $this->createIndex(
    '<?= $index->name ?>',
    '<?= $table ?>',
    ['<?= implode("','", $index->columnNames) ?>'],
    <?= $index->isUnique?'true':'false'?>
    );
<?php endforeach ?>
