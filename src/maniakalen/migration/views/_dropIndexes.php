<?php foreach ($indexes as $index): ?>
    <?php if (!($index instanceof \yii\db\IndexConstraint) || $index->isPrimary) { continue; } ?>
    // creates index
    $this->dropIndex(
    '<?= $index->name ?>',
    '<?= $table ?>'
    );
<?php endforeach ?>