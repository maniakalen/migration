<?php

/**
 * Creates a call for the method `yii\db\Migration::createTable()`.
 */
/* @var $table string the name table */
/* @var $fields array the fields */
/* @var $indexes array the fields */
/* @var $foreignKeys array the foreign keys */

?>        $this->createTable('<?= $table ?>', [
<?php foreach ($fields as $field):
    if (empty($field['decorators'])): ?>
            '<?= $field['property'] ?>',
<?php else: ?>
            <?= "'{$field['property']}' => \$this->{$field['decorators']}" ?>,
<?php endif;
endforeach; ?>
        ], $tableOptions);

<?php
echo $this->render('_addIndexes', [
    'table' => $table,
    'indexes' => $indexes,
]);
echo $this->render('_addForeignKeys', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
]);
if (isset($data) && !empty($data)) {
    echo $this->render('_addData', ['table' => $table, 'data' => $data]);
}
