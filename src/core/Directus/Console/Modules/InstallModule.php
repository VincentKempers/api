<?php

namespace Directus\Console\Modules;

use Directus\Console\Common\Exception\PasswordChangeException;
use Directus\Console\Common\Exception\UserUpdateException;
use Directus\Console\Common\Setting;
use Directus\Console\Common\User;
use Directus\Console\Exception\CommandFailedException;
use Directus\Util\Installation\InstallerUtils;

class InstallModule extends ModuleBase
{
    protected $__module_name = 'install';
    protected $__module_description = 'commands to install and configure Directus';

    protected $commands_help;

    protected $help;

    public function __construct($basePath)
    {
        parent::__construct($basePath);

        $this->help = [
            'config' => ''
                . PHP_EOL . "\t\t-h " . 'Hostname or IP address of the MySQL DB to be used. Default: localhost'
                . PHP_EOL . "\t\t-n " . 'Name of the database to use for Directus. Default: directus'
                . PHP_EOL . "\t\t-u " . 'Username for DB connection. Default: directus'
                . PHP_EOL . "\t\t-p " . 'Password for the DB connection user. Default: directus'
                . PHP_EOL . "\t\t-t " . 'Database Server Type. Default: mysql'
                . PHP_EOL . "\t\t-P " . 'Database Server Port. Default: 3306'
                . PHP_EOL . "\t\t-c " . 'CORS Enabled. Default: false'
                . PHP_EOL . "\t\t-r " . 'Directus root URI. Default: /',
            'database' => '',
            'migrate' => '',
            'install' => ''
                . PHP_EOL . "\t\t-e " . 'Administrator e-mail address, used for administration login. Default: admin@directus.com'
                . PHP_EOL . "\t\t-p " . 'Initial administrator password. Default: directus'
                . PHP_EOL . "\t\t-t " . 'Name for this Directus installation. Default: Directus'
                . PHP_EOL . "\t\t-T " . 'Administrator secret token. Default: Random'
                . PHP_EOL . "\t\t-d " . 'Installation path of Directus. Default: ' . $this->getBasePath()
        ];

        $this->commands_help = [
            'config' => 'Configure Directus: ' . PHP_EOL . PHP_EOL . "\t\t"
                . $this->__module_name . ':config -h db_host -n db_name -u db_user -p db_pass -d directus_path' . PHP_EOL,
            'database' => 'Populate the Database Schema: ' . PHP_EOL . PHP_EOL . "\t\t"
                . $this->__module_name . ':database -d directus_path' . PHP_EOL,
            'install' => 'Install Initial Configurations: ' . PHP_EOL . PHP_EOL . "\t\t"
                . $this->__module_name . ':install -e admin_email -p admin_password -t site_name' . PHP_EOL,
        ];
    }

    public function cmdConfig($args, $extra)
    {
        $data = [];

        $data['db_type'] = 'mysql';
        $data['db_port'] = '3306';
        $data['db_host'] = 'localhost';
        $data['db_name'] = '';
        $data['db_user'] = '';
        $data['db_password'] = '';

        $directusPath = $this->getBasePath();

        foreach ($args as $key => $value) {
            switch ($key) {
                case 't':
                    $data['db_type'] = $value;
                    break;
                case 'P':
                    $data['db_port'] = $value;
                    break;
                case 'h':
                    $data['db_host'] = $value;
                    break;
                case 'n':
                    $data['db_name'] = $value;
                    break;
                case 'u':
                    $data['db_user'] = $value;
                    break;
                case 'p':
                    $data['db_password'] = $value;
                    break;
                case 'r':
                    $directusPath = $value;
                    break;
                case 'e':
                    $data['directus_email'] = $value;
                    break;
                case 'c':
                    $data['cors_enabled'] = (bool) $value;
                    break;
                case 'E':
                    $data['env'] = (string) $value;
                    break;
            }
        }

        $apiPath = rtrim($directusPath, '/') . '/config';
        if (!file_exists($apiPath)) {
            throw new \Exception(sprintf('Path "%s" does not exist', $apiPath));
        }

        InstallerUtils::createConfig($directusPath, $data);
    }

    public function cmdDatabase($args, $extra)
    {
        $directus_path = $this->getBasePath() . DIRECTORY_SEPARATOR;
        $env = null;

        foreach ($args as $key => $value) {
            switch ($key) {
                case 'd':
                    $directus_path = $value;
                    break;
                case 'E':
                    $env = $value;
                    break;
            }
        }

        InstallerUtils::createTables($directus_path, $env);
    }

    public function cmdMigrate($args, $extra)
    {
        $directus_path = $this->getBasePath() . DIRECTORY_SEPARATOR;

        InstallerUtils::runMigration($directus_path);
    }

    public function cmdSeeder($args, $extra)
    {
        $directus_path = $this->getBasePath() . DIRECTORY_SEPARATOR;

        InstallerUtils::runSeeder($directus_path);
    }

    public function cmdInstall($args, $extra)
    {
        $data = [];
        $env = null;
        $directus_path = $this->getBasePath() . DIRECTORY_SEPARATOR;

        foreach ($args as $key => $value) {
            switch ($key) {
                case 'e':
                    $data['directus_email'] = $value;
                    break;
                case 'p':
                    $data['directus_password'] = $value;
                    break;
                case 't':
                    $data['directus_name'] = $value;
                    break;
                case 'T':
                    $data['directus_token'] = $value;
                    break;
                case 'd':
                    $directus_path = $value;
                    break;
                case 'E':
                    $env = $value;
                    break;
            }
        }

        try {
            $setting = new Setting($directus_path, $env);

            if (!$setting->isConfigured()) {
                InstallerUtils::addDefaultSettings($directus_path, $data, $env);
                InstallerUtils::addDefaultUser($directus_path, $data, $env);
            } else {
                $setting->setSetting('global', 'project_name', $data['directus_name']);
                 // NOTE: Do we really want to change the email when re-run install command?
                 $user = new User($directus_path);
                 try {
                     $user->changeEmail(1, $data['directus_email']);
                     $user->changePassword($data['directus_email'], $data['directus_password']);
                 } catch (UserUpdateException $ex) {
                     throw new CommandFailedException('Error changing admin e-mail' . ': ' . $ex->getMessage());
                 } catch (PasswordChangeException $ex) {
                     throw new CommandFailedException('Error changing user password' . ': ' . $ex->getMessage());
                 }
            }
        } catch (\PDOException $e) {
            echo PHP_EOL . "PDO Excetion!!" . PHP_EOL;
            echo PHP_EOL . PHP_EOL . 'Module ' . $this->__module_name . ' error: ' . $e->getMessage() . PHP_EOL . PHP_EOL;
        }
    }
}
