<?php
$CONFIG = [
  // Example config for the local dev environment

  // https://github.com/nextcloud/user_oidc

  // 1. Install the addon "user_oidc": ./occ app:install user_oidc
  // 2. Configure the addon: ./occ user_oidc:provider easystorage --clientid="easystorage" --clientsecret="<CLIENT KEY FROM KEYCLOAK REALM>" --discoveryuri="localhost:8079/realms/easystorage/.well-known/openid-configuration" --scope="openid email profile"

  // Prevent failure due to http protocol
  'allow_local_remote_servers' => true,
  'debug' => true,
  'overwriteprotocol' => 'http',

  'user_oidc' => [
      // true and true are the defaults
      // > If the user already exists in another backend, we don't create a
      // > new one in the user_oidc backend. We update the information
      // > (mapped attributes) of the existing user. If the user does not
      // > exist in another backend, we create it in the user_oidc backend
      // https://github.com/nextcloud/user_oidc#soft-auto-provisioning
      'auto_provision' => true,
      'soft_auto_provision' => true,
  ],
];
