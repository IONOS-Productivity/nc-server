<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitUser_LDAP
{
    public static $prefixLengthsPsr4 = array (
        'O' => 
        array (
            'OCA\\User_LDAP\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'OCA\\User_LDAP\\' => 
        array (
            0 => __DIR__ . '/..' . '/../lib',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'OCA\\User_LDAP\\Access' => __DIR__ . '/..' . '/../lib/Access.php',
        'OCA\\User_LDAP\\AccessFactory' => __DIR__ . '/..' . '/../lib/AccessFactory.php',
        'OCA\\User_LDAP\\AppInfo\\Application' => __DIR__ . '/..' . '/../lib/AppInfo/Application.php',
        'OCA\\User_LDAP\\BackendUtility' => __DIR__ . '/..' . '/../lib/BackendUtility.php',
        'OCA\\User_LDAP\\Command\\CheckGroup' => __DIR__ . '/..' . '/../lib/Command/CheckGroup.php',
        'OCA\\User_LDAP\\Command\\CheckUser' => __DIR__ . '/..' . '/../lib/Command/CheckUser.php',
        'OCA\\User_LDAP\\Command\\CreateEmptyConfig' => __DIR__ . '/..' . '/../lib/Command/CreateEmptyConfig.php',
        'OCA\\User_LDAP\\Command\\DeleteConfig' => __DIR__ . '/..' . '/../lib/Command/DeleteConfig.php',
        'OCA\\User_LDAP\\Command\\PromoteGroup' => __DIR__ . '/..' . '/../lib/Command/PromoteGroup.php',
        'OCA\\User_LDAP\\Command\\ResetGroup' => __DIR__ . '/..' . '/../lib/Command/ResetGroup.php',
        'OCA\\User_LDAP\\Command\\ResetUser' => __DIR__ . '/..' . '/../lib/Command/ResetUser.php',
        'OCA\\User_LDAP\\Command\\Search' => __DIR__ . '/..' . '/../lib/Command/Search.php',
        'OCA\\User_LDAP\\Command\\SetConfig' => __DIR__ . '/..' . '/../lib/Command/SetConfig.php',
        'OCA\\User_LDAP\\Command\\ShowConfig' => __DIR__ . '/..' . '/../lib/Command/ShowConfig.php',
        'OCA\\User_LDAP\\Command\\ShowRemnants' => __DIR__ . '/..' . '/../lib/Command/ShowRemnants.php',
        'OCA\\User_LDAP\\Command\\TestConfig' => __DIR__ . '/..' . '/../lib/Command/TestConfig.php',
        'OCA\\User_LDAP\\Command\\TestUserSettings' => __DIR__ . '/..' . '/../lib/Command/TestUserSettings.php',
        'OCA\\User_LDAP\\Command\\UpdateUUID' => __DIR__ . '/..' . '/../lib/Command/UpdateUUID.php',
        'OCA\\User_LDAP\\Configuration' => __DIR__ . '/..' . '/../lib/Configuration.php',
        'OCA\\User_LDAP\\Connection' => __DIR__ . '/..' . '/../lib/Connection.php',
        'OCA\\User_LDAP\\ConnectionFactory' => __DIR__ . '/..' . '/../lib/ConnectionFactory.php',
        'OCA\\User_LDAP\\Controller\\ConfigAPIController' => __DIR__ . '/..' . '/../lib/Controller/ConfigAPIController.php',
        'OCA\\User_LDAP\\Controller\\RenewPasswordController' => __DIR__ . '/..' . '/../lib/Controller/RenewPasswordController.php',
        'OCA\\User_LDAP\\DataCollector\\LdapDataCollector' => __DIR__ . '/..' . '/../lib/DataCollector/LdapDataCollector.php',
        'OCA\\User_LDAP\\Db\\GroupMembership' => __DIR__ . '/..' . '/../lib/Db/GroupMembership.php',
        'OCA\\User_LDAP\\Db\\GroupMembershipMapper' => __DIR__ . '/..' . '/../lib/Db/GroupMembershipMapper.php',
        'OCA\\User_LDAP\\Events\\GroupBackendRegistered' => __DIR__ . '/..' . '/../lib/Events/GroupBackendRegistered.php',
        'OCA\\User_LDAP\\Events\\UserBackendRegistered' => __DIR__ . '/..' . '/../lib/Events/UserBackendRegistered.php',
        'OCA\\User_LDAP\\Exceptions\\AttributeNotSet' => __DIR__ . '/..' . '/../lib/Exceptions/AttributeNotSet.php',
        'OCA\\User_LDAP\\Exceptions\\ConstraintViolationException' => __DIR__ . '/..' . '/../lib/Exceptions/ConstraintViolationException.php',
        'OCA\\User_LDAP\\Exceptions\\NoMoreResults' => __DIR__ . '/..' . '/../lib/Exceptions/NoMoreResults.php',
        'OCA\\User_LDAP\\Exceptions\\NotOnLDAP' => __DIR__ . '/..' . '/../lib/Exceptions/NotOnLDAP.php',
        'OCA\\User_LDAP\\FilesystemHelper' => __DIR__ . '/..' . '/../lib/FilesystemHelper.php',
        'OCA\\User_LDAP\\GroupPluginManager' => __DIR__ . '/..' . '/../lib/GroupPluginManager.php',
        'OCA\\User_LDAP\\Group_LDAP' => __DIR__ . '/..' . '/../lib/Group_LDAP.php',
        'OCA\\User_LDAP\\Group_Proxy' => __DIR__ . '/..' . '/../lib/Group_Proxy.php',
        'OCA\\User_LDAP\\Handler\\ExtStorageConfigHandler' => __DIR__ . '/..' . '/../lib/Handler/ExtStorageConfigHandler.php',
        'OCA\\User_LDAP\\Helper' => __DIR__ . '/..' . '/../lib/Helper.php',
        'OCA\\User_LDAP\\IGroupLDAP' => __DIR__ . '/..' . '/../lib/IGroupLDAP.php',
        'OCA\\User_LDAP\\ILDAPGroupPlugin' => __DIR__ . '/..' . '/../lib/ILDAPGroupPlugin.php',
        'OCA\\User_LDAP\\ILDAPUserPlugin' => __DIR__ . '/..' . '/../lib/ILDAPUserPlugin.php',
        'OCA\\User_LDAP\\ILDAPWrapper' => __DIR__ . '/..' . '/../lib/ILDAPWrapper.php',
        'OCA\\User_LDAP\\IUserLDAP' => __DIR__ . '/..' . '/../lib/IUserLDAP.php',
        'OCA\\User_LDAP\\Jobs\\CleanUp' => __DIR__ . '/..' . '/../lib/Jobs/CleanUp.php',
        'OCA\\User_LDAP\\Jobs\\Sync' => __DIR__ . '/..' . '/../lib/Jobs/Sync.php',
        'OCA\\User_LDAP\\Jobs\\UpdateGroups' => __DIR__ . '/..' . '/../lib/Jobs/UpdateGroups.php',
        'OCA\\User_LDAP\\LDAP' => __DIR__ . '/..' . '/../lib/LDAP.php',
        'OCA\\User_LDAP\\LDAPProvider' => __DIR__ . '/..' . '/../lib/LDAPProvider.php',
        'OCA\\User_LDAP\\LDAPProviderFactory' => __DIR__ . '/..' . '/../lib/LDAPProviderFactory.php',
        'OCA\\User_LDAP\\LDAPUtility' => __DIR__ . '/..' . '/../lib/LDAPUtility.php',
        'OCA\\User_LDAP\\LoginListener' => __DIR__ . '/..' . '/../lib/LoginListener.php',
        'OCA\\User_LDAP\\Mapping\\AbstractMapping' => __DIR__ . '/..' . '/../lib/Mapping/AbstractMapping.php',
        'OCA\\User_LDAP\\Mapping\\GroupMapping' => __DIR__ . '/..' . '/../lib/Mapping/GroupMapping.php',
        'OCA\\User_LDAP\\Mapping\\UserMapping' => __DIR__ . '/..' . '/../lib/Mapping/UserMapping.php',
        'OCA\\User_LDAP\\Migration\\GroupMappingMigration' => __DIR__ . '/..' . '/../lib/Migration/GroupMappingMigration.php',
        'OCA\\User_LDAP\\Migration\\RemoveRefreshTime' => __DIR__ . '/..' . '/../lib/Migration/RemoveRefreshTime.php',
        'OCA\\User_LDAP\\Migration\\SetDefaultProvider' => __DIR__ . '/..' . '/../lib/Migration/SetDefaultProvider.php',
        'OCA\\User_LDAP\\Migration\\UUIDFix' => __DIR__ . '/..' . '/../lib/Migration/UUIDFix.php',
        'OCA\\User_LDAP\\Migration\\UUIDFixGroup' => __DIR__ . '/..' . '/../lib/Migration/UUIDFixGroup.php',
        'OCA\\User_LDAP\\Migration\\UUIDFixInsert' => __DIR__ . '/..' . '/../lib/Migration/UUIDFixInsert.php',
        'OCA\\User_LDAP\\Migration\\UUIDFixUser' => __DIR__ . '/..' . '/../lib/Migration/UUIDFixUser.php',
        'OCA\\User_LDAP\\Migration\\UnsetDefaultProvider' => __DIR__ . '/..' . '/../lib/Migration/UnsetDefaultProvider.php',
        'OCA\\User_LDAP\\Migration\\Version1010Date20200630192842' => __DIR__ . '/..' . '/../lib/Migration/Version1010Date20200630192842.php',
        'OCA\\User_LDAP\\Migration\\Version1120Date20210917155206' => __DIR__ . '/..' . '/../lib/Migration/Version1120Date20210917155206.php',
        'OCA\\User_LDAP\\Migration\\Version1130Date20211102154716' => __DIR__ . '/..' . '/../lib/Migration/Version1130Date20211102154716.php',
        'OCA\\User_LDAP\\Migration\\Version1130Date20220110154717' => __DIR__ . '/..' . '/../lib/Migration/Version1130Date20220110154717.php',
        'OCA\\User_LDAP\\Migration\\Version1130Date20220110154718' => __DIR__ . '/..' . '/../lib/Migration/Version1130Date20220110154718.php',
        'OCA\\User_LDAP\\Migration\\Version1130Date20220110154719' => __DIR__ . '/..' . '/../lib/Migration/Version1130Date20220110154719.php',
        'OCA\\User_LDAP\\Migration\\Version1141Date20220323143801' => __DIR__ . '/..' . '/../lib/Migration/Version1141Date20220323143801.php',
        'OCA\\User_LDAP\\Migration\\Version1190Date20230706134108' => __DIR__ . '/..' . '/../lib/Migration/Version1190Date20230706134108.php',
        'OCA\\User_LDAP\\Migration\\Version1190Date20230706134109' => __DIR__ . '/..' . '/../lib/Migration/Version1190Date20230706134109.php',
        'OCA\\User_LDAP\\Notification\\Notifier' => __DIR__ . '/..' . '/../lib/Notification/Notifier.php',
        'OCA\\User_LDAP\\PagedResults\\TLinkId' => __DIR__ . '/..' . '/../lib/PagedResults/TLinkId.php',
        'OCA\\User_LDAP\\Proxy' => __DIR__ . '/..' . '/../lib/Proxy.php',
        'OCA\\User_LDAP\\Service\\BirthdateParserService' => __DIR__ . '/..' . '/../lib/Service/BirthdateParserService.php',
        'OCA\\User_LDAP\\Service\\UpdateGroupsService' => __DIR__ . '/..' . '/../lib/Service/UpdateGroupsService.php',
        'OCA\\User_LDAP\\Settings\\Admin' => __DIR__ . '/..' . '/../lib/Settings/Admin.php',
        'OCA\\User_LDAP\\Settings\\Section' => __DIR__ . '/..' . '/../lib/Settings/Section.php',
        'OCA\\User_LDAP\\SetupChecks\\LdapConnection' => __DIR__ . '/..' . '/../lib/SetupChecks/LdapConnection.php',
        'OCA\\User_LDAP\\SetupChecks\\LdapInvalidUuids' => __DIR__ . '/..' . '/../lib/SetupChecks/LdapInvalidUuids.php',
        'OCA\\User_LDAP\\UserPluginManager' => __DIR__ . '/..' . '/../lib/UserPluginManager.php',
        'OCA\\User_LDAP\\User\\DeletedUsersIndex' => __DIR__ . '/..' . '/../lib/User/DeletedUsersIndex.php',
        'OCA\\User_LDAP\\User\\Manager' => __DIR__ . '/..' . '/../lib/User/Manager.php',
        'OCA\\User_LDAP\\User\\OfflineUser' => __DIR__ . '/..' . '/../lib/User/OfflineUser.php',
        'OCA\\User_LDAP\\User\\User' => __DIR__ . '/..' . '/../lib/User/User.php',
        'OCA\\User_LDAP\\User_LDAP' => __DIR__ . '/..' . '/../lib/User_LDAP.php',
        'OCA\\User_LDAP\\User_Proxy' => __DIR__ . '/..' . '/../lib/User_Proxy.php',
        'OCA\\User_LDAP\\Wizard' => __DIR__ . '/..' . '/../lib/Wizard.php',
        'OCA\\User_LDAP\\WizardResult' => __DIR__ . '/..' . '/../lib/WizardResult.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitUser_LDAP::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitUser_LDAP::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitUser_LDAP::$classMap;

        }, null, ClassLoader::class);
    }
}
