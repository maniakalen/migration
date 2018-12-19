# Yii2 Migration extension to allow migration generation from existing table

To activate it you need to apply the following configuration:

    'controllerMap' => [
            'migrate' => [
                'class' => 'maniakalen\migration\controllers\MigrateController',
                // Here configure MigrateController arguments as normal since it extends 
                // from the basic yii2 MigrateController
            ],
        ],